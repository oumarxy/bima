<?php

if (!defined('BASEPATH'))    exit('No direct script access allowed');

class Insurance extends MX_Controller {

    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->library(array('auth/ion_auth', 'form_validation'));
        $this->load->helper(array('url', 'file', 'language'));

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
    }

// default function
    function index() {

        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            // set the flash data error message if there is one
            $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            $expired_status = $this->uri->segment(3);
            $sql = '';
            if ($expired_status == 'expired') {
                $today = date_to_int_string(date('d/m/Y'));
                $sql = "select * from insurance where expired_on < '$today' and closed<1 and id_domain=" . $this->ion_auth->get_id_domain();
            } else {
                $sql = "select * from insurance where id_domain=" . $this->ion_auth->get_id_domain() . " order by issued_on";
            }
            $data['insurances'] = $this->_custom_query($sql)->result();

            $data['module'] = 'insurance';
            $data['view_file'] = 'index';
            $data['insurance_active'] = 'active';
            $data['insurance_information_active'] = 'active';
            $data['heading'] = 'Insurance';
            $data['subheading'] = 'Insurance information';
            echo Modules::run('template/login_area', $data);
        }
    }

// register insurance
    function register($type, $id_property) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $id_property = (int) $id_property;
            $type = (int) $type;
            $data['id_property'] = $id_property;
            $data['id_domain'] = $this->ion_auth->get_id_domain();

            $property_count = Modules::run('property/count_where', $data);
            if (!$property_count || !in_array($type, array(1, 2))) {
                $this->session->set_flashdata('message', '<h6 class="box-title text-aqua" >Pick property to register insurance.</h6>');
                redirect('property');
            }
            $table = $this->get_table();
            $this->form_validation->set_rules('issued_on', 'Date Issued', 'trim|required|xss_clean');
            $this->form_validation->set_rules('comm_on', 'Date Comm.', 'trim|required|xss_clean');
            $this->form_validation->set_rules('duration', 'Duration', 'trim|required|xss_clean');
            $this->form_validation->set_rules('id_insurer', 'Insurer', 'trim|required|xss_clean');
            $this->form_validation->set_rules('id_cover_type', 'Cover Type', 'trim|required|xss_clean');
            $this->form_validation->set_rules('cover_note', 'Cover Note', 'trim|required|numeric|is_unique[' . $table . '.cover_note]|xss_clean');
            if ($type == 1)
                $this->form_validation->set_rules('sticker_number', 'Sticker Number', 'trim|required|numeric|is_unique[' . $table . '.sticker_number]|xss_clean');
            $this->form_validation->set_rules('policy_number', 'Policy Number', 'trim|xss_clean');
            $this->form_validation->set_rules('premium', 'Premium Amount', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('amount', 'Paid Amount', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('receipt', 'Receipt', 'trim|is_unique[' . Modules::run('payment/get_table') . '.receipt]|xss_clean');
            $this->form_validation->set_rules('vat', 'Vat', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('commission', 'Commission', 'trim|required|numeric|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $insurance['issued_on'] = ($this->input->post('issued_on') == '') ? time() : date_to_int_string($this->input->post('issued_on', TRUE));
                $insurance['comm_on'] = ($this->input->post('comm_on') == '') ? time() : date_to_int_string($this->input->post('comm_on', TRUE));
                $insurance['id_insurer'] = $this->input->post('id_insurer', TRUE);
                $insurance['type'] = $type;
                $insurance['id_cover_type'] = $this->input->post('id_cover_type', TRUE);
                $insurance['cover_note'] = $this->input->post('cover_note', TRUE);
                if ($type == 1)
                    $insurance['sticker_number'] = $this->input->post('sticker_number', TRUE);
                $insurance['policy_number'] = $this->input->post('policy_number', TRUE);
                $insurance['premium'] = $this->input->post('premium', TRUE);
                $insurance['vat'] = calculate_tax($insurance['premium'], $this->input->post('vat', TRUE));
                $insurance['commission'] = calculate_commission($insurance['premium'], $this->input->post('commission', TRUE));
                $insurance['user_id'] = $this->ion_auth->user()->row()->id;
                $insurance['id_domain'] = $this->ion_auth->get_id_domain();
                $insurance['id_property'] = $id_property;
                $insurance['expired_on'] = get_expire_date($insurance['comm_on'], $this->input->post('duration', TRUE));
                $this->_insert($insurance);
                if ($this->input->post('amount') > 0) {
                    $payment['id_insurance'] = $this->db->insert_id();
                    $payment['amount'] = $this->input->post('amount', TRUE);
                    $payment['receipt'] = $this->input->post('receipt', TRUE);
                    $payment['user_id'] = $this->ion_auth->user()->row()->id;
                    $payment['id_domain'] = $this->ion_auth->get_id_domain();
                    $payment['paid_on'] = time();
                    if ($payment['amount'] >= ($insurance['premium'] + $insurance['vat'])) {
                        $insurance = array();
                        $insurance['paid'] = 1;
                        $this->_update($this->db->insert_id(), $insurance);
                    }
                    Modules::run('payment/_insert', $payment);
                }
                $this->session->set_flashdata('message', 'Insurance successfully registered');

                redirect('insurance');
            }

            // display registration form
            $data['issued_on'] = array(
                'name' => 'issued_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('issued_on'),
            );

            $data['comm_on'] = array(
                'name' => 'comm_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('comm_on'),
            );

            $data['duration'] = $this->form_validation->set_value('duration');

            $data['id_insurer'] = $this->form_validation->set_value('id_insurer');


            $data['id_cover_type'] = $this->form_validation->set_value('id_cover_type');


            $data['cover_note'] = array(
                'name' => 'cover_note',
                'value' => $this->form_validation->set_value('cover_note')
            );
            $data['policy_number'] = array(
                'name' => 'policy_number',
                'value' => $this->form_validation->set_value('policy_number')
            );
            if ($type == 1) {
                $data['sticker_number'] = array(
                    'name' => 'sticker_number',
                    'value' => $this->form_validation->set_value('sticker_number')
                );
            }

            $data['type'] = $type;

            $data['premium'] = array(
                'name' => 'premium',
                'value' => $this->form_validation->set_value('premium')
            );

            $data['amount'] = array(
                'name' => 'amount',
                'value' => $this->form_validation->set_value('amount')
            );

            $data['receipt'] = array(
                'name' => 'receipt',
                'value' => $this->form_validation->set_value('receipt')
            );


            $data['vat'] = $this->form_validation->set_value('vat');

            $data['commission'] = array(
                'name' => 'commission',
                'value' => $this->form_validation->set_value('commission')
            );

            $data['insurer_list'] = Modules::run('insurer/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            $data['tax_list'] = Modules::run('tax/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            $data['cover_type_list'] = Modules::run('cover_type/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            $data['duration_list'] = duration_list();

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'insurance';
            $data['view_file'] = 'form';
            $data['insurance_active'] = 'active';
            $data['insurance_information_active'] = 'active';
            $data['heading'] = 'Insurance';
            $data['subheading'] = 'fill form with insurance details';
            echo Modules::run('template/login_area', $data);
        }
    }

// edit insurance
    function edit($id_insurance) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $id_insurance = (int) $id_insurance;
            $data['id_insurance'] = $id_insurance;
            $data['id_domain'] = $this->ion_auth->get_id_domain();
            $table = $this->get_table();
            $this->form_validation->set_rules('issued_on', 'Date Issued', 'trim|required|xss_clean');
            $this->form_validation->set_rules('comm_on', 'Date Comm.', 'trim|required|xss_clean');
            // $this->form_validation->set_rules('duration', 'Duration', 'trim|required|xss_clean');
            $this->form_validation->set_rules('id_insurer', 'Insurer', 'trim|required|xss_clean');
            $this->form_validation->set_rules('id_cover_type', 'Cover Type', 'trim|required|xss_clean');
            $this->form_validation->set_rules('cover_note', 'Cover Note', 'trim|required|numeric|edit_unique[' . $table . '.cover_note.' . $id_insurance . ']|xss_clean');
            if (isset($_POST['sticker_number']))
                $this->form_validation->set_rules('sticker_number', 'Sticker Number', 'trim|required|numeric|edit_unique[' . $table . '.sticker_number.' . $id_insurance . ']|xss_clean');
            $this->form_validation->set_rules('policy_number', 'Policy Number', 'trim|xss_clean');
            $this->form_validation->set_rules('premium', 'Premium Amount', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('amount', 'Paid Amount', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('receipt', 'Receipt', 'trim|edit_unique[' . Modules::run('payment/get_table') . '.receipt]|xss_clean');
            // $this->form_validation->set_rules('tax', 'Tax', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('commission', 'Commission', 'trim|required|numeric|xss_clean');


            if ($this->form_validation->run() == TRUE) {
                $insurance['issued_on'] = date_to_int_string($this->input->post('issued_on', TRUE));
                $insurance['comm_on'] = date_to_int_string($this->input->post('comm_on', TRUE));
                $insurance['id_insurer'] = $this->input->post('id_insurer', TRUE);
                $insurance['id_cover_type'] = $this->input->post('id_cover_type', TRUE);
                $insurance['cover_note'] = $this->input->post('cover_note', TRUE);
                if (isset($_POST['sticker_number']))
                    $insurance['sticker_number'] = $this->input->post('sticker_number', TRUE);
                $insurance['policy_number'] = $this->input->post('policy_number', TRUE);
                $insurance['premium'] = $this->input->post('premium', TRUE);
                $insurance['vat'] = calculate_tax($insurance['premium'], $this->input->post('vat', TRUE));
                $insurance['commission'] = calculate_commission($insurance['premium'], $this->input->post('commission', TRUE));
                $insurance['expired_on'] = get_expire_date($insurance['comm_on'], $this->input->post('duration', TRUE));

                $amount = Modules::run('payment/sum_where', $data);
                $insurance['paid'] = ($amount >= ($insurance['premium'] + $insurance['vat'])) ? 1 : 0;
                $this->_update($id_insurance, $insurance);

                $this->session->set_flashdata('message', 'Insurance successfully updated');

                redirect('insurance');
            }

            $sql = "select * from insurance where id_domain=" . $this->ion_auth->get_id_domain() . " and id_insurance=$id_insurance and closed=0";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('insurance');
            }
            $insurance_row = $query->row();
            $payment_row = Modules::run('payment/get_where_custom', $data)->row();

            $duration = round(($insurance_row->expired_on - $insurance_row->comm_on) / 60 / 60 / 24 / 30);

            // display registration form
            $data['issued_on'] = array(
                'name' => 'issued_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('issued_on', format_date('', $insurance_row->issued_on)),
            );

            $data['comm_on'] = array(
                'name' => 'comm_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('comm_on', format_date('', $insurance_row->comm_on)),
            );

            $data['duration'] = $this->form_validation->set_value('duration', $duration);

            $data['id_insurer'] = $this->form_validation->set_value('id_insurer', $insurance_row->id_insurer);


            $data['id_cover_type'] = $this->form_validation->set_value('id_cover_type', $insurance_row->id_cover_type);


            $data['cover_note'] = array(
                'name' => 'cover_note',
                'value' => $this->form_validation->set_value('cover_note', $insurance_row->cover_note)
            );

            $data['sticker_number'] = array(
                'name' => 'sticker_number',
                'value' => $this->form_validation->set_value('sticker_number', $insurance_row->sticker_number)
            );

            $data['policy_number'] = array(
                'name' => 'policy_number',
                'value' => $this->form_validation->set_value('policy_number', $insurance_row->policy_number)
            );


            $data['premium'] = array(
                'name' => 'premium',
                'value' => $this->form_validation->set_value('premium', $insurance_row->premium)
            );

            $data['type'] = $insurance_row->type;

            $data['amount'] = array(
                'name' => 'amount',
                'value' => $this->form_validation->set_value('amount', isset($payment_row->amount) ? $payment_row->amount : '')
            );

            $data['receipt'] = array(
                'name' => 'receipt',
                'value' => $this->form_validation->set_value('receipt', isset($payment_row->receipt) ? $payment_row->receipt : '')
            );

            $data['vat'] = $this->form_validation->set_value('vat', number_format(($insurance_row->vat / $insurance_row->premium) * 100, 2));

            $data['commission'] = array(
                'name' => 'commission',
                'value' => $this->form_validation->set_value('commission', number_format(($insurance_row->commission / $insurance_row->premium) * 100, 2))
            );

            $data['insurer_list'] = Modules::run('insurer/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            $data['tax_list'] = Modules::run('tax/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            $data['cover_type_list'] = Modules::run('cover_type/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            $data['duration_list'] = duration_list();

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'insurance';
            $data['view_file'] = 'form';
            $data['insurance_active'] = 'active';
            $data['register_insurance_active'] = 'active';
            $data['heading'] = 'Insurance';
            $data['subheading'] = 'fill form with insurance details';
            echo Modules::run('template/login_area', $data);
        }
    }

    //renew insurance
    function renew($id_insurance) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $id_insurance = (int) $id_insurance;
            $data['id_insurance'] = $id_insurance;
            $data['id_domain'] = $this->ion_auth->get_id_domain();
            $data['closed'] = 0;
            $insurance_count = Modules::run('insurance/count_where', $data);
            if (!$insurance_count) {
                redirect('insurance');
            }
            $insurance_row = Modules::run('insurance/get_where_custom', $data)->row();
            $table = $this->get_table();
            $this->form_validation->set_rules('issued_on', 'Date Issued', 'trim|required|xss_clean');
            $this->form_validation->set_rules('comm_on', 'Date Comm.', 'trim|required|xss_clean');
            $this->form_validation->set_rules('duration', 'Duration', 'trim|required|xss_clean');
            $this->form_validation->set_rules('id_insurer', 'Insurer', 'trim|required|xss_clean');
            $this->form_validation->set_rules('id_cover_type', 'Cover Type', 'trim|required|xss_clean');
            $this->form_validation->set_rules('cover_note', 'Cover Note', 'trim|required|numeric|is_unique[' . $table . '.cover_note]|xss_clean');
            if ($insurance_row->type == 1)
                $this->form_validation->set_rules('sticker_number', 'Sticker Number', 'trim|required|numeric|is_unique[' . $table . '.sticker_number]|xss_clean');
            $this->form_validation->set_rules('policy_number', 'Policy Number', 'trim|xss_clean');
            $this->form_validation->set_rules('premium', 'Premium Amount', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('amount', 'Paid Amount', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('receipt', 'Receipt', 'trim|is_unique[' . Modules::run('payment/get_table') . '.receipt]|xss_clean');
            $this->form_validation->set_rules('vat', 'Vat', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('commission', 'Commission', 'trim|required|numeric|xss_clean');
            if ($this->form_validation->run() == TRUE) {
                $insurance['issued_on'] = ($this->input->post('issued_on') == '') ? time() : date_to_int_string($this->input->post('issued_on', TRUE));
                $insurance['comm_on'] = ($this->input->post('comm_on') == '') ? time() : date_to_int_string($this->input->post('comm_on', TRUE));
                $insurance['id_insurer'] = $this->input->post('id_insurer', TRUE);
                $insurance['id_insurance_prev'] = $id_insurance;
                $insurance['type'] = $insurance_row->type;
                $insurance['id_cover_type'] = $this->input->post('id_cover_type', TRUE);
                $insurance['cover_note'] = $this->input->post('cover_note', TRUE);
                if ($insurance_row->type == 1)
                    $insurance['sticker_number'] = $this->input->post('sticker_number', TRUE);
                $insurance['policy_number'] = $this->input->post('policy_number', TRUE);
                $insurance['premium'] = $this->input->post('premium', TRUE);
                $insurance['vat'] = calculate_tax($insurance['premium'], $this->input->post('vat', TRUE));
                $insurance['commission'] = calculate_commission($insurance['premium'], $this->input->post('commission', TRUE));
                $insurance['user_id'] = $this->ion_auth->user()->row()->id;
                $insurance['id_domain'] = $this->ion_auth->get_id_domain();
                $insurance['id_property'] = $insurance_row->id_property;
                $insurance['expired_on'] = get_expire_date($insurance['comm_on'], $this->input->post('duration', TRUE));
                $this->_insert($insurance);
                $id_insurance_renew = $this->db->insert_id();
                if ($this->db->affected_rows() > 0) {
                    $this->_update($id_insurance, array('closed' => 1));
                    if ($this->input->post('amount') > 0) {
                        $payment['id_insurance'] = $id_insurance_renew;
                        $payment['amount'] = $this->input->post('amount', TRUE);
                        $payment['receipt'] = $this->input->post('receipt', TRUE);
                        $payment['user_id'] = $this->ion_auth->user()->row()->id;
                        $payment['id_domain'] = $this->ion_auth->get_id_domain();
                        $payment['paid_on'] = time();
                        // check paid status
                        $amount_required = 0;
                        $amount_required = $insurance['premium'] + $insurance['vat'];
                        $insurance['paid'] = ($payment['amount'] >= $amount_required) ? 1 : 0;
                        $this->_update($id_insurance_renew, array('paid' => $insurance['paid']));
                        Modules::run('payment/_insert', $payment);
                    }
                    $this->session->set_flashdata('message', 'Insurance successfully renewed.');
                    redirect('insurance');
                }
            }

            // display registration form
            $data['issued_on'] = array(
                'name' => 'issued_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('issued_on'),
            );

            $data['comm_on'] = array(
                'name' => 'comm_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('comm_on'),
            );

            $data['duration'] = $this->form_validation->set_value('duration');

            $data['id_insurer'] = $this->form_validation->set_value('id_insurer', $insurance_row->id_insurer);

            $data['id_cover_type'] = $this->form_validation->set_value('id_cover_type', $insurance_row->id_cover_type);


            $data['cover_note'] = array(
                'name' => 'cover_note',
                'value' => $this->form_validation->set_value('cover_note')
            );
            if ($insurance_row->type == 1) {
                $data['sticker_number'] = array(
                    'name' => 'sticker_number',
                    'value' => $this->form_validation->set_value('sticker_number')
                );
            }

            $data['policy_number'] = array(
                'name' => 'policy_number',
                'value' => $this->form_validation->set_value('policy_number')
            );

            $data['type'] = $insurance_row->type;

            $data['premium'] = array(
                'name' => 'premium',
                'value' => $this->form_validation->set_value('premium', $insurance_row->premium)
            );

            $data['amount'] = array(
                'name' => 'amount',
                'value' => $this->form_validation->set_value('amount')
            );

            $data['receipt'] = array(
                'name' => 'receipt',
                'value' => $this->form_validation->set_value('receipt')
            );


            $data['vat'] = $this->form_validation->set_value('vat', number_format(($insurance_row->vat / $insurance_row->premium) * 100, 2));

            $data['commission'] = array(
                'name' => 'commission',
                'value' => $this->form_validation->set_value('commission', number_format(($insurance_row->commission / $insurance_row->premium) * 100, 2))
            );

            $data['insurer_list'] = Modules::run('insurer/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            $data['tax_list'] = Modules::run('tax/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            $data['cover_type_list'] = Modules::run('cover_type/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            $data['duration_list'] = duration_list();

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'insurance';
            $data['view_file'] = 'form';
            $data['insurance_active'] = 'active';
            $data['insurance_information_active'] = 'active';
            $data['heading'] = 'Insurance';
            $data['subheading'] = 'fill form with insurance details';
            echo Modules::run('template/login_area', $data);
        }
    }

    // delete insurance
    function delete($id_insurance) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_insurance = (int) $id_insurance;
        $data['id_insurance'] = $id_insurance;
        $data['id_domain'] = $this->ion_auth->get_id_domain();
        $insurance_row = Modules::run('insurance/get_where_custom', $data)->row();
        $id_insurance_prev = $insurance_row->id_insurance_prev;
        $sql = "delete from insurance where
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_insurance=$id_insurance";

        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0) {
            if ($id_insurance_prev > 0)
                $this->_update($id_insurance_prev, array('closed' => 0));
            $this->session->set_flashdata('message', 'Insurance successfully deleted');
        } else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Insurance unsuccessfully deleted./h6>');

        redirect('insurance');
    }

    // import bulk insurance
    function import() {

        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $excel_upload = TRUE;
            $upload_error = '';

            $this->form_validation->set_rules('insurer', 'Insurer', 'trim|required');
            if ($this->form_validation->run() == TRUE) {
                if (isset($_FILES['userfile']['name']) && empty($_FILES['userfile']['name'])) {
                    $upload_error = '<p>You must upload excel file.</p>';
                    $excel_upload = FALSE;
                } elseif (isset($_FILES['userfile']['name']) && (get_file_extension($_FILES['userfile']['name']) != 'xlsx' && get_file_extension($_FILES['userfile']['name']) != 'xls')) {
                    $upload_error = '<p>File uploaded must be in xls or xlxs format.</p>';
                    $excel_upload = FALSE;
                }

                if ($excel_upload == TRUE) {
                    move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/temp/insurance_import.xlsx');
                    // set file path
                    $file = './uploads/temp/insurance_import.xlsx';
                    $type = (isset($row['H']) && $row['H'] <> '') ? 2 : 1;
                    //load the excel library
                    $this->load->library('excel');
                    //read file from path
                    $objPHPExcel = PHPExcel_IOFactory::load($file);
                    //get only the Cell Collection
                    $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
                    //extract to a PHP readable array format
                    foreach ($cell_collection as $cell) {
                        $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
                        $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
                        $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
                        //header will/should be in row 1 only. of course this can be modified to suit your need.
                        if ($row == 1) {
                            $header[$row][$column] = $data_value;
                        } else {

                            $arr_data[$row][$column] = trim($data_value);
                        }
                    }

                    $i = 0;
                    $i_customer = 0;
                    $i_property = 0;
                    $i_insurance = 0;
                    $i_payment = 0;

                    foreach ($arr_data as $row) {
                        $issued_on = '';
                        $comm_on = '';
                        $issued_name = '';
                        $cover_note = '';
                        $sticker_number = '';
                        $property_number = '';
                        $property_value = '';
                        $premium_amount = '';
                        $premium_paid = '';
                        $vat = '';
                        $address = '';
                        $policy_number = '';
                        $property_type = '';
                        $receipt = '';
                        $location = '';
                        $phone = '';
                        $period = '';
                        if (trim($row['B']) <> '' && trim($row['C']) <> '' && trim($row['J']) <> '' && trim($row['F']) <> '' && trim($row['L'] <> '')) {
                            $issued_on = (isset($row['A']) && $row['A']) ? trim($row['A']) : trim($row['B']);
                            $comm_on = trim($row['B']);

                            $cover_note_count = Modules::run('insurance/count_where', array('cover_note' => trim($row['J']), 'id_domain' => $this->ion_auth->get_id_domain()));

                            $sticker_number_count = ($type == 1) ? (trim($row['K']) <> '' ? Modules::run('insurance/count_where', array('sticker_number' => trim($row['K']), 'id_domain' => $this->ion_auth->get_id_domain())) : 0) : 0;


                            $data['receipt'] = ((isset($row['R']) && $row['R'] <> '') ? trim($row['R']) : '');
                            $receipt_count = ($data['receipt'] == '') ? 0 : Modules::run('payment/count_where', array('receipt' => $data['receipt'], 'id_domain' => $this->ion_auth->get_id_domain()));

                            if ($cover_note_count == 0 && $sticker_number_count == 0) {
                                // customer data
                                $issued_name = strtoupper(trim($row['C']));
                                $customer['name'] = $issued_name;
                                $address = ((isset($row['D']) && $row['D'] <> '') ? trim($row['D']) : '');
                                $customer['address'] = $address;
                                $phone = ((isset($row['E']) && $row['E'] <> '') ? trim($row['E']) : '');
                                $customer['phone'] = $phone;
                                $customer['registered_on'] = date_to_int_string($comm_on);
                                $customer['user_id'] = $this->ion_auth->user()->row()->id;
                                $customer['id_domain'] = $this->ion_auth->get_id_domain();
                                $customer_count = Modules::run('customer/count_where_custom', array('name' => $issued_name, 'id_domain' => $this->ion_auth->get_id_domain()));

                                if (!empty($customer) && $customer_count < 1) {
                                    Modules::run('customer/_insert', $customer);
                                    $i_customer++;
                                } else {
                                    $id_customer = Modules::run('customer/get_where_custom', array('name' => $issued_name, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->id_customer;
                                }
                                $customer = array();
                                // property data
                                $property['id_customer'] = ($customer_count < 1) ? $this->db->insert_id() : $id_customer;

                                $property_description = ((isset($row['F']) && $row['F'] <> '') ? trim($row['F']) : '');
                                $property['description'] = $property_description;

                                $property_number = trim($row['G']) <> '' ? strtoupper(trim($row['G'])) : '';
                                $property_number = str_replace('  ', ' ', $property_number);
                                $property['number'] = $property_number;

                                $property['id_property_type'] = (isset($row['H']) && $row['H'] <> '') ? $row['H'] : 1;

                                $property_value = ((isset($row['I']) && $row['I'] <> '') ? trim($row['I']) : '');
                                $property['property_value'] = $property_value;
                                $property['user_id'] = $this->ion_auth->user()->row()->id;
                                $property['id_domain'] = $this->ion_auth->get_id_domain();

                                if (!empty($property)) {
                                    Modules::run('property/_insert', $property);
                                    $property = array();
                                    $i_property++;
                                    //insurance data
                                    $insurance['id_property'] = $this->db->insert_id();
                                    $insurance['id_insurer'] = $this->input->post('insurer', TRUE);
                                    $insurance['type'] = $type;
                                    $insurance['issued_on'] = date_to_int_string($issued_on);

                                    $insurance['comm_on'] = date_to_int_string($comm_on);
                                    $period = ((isset($row['P']) && $row['P'] <> '') ? trim($row['P']) : '');
                                    $insurance['expired_on'] = ($period <> '' && is_numeric($period)) ? get_expire_date($insurance['comm_on'], $period) : get_expire_date($insurance['comm_on']);

                                    $cover_note = trim($row['J']);
                                    $insurance['cover_note'] = $cover_note;
                                    $sticker_number = ((isset($row['K']) && $row['K'] <> '') ? trim($row['K']) : '');
                                    $insurance['sticker_number'] = $sticker_number;

                                    $policy_number = ((isset($row['O']) && $row['O'] <> '') ? trim($row['O']) : '');
                                    $insurance['policy_number'] = $policy_number;

                                    $premium_amount = trim($row['L']);
                                    $insurance['premium'] = intval($premium_amount);

                                    // set vat rate
                                    $vat = Modules::run('tax/get_where_custom', array('type'=>1,'id_domain'=>$this->ion_auth->get_id_domain()))->row()->percentage;
                                    if (trim($row['M']) <> '') {
                                        $vat = 0;
                                    }
                                    $insurance['vat'] = calculate_tax($insurance['premium'], $vat);

                                    // set comm rate
                                    $commission = Modules::run('tax/get_where_custom', array('type'=>2,'id_domain'=>$this->ion_auth->get_id_domain()))->row()->percentage;
                                    if (trim($row['N']) <> '') {
                                        $commission = 0;
                                    }
                                    $insurance['commission'] = calculate_commission($insurance['premium'], $commission);
                                    $insurance['user_id'] = $this->ion_auth->user()->row()->id;
                                    $insurance['id_domain'] = $this->ion_auth->get_id_domain();
                                    // check paid status
                                    $amount_required = 0;
                                    $amount = (isset($row['Q']) && $row['Q'] <> '') ? trim($row['Q']) : '';
                                    $amount_required = $insurance['premium'] + $insurance['vat'];
                                    // limit insurance by specify required value
                                    if (!empty($insurance)) {
                                        $this->_insert($insurance);
                                        $i_insurance++;
                                        if ($amount <> '' && $data['receipt'] <> '' && $receipt_count == 0) {
                                            $insurance['paid'] = ($amount >= $amount_required) ? 1 : 0;
                                            // payment data
                                            $payment['id_insurance'] = $this->db->insert_id();
                                            $this->_update($this->db->insert_id(), array('paid' => $insurance['paid']));
                                            $payment['amount'] = $amount;
                                            $payment['receipt'] = $data['receipt'];
                                            $payment['paid_on'] = $insurance['comm_on'];
                                            $payment['user_id'] = $this->ion_auth->user()->row()->id;
                                            $payment['id_domain'] = $this->ion_auth->get_id_domain();
                                            if (!empty($payment)) {
                                                Modules::run('payment/_insert', $payment);
                                                $payment = array();
                                                $i_payment++;
                                            }
                                        }
                                        $insurance = array();
                                    }
                                }



                                $i++;
                            } else {
                                $file_path = APPPATH . "/logs/log_" . date('Y-m-d') . ".txt";
                                $data = "Record with cover " . trim($row['J']) . " was not imported since either cover or sticker exist [ " . date('H:i:s') . " ].\n";
                                if (file_exists($file_path)) {
                                    write_file($file_path, $data, 'a');
                                } else {
                                    write_file($file_path, $data);
                                }
                            }
                        }
                    }
                    $this->load->helper('file');
                    delete_files('./uploads/temp');
                    if ($i > 0) {
                        $message = "$i found, $i_customer customers, $i_property properties, $i_insurance insurance and $i_payment payment successfully imported ";
                        $this->session->set_flashdata('message', '<h6 class="box-title text-green" >' . $message . '</h6>');
                    } else
                        $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Data unsuccessfully imported</h6>');
                    redirect('insurance');
                }
            }


            $data['id_insurer'] = $this->form_validation->set_value('insurer');
            $data['insurer_list'] = Modules::run('insurer/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            // display registration form
            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : ($upload_error ? $upload_error : $this->session->flashdata('message'));
            $data['module'] = 'insurance';
            $data['view_file'] = 'import_insurance';
            $data['insurance_active'] = 'active';
            $data['import_bulk_insurance_active'] = 'active';
            $data['heading'] = 'Insurance';
            $data['subheading'] = 'import bulk insurance file';
            echo Modules::run('template/login_area', $data);
        }
    }

    // import bulk renew insurance
    function import_renewal() {

        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $excel_upload = TRUE;
            $upload_error = '';
            $this->form_validation->set_rules('renewal', 'Renewal', 'trim|required');
            if ($this->form_validation->run() == TRUE) {
                if (isset($_FILES['userfile']['name']) && empty($_FILES['userfile']['name'])) {
                    $upload_error = '<p>You must upload excel file.</p>';
                    $excel_upload = FALSE;
                } elseif (isset($_FILES['userfile']['name']) && (get_file_extension($_FILES['userfile']['name']) != 'xlsx' && get_file_extension($_FILES['userfile']['name']) != 'xls')) {
                    $upload_error = '<p>File uploaded must be in xls or xlxs format.</p>';
                    $excel_upload = FALSE;
                }
                if ($excel_upload == TRUE) {
                    move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/temp/insurance_import.xlsx');
                    // set file path
                    $file = './uploads/temp/insurance_import.xlsx';
                    $type = $this->input->post('type', TRUE);
                    //load the excel library
                    $this->load->library('excel');
                    //read file from path
                    $objPHPExcel = PHPExcel_IOFactory::load($file);
                    //get only the Cell Collection
                    $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
                    //extract to a PHP readable array format
                    foreach ($cell_collection as $cell) {
                        $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
                        $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
                        $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
                        //header will/should be in row 1 only. of course this can be modified to suit your need.
                        if ($row == 1) {
                            $header[$row][$column] = $data_value;
                        } else {

                            $arr_data[$row][$column] = trim($data_value);
                        }
                    }

                    $i = 0;
                    $i_customer = 0;
                    $i_property = 0;
                    $i_insurance = 0;
                    $i_payment = 0;

                    foreach ($arr_data as $row) {
                        $issued_on = '';
                        $comm_on = '';
                        $issued_name = '';
                        $cover_note = '';
                        $sticker_number = '';
                        $property_number = '';
                        $property_value = '';
                        $premium_amount = '';
                        $premium_paid = '';
                        $vat = '';
                        $address = '';
                        $policy_number = '';
                        $property_type = '';
                        $receipt = '';
                        $location = '';
                        $phone = '';
                        $period = '';
                        if (trim($row['B']) <> '' && trim($row['C']) <> '' && trim($row['D']) <> '' && trim($row['F']) <> '' && trim($row['K']) <> '') {
                            $issued_on = (isset($row['A']) && $row['A']) ? trim($row['A']) : trim($row['B']);
                            $comm_on = trim($row['B']);

                            $cover_note_count = Modules::run('insurance/count_where', array('cover_note'=>trim($row['D']),'id_domain'=>$this->ion_auth->get_id_domain()));


                            $sticker_number_count = $data['sticker_number'] <> '' ? Modules::run('insurance/count_where', array('sticker_number'=>trim($row['E']),'id_domain'=>$this->ion_auth->get_id_domain())) : 0;


                            // check if insurer exist
                            $insurer_count = Modules::run('insurer/count_where', array('id_insurer'=>intval(trim($row['K'])),'id_domain'=>$this->ion_auth->get_id_domain()));


                            $data['receipt'] = (isset($row['M']) && $row['M'] <> '') ? trim($row['M']) : '';
                            $receipt_count = ($data['receipt'] <> '') ? Modules::run('payment/count_where', array('receipt'=>$data['receipt'],'id_domain'=>$this->ion_auth->get_id_domain())) : 0;


                            if ($cover_note_count == 0 && $sticker_number_count == 0 && $insurer_count > 0) {
                                $prev_cover_note = trim($row['C']);
                                $prev_insurance_query = Modules::run('insurance/get_where_custom', array('cover_note' => $prev_cover_note,'id_domain'=>$this->ion_auth->get_id_domain()));

                                if ($prev_insurance_query->num_rows() > 0) {
                                    $prev_insurance_row = $prev_insurance_query->row();
                                    $id_property = $prev_insurance_row->id_property;
                                    //insurance data
                                    $insurance['id_property'] = $id_property;
                                    $insurance['id_insurer'] = intval(trim($row['K']));
                                    $insurance['type'] = $prev_insurance_row->type;
                                    $insurance['issued_on'] = date_to_int_string($issued_on);

                                    $insurance['comm_on'] = date_to_int_string($comm_on);
                                    $period = (isset($row['J']) && $row['J'] <> '') ? trim($row['J']) : '';
                                    $insurance['expired_on'] = ($period <> '' && is_numeric($period)) ? get_expire_date($insurance['comm_on'], $period) : get_expire_date($insurance['comm_on']);

                                    $cover_note = trim($row['D']);
                                    $insurance['cover_note'] = $cover_note;
                                    $sticker_number = (isset($row['E']) && trim($row['E']) <> '') ? trim($row['E']) : '';
                                    $insurance['sticker_number'] = $sticker_number;

                                    $policy_number = (isset($row['I']) && $row['I'] <> '') ? trim($row['I']) : '';
                                    $insurance['policy_number'] = $policy_number;

                                    $premium_amount = trim($row['F']);
                                    $insurance['premium'] = intval($premium_amount);

                                    // set vat rate
                                    $vat = Modules::run('tax/get_where_custom', array('type'=>1,'id_domain'=>$this->ion_auth->get_id_domain()))->row()->percentage;
                                    if (trim($row['G']) <> '') {
                                        $vat = 0;
                                    }
                                    $insurance['vat'] = calculate_tax($insurance['premium'], $vat);

                                    // set comm rate
                                    $commission = Modules::run('tax/get_where_custom', array('type'=>1,'id_domain'=>$this->ion_auth->get_id_domain()))->row()->percentage;
                                    if (trim($row['H']) <> '') {
                                        $commission = 0;
                                    }
                                    $insurance['commission'] = calculate_commission($insurance['premium'], $commission);
                                    $insurance['user_id'] = $this->ion_auth->user()->row()->id;
                                    $insurance['id_domain'] = $this->ion_auth->get_id_domain();
                                    $insurance['id_insurance_prev'] = $prev_insurance_row->id_insurance;
                                    // check paid status
                                    $amount_required = 0;
                                    $amount = trim($row['L']);
                                    $amount_required = $insurance['premium'] + $insurance['vat'];
                                    // limit insurance insert
                                    if (!empty($insurance)) {
                                        $this->_insert($insurance);
                                        $id_insurance_renew = $this->db->insert_id();
                                        if ($this->db->affected_rows() > 0) {
                                            $this->_update($prev_insurance_row->id_insurance, array('closed' => 1));
                                            $i_insurance++;
                                        }
                                        if ($amount <> '' && $data['receipt'] <> '' && $receipt_count == 0) {
                                            $insurance['paid'] = ($amount >= $amount_required) ? 1 : 0;
                                            // payment data
                                            $payment['id_insurance'] = $id_insurance_renew;
                                            $payment['amount'] = $amount;
                                            $payment['receipt'] = $data['receipt'];
                                            $payment['paid_on'] = $insurance['comm_on'];
                                            $payment['user_id'] = $this->ion_auth->user()->row()->id;
                                            $payment['id_domain'] = $this->ion_auth->get_id_domain();
                                            $this->_update($id_insurance_renew, array('paid' => $insurance['paid']));
                                            if (!empty($payment))
                                                Modules::run('payment/_insert', $payment);
                                            $payment = array();
                                            $i_payment++;
                                        }
                                        $insurance = array();
                                    }
                                }
                            }else {
                                $file_path = APPPATH . "/logs/log_" . date('Y-m-d') . ".txt";
                                $data = "Record with cover " . trim($row['D']) . " was not imported since either cover or sticker exist, otherwise you filled with wrong insurer id [ " . date('H:i:s') . " ].\n";
                                if (file_exists($file_path)) {
                                    write_file($file_path, $data, 'a');
                                } else {
                                    write_file($file_path, $data);
                                }
                            }
                        }
                        $i++;
                    }
                    $this->load->helper('file');
                    delete_files('./uploads/temp');
                    if ($i > 0) {
                        $message = "$i found, $i_insurance renewal insurance and $i_payment payment successfully imported ";
                        $this->session->set_flashdata('message', '<h6 class="box-title text-green" >' . $message . '</h6>');
                    } else
                        $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Data not successfully imported</h6>');
                    redirect('insurance');
                }
            }

            // display registration form
            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : ($upload_error ? $upload_error : $this->session->flashdata('message'));
            $data['module'] = 'insurance';
            $data['view_file'] = 'renewal_insurance';
            $data['insurance_active'] = 'active';
            $data['import_renewal_insurance_active'] = 'active';
            $data['heading'] = 'Insurance';
            $data['subheading'] = 'import renewal insurance file';
            echo Modules::run('template/login_area', $data);
        }
    }

    function get_table() {
        $this->load->model('insurance_model');
        $table = $this->insurance_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('insurance_model');
        $query = $this->insurance_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('insurance_model');
        $query = $this->insurance_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('insurance_model');
        $query = $this->insurance_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('insurance_model');
        $query = $this->insurance_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('insurance_model');
        $this->insurance_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('insurance_model');
        $this->insurance_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('insurance_model');
        $this->insurance_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('insurance_model');
        $count = $this->insurance_model->count_where($column, $value);
        return $count;
    }

    function count_all() {
        $this->load->model('insurance_model');
        $count = $this->insurance_model->count_all();
        return $count;
    }

    function sum_premium($column, $value) {
        $this->load->model('insurance_model');
        $sum = $this->insurance_model->sum_premium($column, $value);
        return $sum;
    }

    function get_max() {
        $this->load->model('insurance_model');
        $max_id = $this->insurance_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('insurance_model');
        $query = $this->insurance_model->_custom_query($mysql_query);
        return $query;
    }

}
