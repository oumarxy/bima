<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Bank_transaction extends MX_Controller {

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
            $sql = "select * from bank_transaction where id_domain=" . $this->ion_auth->get_id_domain();
            $sql.=" order by issued_on";
            $data['bank_transactions'] = $this->_custom_query($sql)->result();
            $data['module'] = 'bank_transaction';
            $data['view_file'] = 'index';
            $data['financial_active'] = 'active';
            $data['bank_transaction_active'] = 'active';
            $data['heading'] = 'Financial';
            $data['subheading'] = 'deposit transaction details';
            echo Modules::run('template/login_area', $data);
        }
    }

// add deposit
    function add($id_transaction_type) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $id_transaction_type = (int) $id_transaction_type;
            $data['id_transaction_type'] = $id_transaction_type;
            $transaction_type_count = Modules::run('transaction_type/count_where', $data);
            if (!$transaction_type_count) {
                $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Sorry, set transaction type and try again</h6>');
                redirect('bank_transaction', 'refresh');
            }
            $this->form_validation->set_rules('issued_on', 'Date Issued', 'trim|required|xss_clean');
            $this->form_validation->set_rules('bank_account', 'Account ', 'trim|required|xss_clean');
            if (isset($id_transaction_type) && $id_transaction_type <> 1)
                $this->form_validation->set_rules('cheque_number', 'Check Number', 'trim|required|numeric|xss_clean');
            else
                $this->form_validation->set_rules('cheque_number', 'Cheque', 'trim|numeric|xss_clean');

            $this->form_validation->set_rules('amount', 'Amount', 'trim|required|numeric|xss_clean');
            if (isset($id_transaction_type) && $id_transaction_type <> 1):
                $this->form_validation->set_rules('particular', 'Particular', 'trim|required|xss_clean');
            endif;
            $this->form_validation->set_rules('comment', 'Description', 'trim|required|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $bank_transaction['id_bank_account'] = $this->input->post('bank_account', TRUE);
                $bank_transaction['cheque_number'] = $this->input->post('cheque_number', TRUE);
                $bank_transaction['id_transaction_type'] = $id_transaction_type;
                $bank_transaction['amount'] = $this->input->post('amount', TRUE);
                $bank_transaction['particular'] = (isset($id_transaction_type) && $id_transaction_type <> 1) ? $this->input->post('particular', TRUE) : 'THORN LIMITED';
                $bank_transaction['comment'] = $this->input->post('comment', TRUE);
                $bank_transaction['user_id'] = $this->ion_auth->user()->row()->id;
                $bank_transaction['id_domain'] = $this->ion_auth->get_id_domain();
                $bank_transaction['issued_on'] = date_to_int_string($this->input->post('issued_on', TRUE));
                $this->_insert($bank_transaction);
                if ($this->db->affected_rows() > 0)
                    $this->session->set_flashdata('message', 'Transaction successfully recorded');
                else
                    $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Transaction unsuccessfully recorded</h6>');



                redirect('bank_transaction');
            }

            // display registration form
            $data['issued_on'] = array(
                'name' => 'issued_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('issued_on'),
            );

            $data['cheque_number'] = array(
                'name' => 'cheque_number',
                'value' => $this->form_validation->set_value('cheque_number')
            );

            $data['amount'] = array(
                'name' => 'amount',
                'value' => $this->form_validation->set_value('amount')
            );

            $data['particular'] = array(
                'name' => 'particular',
                'value' => $this->form_validation->set_value('particular')
            );

            $data['comment'] = array(
                'name' => 'comment',
                'value' => $this->form_validation->set_value('comment')
            );
            $data['id_bank_account'] = $this->form_validation->set_value('bank_account');
            
            $sql="select * from bank_account where id_domain=".$this->ion_auth->get_id_domain()." order by bank_name";
            $data['bank_account_list'] = Modules::run('bank_account/_custom_query',$sql)->result_array();

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'bank_transaction';
            $data['view_file'] = 'form';
            $data['financial_active'] = 'active';
            $data['bank_transaction_active'] = 'active';
            $data['heading'] = 'Financial';
            $data['subheading'] = 'fill form with transaction details';
            echo Modules::run('template/login_area', $data);
        }
    }

    //edit transaction
    function edit($id_bank_transaction) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } elseif (!$this->ion_auth->is_admin()) { // remove this elseif if you want to enable this for non-admins
            // redirect them to the home page because they must be an administrator to view this
            redirect('site/index', 'refresh');
            //return show_error('You must be an administrator to view this page.');
        } else {
            $id_bank_transaction = (int) $id_bank_transaction;
            $this->form_validation->set_rules('issued_on', 'Date Issued', 'trim|required|xss_clean');
            $this->form_validation->set_rules('bank_account', 'Bank ', 'trim|required|xss_clean');
            if (isset($id_transaction_type) && $id_transaction_type <> 1)
                $this->form_validation->set_rules('cheque_number', 'Cheque', 'trim|required|numeric|xss_clean');
            else
                $this->form_validation->set_rules('cheque_number', 'Cheque', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('amount', 'Amount', 'trim|required|numeric|xss_clean');
            if (isset($id_transaction_type) && $id_transaction_type <> 1):
                $this->form_validation->set_rules('particular', 'Particular', 'trim|required|xss_clean');
            endif;
            $this->form_validation->set_rules('comment', 'Description', 'trim|required|xss_clean');
            if ($this->form_validation->run() == TRUE) {
                $bank_transaction['id_bank_account'] = $this->input->post('bank_account', TRUE);
                $bank_transaction['cheque_number'] = $this->input->post('cheque_number', TRUE);
                $bank_transaction['amount'] = $this->input->post('amount', TRUE);
                $bank_transaction['particular'] = (isset($id_transaction_type) && $id_transaction_type <> 1) ? $this->input->post('particular', TRUE) : 'THORN LIMITED';
                $bank_transaction['comment'] = $this->input->post('comment', TRUE);
                $bank_transaction['issued_on'] = date_to_int_string($this->input->post('issued_on', TRUE));
                $this->_update($id_bank_transaction, $bank_transaction);
                if ($this->db->affected_rows() > 0)
                    $this->session->set_flashdata('message', 'Transaction successfully updated');
                else
                    $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Transaction unsuccessfully updated</h6>');



                redirect('bank_transaction');
            }
            
            $sql = "select * from bank_transaction where id_domain=" . $this->ion_auth->get_id_domain() . " and id_bank_transaction=$id_bank_transaction";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('bank_transaction');
            }
            $bank_transaction_row=$query->row();
            $id_transaction_type = $bank_transaction_row->id_transaction_type;
            $data['id_bank_transaction'] = $id_bank_transaction;
            $data['id_transaction_type'] = $id_transaction_type;
            $data['id_bank_account'] = $bank_transaction_row->id_bank_account;
            
            $sql="select * from bank_account where id_domain=".$this->ion_auth->get_id_domain()." order by bank_name";
            $data['bank_account_list'] = Modules::run('bank_account/_custom_query',$sql)->result_array();


            // display registration form
            $data['issued_on'] = array(
                'name' => 'issued_on',
                'data-inputmask' => "'alias': 'dd/mm/yyyy'",
                'data-mask' => '',
                'value' => $this->form_validation->set_value('issued_on', format_date('', $bank_transaction_row->issued_on)),
            );

            $data['cheque_number'] = array(
                'name' => 'cheque_number',
                'value' => $this->form_validation->set_value('cheque_number', $bank_transaction_row->cheque_number)
            );

            $data['amount'] = array(
                'name' => 'amount',
                'value' => $this->form_validation->set_value('amount', $bank_transaction_row->amount)
            );

            $data['particular'] = array(
                'name' => 'particular',
                'value' => $this->form_validation->set_value('particular', $bank_transaction_row->particular)
            );

            $data['comment'] = array(
                'name' => 'comment',
                'value' => $this->form_validation->set_value('comment', $bank_transaction_row->comment)
            );

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'bank_transaction';
            $data['view_file'] = 'form';
            $data['financial_active'] = 'active';
            $data['bank_transaction_active'] = 'active';
            $data['heading'] = 'Bank Deposit';
            $data['subheading'] = 'fill form with transaction details';
            echo Modules::run('template/login_area', $data);
        }
    }

    // delete transaction
    function delete($id_bank_transaction) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_bank_transaction = (int) $id_bank_transaction;
         $sql = "delete from bank_transaction where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_bank_transaction=$id_bank_transaction";
         $this->_custom_query($sql);  
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Transaction successfully deleted');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Transaction unsuccessfully deleted</h6>');

        redirect('bank_transaction');
    }

    function get_table() {
        $this->load->model('bank_transaction_model');
        $table = $this->bank_transaction_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('bank_transaction_model');
        $query = $this->bank_transaction_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('bank_transaction_model');
        $query = $this->bank_transaction_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('bank_transaction_model');
        $query = $this->bank_transaction_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('bank_transaction_model');
        $query = $this->bank_transaction_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('bank_transaction_model');
        $this->bank_transaction_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('bank_transaction_model');
        $this->bank_transaction_model->_update($id, $data);
    }

    function _update_all($data) {
        $this->load->model('bank_transaction_model');
        $this->bank_transaction_model->_update_all($data);
    }

    function _delete($id) {
        $this->load->model('bank_transaction_model');
        $this->bank_transaction_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('bank_transaction_model');
        $count = $this->bank_transaction_model->count_where($column, $value);
        return $count;
    }

    function count_all() {
        $this->load->model('bank_transaction_model');
        $count = $this->bank_transaction_model->count_all();
        return $count;
    }

    function sum_where($column, $value) {
        $this->load->model('bank_transaction_model');
        $sum = $this->bank_transaction_model->sum_where($column, $value);
        return $sum;
    }

    function get_max() {
        $this->load->model('bank_transaction_model');
        $max_id = $this->bank_transaction_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('bank_transaction_model');
        $query = $this->bank_transaction_model->_custom_query($mysql_query);
        return $query;
    }

}
