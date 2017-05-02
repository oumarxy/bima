<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Bank_account extends MX_Controller {

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

            $sql = "select * from bank_account where id_domain=" . $this->ion_auth->get_id_domain();
            $sql.=" order by bank_name";
            $data['bank_accounts'] = $this->_custom_query($sql)->result();

            $data['module'] = 'bank_account';
            $data['view_file'] = 'index';
            $data['setting_active'] = 'active';
            $data['bank_account_active'] = 'active';
            $data['heading'] = 'Bank account';
            $data['subheading'] = 'account details';
            echo Modules::run('template/login_area', $data);
        }
    }

// add account
    function add() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {

            $table = $this->get_table();
            $this->form_validation->set_rules('bank_name', 'Bank', 'trim|required|xss_clean');
            $this->form_validation->set_rules('account_name', 'Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|xss_clean');
            $this->form_validation->set_rules('account_number', 'Number', 'trim|required|is_unique[' . $table . '.account_number]|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $bank_account['bank_name'] = strtoupper($this->input->post('bank_name', TRUE));
                $bank_account['account_name'] = strtoupper($this->input->post('account_name', TRUE));
                $bank_account['description'] = strtoupper($this->input->post('description', TRUE));
                $bank_account['account_number'] = $this->input->post('account_number', TRUE);
                $bank_account['id_domain'] = $this->ion_auth->get_id_domain();
                $this->_insert($bank_account);
                if ($this->db->affected_rows() > 0)
                    $this->session->set_flashdata('message', 'Account successfully added');
                else
                    $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Account unsuccessfully added</h6>');

                redirect('bank_account');
            }

            // display form
            $data['bank_name'] = array(
                'name' => 'bank_name',
                'value' => $this->form_validation->set_value('bank_name')
            );
            $data['account_name'] = array(
                'name' => 'account_name',
                'value' => $this->form_validation->set_value('account_name')
            );

            $data['description'] = array(
                'name' => 'description',
                'value' => $this->form_validation->set_value('description')
            );

            $data['account_number'] = array(
                'name' => 'account_number',
                'value' => $this->form_validation->set_value('account_number')
            );

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'bank_account';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['bank_account_active'] = 'active';
            $data['heading'] = 'Bank account';
            $data['subheading'] = 'fill form with account details';
            echo Modules::run('template/login_area', $data);
        }
    }

// edit account
    function edit($id_bank_account) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {

            $id_bank_account = (int) $id_bank_account;
            $table = $this->get_table();
            $this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('account_name', 'Account Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|xss_clean');
            $this->form_validation->set_rules('account_number', 'Account Number', 'trim|required|edit_unique[' . $table . '.account_number.' . $id_bank_account . ']|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $bank_account['bank_name'] = strtoupper($this->input->post('bank_name', TRUE));
                $bank_account['account_name'] = strtoupper($this->input->post('account_name', TRUE));
                $bank_account['description'] = strtoupper($this->input->post('description', TRUE));
                $bank_account['account_number'] = $this->input->post('account_number', TRUE);
                $this->_update($id_bank_account, $bank_account);
                if ($this->db->affected_rows() > 0)
                    $this->session->set_flashdata('message', 'Account successfully updated');
                else
                    $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Account unsuccessfully updated</h6>');

                redirect('bank_account');
            }

            $sql = "select * from bank_account where id_domain=" . $this->ion_auth->get_id_domain() . " and id_bank_account=$id_bank_account";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('bank_account');
            }

            $data['id_bank_account'] = $id_bank_account;
            $bank_account_row = $query->row();

            // display form

            $data['bank_name'] = array(
                'name' => 'bank_name',
                'value' => $this->form_validation->set_value('bank_name', $bank_account_row->bank_name)
            );
            $data['account_name'] = array(
                'name' => 'account_name',
                'value' => $this->form_validation->set_value('account_name', $bank_account_row->account_name)
            );

            $data['description'] = array(
                'name' => 'description',
                'value' => $this->form_validation->set_value('description', $bank_account_row->description)
            );

            $data['account_number'] = array(
                'name' => 'account_number',
                'value' => $this->form_validation->set_value('account_number', $bank_account_row->account_number)
            );

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'bank_account';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['bank_account_active'] = 'active';
            $data['heading'] = 'Bank Account';
            $data['subheading'] = 'fill form with account details';
            echo Modules::run('template/login_area', $data);
        }
    }

    // delete account
    function delete($id_bank_account) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_bank_account = (int) $id_bank_account;
        $sql = "delete from bank_account where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_bank_account=$id_bank_account";

        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Account successfully deleted');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Account unsuccessfully deleted</h6>');

        redirect('bank_account');
    }

    function get_table() {
        $this->load->model('bank_account_model');
        $table = $this->bank_account_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('bank_account_model');
        $query = $this->bank_account_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('bank_account_model');
        $query = $this->bank_account_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('bank_account_model');
        $query = $this->bank_account_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('bank_account_model');
        $query = $this->bank_account_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('bank_account_model');
        $this->bank_account_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('bank_account_model');
        $this->bank_account_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('bank_account_model');
        $this->bank_account_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('bank_account_model');
        $count = $this->bank_account_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('bank_account_model');
        $max_id = $this->bank_account_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('bank_account_model');
        $query = $this->bank_account_model->_custom_query($mysql_query);
        return $query;
    }

}
