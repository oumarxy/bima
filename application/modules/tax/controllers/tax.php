<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Tax extends MX_Controller {

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
            // redirect them to the home page because they must be an access to view this
            $this->show_error();
        } else {
            // set the flash data error message if there is one
            $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            $sql = "select * from tax where id_domain=" . $this->ion_auth->get_id_domain();
            $sql.=" order by type";
            $data['taxes'] = $this->_custom_query($sql)->result();

            $data['module'] = 'tax';
            $data['view_file'] = 'index';
            $data['setting_active'] = 'active';
            $data['tax_active'] = 'active';
            $data['heading'] = 'Tax';
            $data['subheading'] = 'tax information';
            echo Modules::run('template/login_area', $data);
        }
    }

// add tax
    function add() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            // redirect them to the home page because they must be an access to view this
            $this->show_error();
        } else {

            $table = $this->get_table();

            $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
            $this->form_validation->set_rules('percentage', 'Percentage', 'trim|required|numeric|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $tax['type'] = $this->input->post('type', TRUE);
                $tax['percentage'] = $this->input->post('percentage', TRUE);
                $tax['id_domain'] = $this->ion_auth->get_id_domain();
                $this->_insert($tax);
                $this->session->set_flashdata('message', 'Tax successfully added');

                redirect('tax');
            }

            // display form

            $data['percentage'] = array(
                'name' => 'percentage',
                'value' => $this->form_validation->set_value('percentage')
            );

            $data['type'] = $this->form_validation->set_value('type');

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'tax';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['tax_active'] = 'active';
            $data['heading'] = 'Tax';
            $data['subheading'] = 'fill form with tax details';
            $data['tax_type_list'] = tax_type_list();
            echo Modules::run('template/login_area', $data);
        }
    }

// edit tax
    function edit($id_tax) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            // redirect them to the home page because they must be an access to view this
            $this->show_error();
        } else {

            $table = $this->get_table();
            $this->form_validation->set_rules('type', 'Type', 'trim|required');
            $this->form_validation->set_rules('percentage', 'Percentage', 'trim|required|numeric|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $tax['type'] = $this->input->post('type', TRUE);
                $tax['percentage'] = $this->input->post('percentage', TRUE);
                $tax['status'] = $this->input->post('status', TRUE);
                $this->_update($id_tax, $tax);
                $this->session->set_flashdata('message', 'Tax successfully updated');

                redirect('tax');
            }

            $sql = "select * from tax where id_domain=" . $this->ion_auth->get_id_domain() . " and id_tax=$id_tax";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('tax');
            }

            $data['id_tax'] = $id_tax;
            $tax_row = $query->row();

            // display form
            $data['type'] = $this->form_validation->set_value('type', $tax_row->type);

            $data['percentage'] = array(
                'name' => 'percentage',
                'value' => $this->form_validation->set_value('description', $tax_row->percentage)
            );

            $data['status'] = $this->form_validation->set_value('status', $tax_row->status);
            $data['status_type'] = status_types();
            $data['tax_type_list'] = tax_type_list();
            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'tax';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['tax_active'] = 'active';
            $data['heading'] = 'Tax';
            $data['subheading'] = 'fill form with tax details';
            echo Modules::run('template/login_area', $data);
        }
    }

    // delete tax
    function delete($id_tax) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            // redirect them to the home page because they must be an access to view this
            $this->show_error();
        }

        $id_tax = (int) $id_tax;
        $sql = "delete from tax where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_tax=$id_tax";

        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Tax successfully deleted');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Tax unsuccessfully deleted</h6>');
        redirect('tax');
    }

    function get_table() {
        $this->load->model('tax_model');
        $table = $this->tax_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('tax_model');
        $query = $this->tax_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('tax_model');
        $query = $this->tax_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('tax_model');
        $query = $this->tax_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('tax_model');
        $query = $this->tax_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('tax_model');
        $this->tax_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('tax_model');
        $this->tax_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('tax_model');
        $this->tax_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('tax_model');
        $count = $this->tax_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('tax_model');
        $max_id = $this->tax_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('tax_model');
        $query = $this->tax_model->_custom_query($mysql_query);
        return $query;
    }

}
