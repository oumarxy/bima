<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Claim extends MX_Controller {

    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->library(array('auth/ion_auth', 'form_validation'));
        $this->load->helper(array('url', 'language'));

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
            $sql = "select * from claim where id_domain=" . $this->ion_auth->get_id_domain();
            $sql.=" order by reported_on";

            $data['claims'] = $this->_custom_query($sql)->result();
            $data['module'] = 'claim';
            $data['view_file'] = 'index';
            $data['insurance_active'] = 'active';
            $data['claim_sheet_active'] = 'active';
            $data['heading'] = 'Insurance';
            $data['subheading'] = 'claim sheet';
            echo Modules::run('template/login_area', $data);
        }
    }

// register claim
    function register($id_insurance) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $id_insurance = (int) $id_insurance;
            $data['id_insurance'] = $id_insurance;
            $data['id_domain']=$this->ion_auth->get_id_domain();
            $insurance_count = Modules::run('insurance/count_where', $data);
            if (!$insurance_count) {
                $this->session->set_flashdata('message', '<h6 class="box-title text-aqua" >Search a record, under options column click <i class="fa fa-warning"></i> to register claim.</h6>');
                redirect('insurance');
            }

            $table = $this->get_table();
            $this->form_validation->set_rules('reported_on', 'Reported Date', 'required|trim|xss_clean');
            $this->form_validation->set_rules('lost_on', 'Lost Date', 'required|trim|xss_clean');
            $this->form_validation->set_rules('claim_amount', 'Amount', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('claim_type', 'Nature', 'requred|trim|numeric|xss_clean');
            $this->form_validation->set_rules('settlement_amount', 'Settlement', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('claim_number', 'Number', 'trim|numeric|is_unique[' . $table . '.claim_number]|xss_clean');
            if ($this->form_validation->run() == TRUE) {
                $claim['id_insurance'] = $id_insurance;
                if ($this->input->post('claim_amount') <> '')
                    $claim['claim_amount'] = $this->input->post('claim_amount', TRUE);
                if ($this->input->post('settlement_amount') <> '')
                    $claim['settlement_amount'] = $this->input->post('settlement_amount', TRUE);
                $claim['id_claim_type'] = $this->input->post('claim_type', TRUE);
                $claim['claim_number'] = $this->input->post('claim_number', TRUE);
                $claim['reported_on'] = ($this->input->post('reported_on') == '') ? time() : date_to_int_string($this->input->post('reported_on', TRUE));
                $claim['lost_on'] = ($this->input->post('lost_on') == '') ? time() : date_to_int_string($this->input->post('lost_on', TRUE));
                $claim['id_domain'] = $this->ion_auth->get_id_domain();
                $claim['user_id'] = $this->ion_auth->user()->row()->id;
                $this->_insert($claim);
                $this->session->set_flashdata('message', 'Claim successfully registered');


                redirect('claim/sheet');
            }

            // display registration form
            $data['cover_note'] = array(
                'name' => 'cover_note',
                'readonly' => 'readonly',
                'value' => $this->form_validation->set_value('cover_note', cover_note_by_id_insurance($id_insurance))
            );

            $data['reported_on'] = array(
                'name' => 'reported_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('reported_on')
            );

            $data['lost_on'] = array(
                'name' => 'lost_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('lost_on')
            );
            $data['claim_number'] = array(
                'name' => 'claim_number',
                'value' => $this->form_validation->set_value('claim_number')
            );

            $data['claim_amount'] = array(
                'name' => 'claim_amount',
                'value' => $this->form_validation->set_value('claim_amount')
            );

            $data['settlement_amount'] = array(
                'name' => 'settlement_amount',
                'value' => $this->form_validation->set_value('settlement_amount')
            );
            $data['id_claim_type'] = $this->form_validation->set_value('claim_type');
            $data['claim_type_list'] = Modules::run('claim_type/get_where_custom', array('id_domain'=>$this->ion_auth->get_id_domain()))->result_array();

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'claim';
            $data['view_file'] = 'form';
            $data['insurance_active'] = 'active';
            $data['claim_sheet_active'] = 'active';
            $data['heading'] = 'Insurance';
            $data['subheading'] = 'fill form with claim details';
            echo Modules::run('template/login_area', $data);
        }
    }

    //edit claim
    function edit($id_claim) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $this->form_validation->set_rules('reported_on', 'Reported Date', 'required|trim|xss_clean');
            $this->form_validation->set_rules('lost_on', 'Lost Date', 'required|trim|xss_clean');
            $this->form_validation->set_rules('cover_note', 'Cover Note', 'required|trim|required|xss_clean');
            $this->form_validation->set_rules('claim_type', 'Nature', 'requred|trim|numeric|xss_clean');
            $this->form_validation->set_rules('claim_amount', 'Amount', 'required|trim|numeric|xss_clean');
            $this->form_validation->set_rules('settlement_amount', 'Settlement', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('claim_number', 'Number', 'trim|numeric|xss_clean');


            if ($this->form_validation->run() == TRUE) {
                if ($this->input->post('claim_amount') <> '')
                    $claim['claim_amount'] = $this->input->post('claim_amount', TRUE);
                if ($this->input->post('settlement_amount') <> '')
                    $claim['settlement_amount'] = $this->input->post('settlement_amount', TRUE);
                $claim['id_claim_type'] = $this->input->post('claim_type', TRUE);
                $claim['claim_number'] = $this->input->post('claim_number', TRUE);
                $claim['reported_on'] = date_to_int_string($this->input->post('reported_on', TRUE));
                $claim['lost_on'] = date_to_int_string($this->input->post('lost_on', TRUE));
                $this->_update($id_claim, $claim);
                $this->session->set_flashdata('message', 'Claim successfully updated');


                redirect('claim/sheet');
            }
            $data['id_claim'] = $id_claim;
            $sql = "select * from claim where id_domain=" . $this->ion_auth->get_id_domain() . " and id_claim=$id_claim";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('claim/sheet');
            }
            $claim_row = $query->row();
            $id_insurance = $claim_row->id_insurance;
            $cover_note = Modules::run('insurance/get_where_custom', array('id_insurance' => $id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->cover_note;

            // display registration form
            $data['cover_note'] = array(
                'name' => 'cover_note',
                'readonly' => 'readonly',
                'value' => $this->form_validation->set_value('cover_note', $cover_note)
            );

            $data['reported_on'] = array(
                'name' => 'reported_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('reported_on', format_date('', $claim_row->reported_on))
            );

            $data['lost_on'] = array(
                'name' => 'lost_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('lost_on', format_date('', $claim_row->lost_on))
            );

            $data['claim_type'] = $this->form_validation->set_value('claim_type', $claim_row->id_claim_type);

            $data['claim_number'] = array(
                'name' => 'claim_number',
                'value' => $this->form_validation->set_value('claim_number', $claim_row->claim_number)
            );

            $data['claim_amount'] = array(
                'name' => 'claim_amount',
                'value' => $this->form_validation->set_value('claim_amount', $claim_row->claim_amount)
            );

            $data['settlement_amount'] = array(
                'name' => 'settlement_amount',
                'value' => $this->form_validation->set_value('settlement_amount', $claim_row->settlement_amount)
            );

            $data['id_claim_type'] = $this->form_validation->set_value('claim_type', $claim_row->id_claim_type);
            $data['claim_type_list'] = Modules::run('claim_type/get_where_custom', array('id_domain'=>$this->ion_auth->get_id_domain()))->result_array();

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'claim';
            $data['view_file'] = 'form';
            $data['insurance_active'] = 'active';
            $data['claim_sheet_active'] = 'active';
            $data['heading'] = 'Claim';
            $data['subheading'] = 'fill form with claim details';
            echo Modules::run('template/login_area', $data);
        }
    }

// close claim
    function close($id_claim) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_claim = (int) $id_claim;
        $sql = "update claim set remark=1 where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_claim=$id_claim";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Claim successfully closed');
           else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Claim unsuccessfully closed</h6>');

        redirect('claim/sheet');
    }

    // open claim
    function open($id_claim) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_claim = (int) $id_claim;
        $sql = "update claim set remark=2 where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_claim=$id_claim";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Claim successfully opened');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Claim unsuccessfully opened</h6>');


        redirect('claim/sheet');
    }

    // set to paid
    function paid($id_claim) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_claim = (int) $id_claim;
        $sql = "update claim set paid=1,remark=1 where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_claim=$id_claim";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Claim successfully marked as paid and closed');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Claim unsuccessfully marked as paid and closed</h6>');

        redirect('claim/sheet');
    }

    // set to not paid
    function not_paid($id_claim) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_claim = (int) $id_claim;
        $claim['paid'] = 0;
        $sql = "update claim set paid=0 where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_claim=$id_claim";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Claim successfully marked as not paid');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Claim unsuccessfully marked as not paid</h6>');
        redirect('claim/sheet');
    }

    // delete claim
    function delete($id_claim) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_claim = (int) $id_claim;
        $sql = "delete from claim where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_claim=$id_claim";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Claim successfully deleted');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Claim unsuccessfully deleted.</h6>');

        redirect('claim/sheet');
    }

    function get_table() {
        $this->load->model('claim_model');
        $table = $this->claim_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('claim_model');
        $query = $this->claim_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('claim_model');
        $query = $this->claim_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('claim_model');
        $query = $this->claim_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('claim_model');
        $query = $this->claim_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('claim_model');
        $this->claim_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('claim_model');
        $this->claim_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('claim_model');
        $this->claim_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('claim_model');
        $count = $this->claim_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('claim_model');
        $max_id = $this->claim_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('claim_model');
        $query = $this->claim_model->_custom_query($mysql_query);
        return $query;
    }

}
