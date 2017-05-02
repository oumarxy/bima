<?php

if (!defined('BASEPATH'))    exit('No direct script access allowed');

class Payment extends MX_Controller {

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
            $sql = "select * from payment where id_domain=" . $this->ion_auth->get_id_domain() . " order by receipt";
            $data['payments'] = $this->_custom_query($sql)->result();
            $data['module'] = 'payment';
            $data['view_file'] = 'index';
            $data['financial_active'] = 'active';
            $data['payment_sheet_active'] = 'active';
            $data['heading'] = 'Financial';
            $data['subheading'] = 'payment sheet';
            echo Modules::run('template/login_area', $data);
        }
    }

// add payment
    function add() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $table = $this->get_table();
            $this->form_validation->set_rules('issued_on', 'Date Issued', 'trim|required|xss_clean');
            $this->form_validation->set_rules('cover_note', 'Cover Note', 'trim|required|is_exist[' . Modules::run('insurance/get_table') . '.cover_note]|xss_clean');
            $this->form_validation->set_rules('amount', 'Amount', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('receipt', 'Receipt', 'trim|required|is_unique[' . $table . '.receipt]|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $query = Modules::run('insurance/get_where_custom', array('cover_note' => $this->input->post('cover_note'), 'id_domain' => $this->ion_auth->get_id_domain()));
                if (!$query->num_rows()) {
                    redirect('payment/sheet');
                }
                $insurance=$query->row();
                $payment['id_insurance'] = $insurance->id_insurance;
                $payment['amount'] = $this->input->post('amount', TRUE);
                $payment['receipt'] = $this->input->post('receipt', TRUE);
                $payment['paid_on'] = date_to_int_string($this->input->post('issued_on', TRUE));
                $payment['user_id'] = $this->ion_auth->user()->row()->id;
                $payment['id_domain'] = $this->ion_auth->get_id_domain();
                $payment['status']=1;
                $this->_insert($payment);
                // fetch insurance premium
                $data['id_insurance'] = $payment['id_insurance'];
                $data['id_domain'] = $this->ion_auth->get_id_domain();
                $premium = Modules::run('insurance/get_where_custom', $data)->row()->premium;
                $amount = Modules::run('payment/sum_where', $data);
                $paid = ($amount >= $premium) ? 1 : 0;
                $sql = "update insurance set paid=$paid where id_insurance=" . $data['id_insurance'] . " and id_domain=" . $this->ion_auth->get_id_domain();
                Modules::run('insurance/_custom_query', $sql);

                $this->session->set_flashdata('message', 'Payment successfully added');


                redirect('payment/sheet');
            }

            // display registration form
            $data['issued_on'] = array(
                'name' => 'issued_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('issued_on'),
            );
            $data['cover_note'] = array(
                'name' => 'cover_note',
                'value' => $this->form_validation->set_value('cover_note')
            );

            $data['amount'] = array(
                'name' => 'amount',
                'value' => $this->form_validation->set_value('amount')
            );

            $data['receipt'] = array(
                'name' => 'receipt',
                'value' => $this->form_validation->set_value('receipt')
            );


            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'payment';
            $data['view_file'] = 'form';
            $data['financial_active'] = 'active';
            $data['add_payment_active'] = 'active';
            $data['heading'] = 'Financial';
            $data['subheading'] = 'fill form with payment details';
            echo Modules::run('template/login_area', $data);
        }
    }

    //edit payment
    function edit($id_payment) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $id_payment = (int) $id_payment;
            $table = $this->get_table();
            $this->form_validation->set_rules('issued_on', 'Date Issued', 'trim|required|xss_clean');
            $this->form_validation->set_rules('cover_note', 'Cover Note', 'trim|required|is_exist[' . Modules::run('insurance/get_table') . '.cover_note]|xss_clean');
            $this->form_validation->set_rules('amount', 'Amount', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('receipt', 'Receipt', 'trim|required|xss_clean|edit_unique[' . $table . '.receipt.' . $id_payment . ']');

            if ($this->form_validation->run() == TRUE) {
                $payment['amount'] = $this->input->post('amount', TRUE);
                $payment['receipt'] = $this->input->post('receipt', TRUE);
                $payment['paid_on'] = date_to_int_string($this->input->post('issued_on', TRUE));
                $payment['updated_on'] = time();
                $this->_update($id_payment, $payment);
                // fetch insurance premium
                $id_insurance = $this->get_where($id_payment)->row()->id_insurance;
                $data['id_insurance'] = $id_insurance;
                $data['id_domain']=$this->ion_auth->get_id_domain();
                $insurance_row = Modules::run('insurance/get_where_custom', $data)->row();
                $amount = Modules::run('payment/sum_where', $data);
                $insurance['paid'] = ($amount >= ($insurance_row->premium+$insurance_row->vat)) ? 1 : 0;
                Modules::run('insurance/_update', $id_insurance, $insurance);
                $this->session->set_flashdata('message', 'Payment successfully updated');


                redirect('payment/sheet');
            }
            $data['id_payment'] = $id_payment;
            $sql = "select * from payment where id_domain=" . $this->ion_auth->get_id_domain() . " and id_payment=$id_payment";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('payment/sheet');
            }
            $payment_row = $query->row();
            $id_insurance = $payment_row->id_insurance;
            $cover_note = Modules::run('insurance/get_where_custom', array('id_insurance' => $id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->cover_note;

            // display registration form

            $data['issued_on'] = array(
                'name' => 'issued_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('issued_on', format_date('', $payment_row->paid_on)),
            );

            $data['cover_note'] = array(
                'name' => 'cover_note',
                'readonly' => 'readonly',
                'value' => $this->form_validation->set_value('cover_note', $cover_note)
            );

            $data['amount'] = array(
                'name' => 'amount',
                'value' => $this->form_validation->set_value('amount', $payment_row->amount)
            );

            $data['receipt'] = array(
                'name' => 'receipt',
                'value' => $this->form_validation->set_value('receipt', $payment_row->receipt)
            );

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'payment';
            $data['view_file'] = 'form';
            $data['financial_active'] = 'active';
            $data['add_payment_active'] = 'active';
            $data['heading'] = 'Financial';
            $data['subheading'] = 'fill form with payment details';
            echo Modules::run('template/login_area', $data);
        }
    }

    // delete payment
    function delete($id_payment) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_payment = (int) $id_payment;
        $payment = Modules::run('payment/get_where_custom', array('id_payment' => $id_payment, 'id_domain' => $this->ion_auth->get_id_domain()));
        $sql = "delete from payment where
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_payment=$id_payment";

        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('message', 'Payment successfully deleted');
            $sql = "update insurance set paid=0 where id_insurance=" . $payment->row()->id_insurance . " and id_domain=" . $this->ion_auth->get_id_domain();
            Modules::run('insurance/_custom_query', $sql);
        } else {
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Payment unsuccessfully deleted.</h6>');
        }

        redirect('payment/sheet');
    }

// confirm payment
    function confirm($id_payment) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        if ($id_payment <> 'all')
            $id_payment = (int) $id_payment;
        $sql = "update payment set status=1,updated_on=" . time() . " where id_domain=" . $this->ion_auth->get_id_domain();
        $sql.= ($id_payment <> 'all') ? " and id_payment=$id_payment " : "";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Payment successfully confirmed');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Payment unsuccessfully confirmed</h6>');

        redirect('payment/sheet');
    }

    // confirm payment
    function unconfirm($id_payment) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        if ($id_payment <> 'all')
            $id_payment = (int) $id_payment;
        $sql = "update payment set status=0,updated_on=" . time() . " where id_domain=" . $this->ion_auth->get_id_domain();
        $sql.= ($id_payment <> 'all') ? " and id_payment=$id_payment " : "";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Payment successfully unconfirmed');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Payment unsuccessfully unconfirmed</h6>');

        redirect('payment/sheet');
    }

    function receipt($id_payment) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }
        $id_payment = (int) $id_payment;
        $query = Modules::run('payment/get_where_custom', array('id_payment' => $id_payment, 'id_domain' => $this->ion_auth->get_id_domain()));
        if (!$query->num_rows()) {
            redirect('payment/sheet');
        }

        $payment = $query->row();
        $insurance = Modules::run('insurance/get_where_custom', array('id_insurance' => $payment->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
        $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
        $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();



        define('SMARTWEB_PAGE', 'P');
        define('RECEIPT_SIZE', 'A5');
        $this->load->library('receipt');


        // set document information
        $this->receipt->SetCreator('Smartweb');
        $this->receipt->SetAuthor('Smartweb');
        $this->receipt->SetTitle('IBMS Receipt');
        $this->receipt->SetSubject('Smartweb');
        $this->receipt->SetKeywords('Smartweb', 'Receipt', 'IBMS');

        // set font
        $this->receipt->SetFont('times', '', 11);

        // footer margin
        $this->receipt->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $this->receipt->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // add a page
        $this->receipt->AddPage();
        $this->receipt->setCellHeightRatio(3);
        $x = $this->receipt->GetX();
        $y = $this->receipt->GetY() + 20;
        $this->receipt->SetXY($x, $y);
        $datestring = "%d/%m/%Y";
        $this->receipt->Cell(0, 0, 'Date: ' . format_date($datestring, $payment->paid_on), 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $this->receipt->SetXY($x + 100, $y);
        $this->receipt->SetFont('times', 'B', 10);
        $this->receipt->Cell(0, 0, 'No. ' . $payment->id_payment, 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $y = $this->receipt->GetY();
        $x = $this->receipt->GetX();
        $x2 = $x + 120;
        $this->receipt->Line($x, $y, $x2, $y);
        $this->receipt->SetFont('times', '', 11);
        $y = $this->receipt->GetY() + 5;
        $this->receipt->SetXY($x, $y);
        $this->receipt->Cell(0, 0, 'Received from ' . $customer->name . ' of ' . $customer->address . '.', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $this->receipt->Cell(0, 0, 'Cash/Cheque No ............................. Date ................................', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $x = $this->receipt->GetX() + 40;
        $y = $this->receipt->GetY() + 5;
        $this->receipt->SetXY($x, $y);
        $this->receipt->Cell(0, 0, 'PREMIUM ', 0, 1, 'L', 0, '', 0, false, 'T', 'M');
        $this->receipt->SetX($x);
        $this->receipt->Cell(0, 0, 'VAT 18%', 0, 1, 'L', 0, '', 0, false, 'T', 'M');
        $this->receipt->SetX($x);
        $this->receipt->Cell(0, 0, 'TOTAL ', 0, 1, 'L', 0, '', 0, false, 'T', 'M');
        $x = $x + 30;
        $this->receipt->SetXY($x, $y);
        $this->receipt->Cell(50, 1, number_format($insurance->premium) . '/=', 1, 1, 'R', 0, '', 0, false, 'T', 'M');
        $this->receipt->SetX($x);
        $this->receipt->Cell(50, 1, ($insurance->vat > 0) ? number_format($insurance->vat) . '/=' : '-', 1, 1, 'R', 0, '', 0, false, 'T', 'M');
        $this->receipt->SetX($x);
        $this->receipt->Cell(50, 1, number_format($insurance->premium + $insurance->vat) . '/=', 1, 1, 'R', 0, '', 0, false, 'T', 'M');
        $y = $this->receipt->GetY() + 5;
        $this->receipt->SetY($y);
        $this->receipt->Cell(0, 0, 'Amount in words: Shillings ..............................................................................', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $this->receipt->Cell(0, 0, '............................................................................................................................', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $x = $this->receipt->GetX();
        $y = $this->receipt->GetY();
        $this->receipt->SetXY($x, $y);
        $this->receipt->Line($x, $y, $x2, $y);
        $y = $this->receipt->GetY() + 3;
        $this->receipt->SetY($y);
        $this->receipt->Cell(0, 0, 'Details PREMIUM FOR ' . $property->number, 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $x = $this->receipt->GetX();
        $y = $this->receipt->GetY();
        $this->receipt->Cell(0, 0, 'Cover note No. ', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $x = $x + 100;
        $this->receipt->SetXY($x, $y);
        $this->receipt->Cell(0, 0, 'Shs. ', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $x = $this->receipt->GetX() + 95;
        $y = $this->receipt->GetY();
        $this->receipt->Cell(0, 0, $insurance->cover_note, 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $this->receipt->SetXY($x, $y);
        $this->receipt->Cell(0, 0, number_format($insurance->premium + $insurance->vat) . '/=', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $x = $this->receipt->GetX();
        $y = $this->receipt->GetY() - 2;
        $this->receipt->SetXY($x, $y);
        for ($i = 1; $i < 5; $i++) {
            $this->receipt->Line($x, $y, $x + 30, $y);
            $this->receipt->Line($x + 90, $y, $x + 120, $y);
            $y = $y + 5;
        }

        $this->receipt->SetY($y);
        $this->receipt->Cell(0, 0, 'Cashier\'s Signature ', 0, 1, 'L', 0, '', 0, false, 'M', 'M');
        $this->receipt->Cell(0, 0, '...............................', 0, 1, 'L', 0, '', 0, false, 'M', 'M');

        ob_end_clean();
        $this->receipt->Output('receipt_' . $insurance->cover_note . '.pdf', 'I');
    }

    function get_table() {
        $this->load->model('payment_model');
        $table = $this->payment_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('payment_model');
        $query = $this->payment_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('payment_model');
        $query = $this->payment_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('payment_model');
        $query = $this->payment_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('payment_model');
        $query = $this->payment_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('payment_model');
        $this->payment_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('payment_model');
        $this->payment_model->_update($id, $data);
    }

    function _update_all($data) {
        $this->load->model('payment_model');
        $this->payment_model->_update_all($data);
    }

    function _delete($id) {
        $this->load->model('payment_model');
        $this->payment_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('payment_model');
        $count = $this->payment_model->count_where($column, $value);
        return $count;
    }

    function count_all() {
        $this->load->model('payment_model');
        $count = $this->payment_model->count_all();
        return $count;
    }

    function sum_where($column, $value) {
        $this->load->model('payment_model');
        $sum = $this->payment_model->sum_where($column, $value);
        return $sum;
    }

    function get_max() {
        $this->load->model('payment_model');
        $max_id = $this->payment_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('payment_model');
        $query = $this->payment_model->_custom_query($mysql_query);
        return $query;
    }

}
