<?php

if (!defined('BASEPATH'))  exit('No direct script access allowed');

class Claim_type extends MX_Controller {

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
            $sql = "select * from claim_type where id_domain=" . $this->ion_auth->get_id_domain();
            $sql.=" order by type";
            $data['claim_types'] = $this->_custom_query($sql)->result();

            $data['module'] = 'claim_type';
            $data['view_file'] = 'index';
            $data['setting_active'] = 'active';
            $data['claim_type_active'] = 'active';
            $data['heading'] = 'Claim';
            $data['subheading'] = 'claim type information';
            echo Modules::run('template/login_area', $data);
        }
    }

// add claim type
    function add() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {

            $table = $this->get_table();
            $this->form_validation->set_rules('type', 'Type', 'trim|required|is_unique[' . $table . '.type]|xss_clean');
            $this->form_validation->set_rules('description', 'Description', 'trim|xss_clean');
            $this->form_validation->set_rules('status', 'Status', '');

            if ($this->form_validation->run() == TRUE) {
                $claim_type['type'] = $this->input->post('type', TRUE);
                $claim_type['description'] = ucwords($this->input->post('description', TRUE));
                $claim_type['id_domain'] = $this->ion_auth->get_id_domain();
                $this->_insert($claim_type);
                $this->session->set_flashdata('message', 'Claim Type Successfully Created');
                redirect('claim_type');
            }

            // display form

            $data['type'] = array(
                'name' => 'type',
                'value' => $this->form_validation->set_value('type')
            );
            $data['description'] = array(
                'name' => 'description',
                'value' => $this->form_validation->set_value('description')
            );

            $data['status'] = $this->form_validation->set_value('status');

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'claim_type';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['claim_type_active'] = 'active';
            $data['heading'] = 'Claim';
            $data['subheading'] = 'fill form with claim type details';
            echo Modules::run('template/login_area', $data);
        }
    }

// edit claim type
    function edit($id_claim_type) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $id_claim_type = (int) $id_claim_type;
            $table = $this->get_table();
            $this->form_validation->set_rules('type', 'Type', 'trim|required|edit_unique[' . $table . '.type.' . $id_claim_type . ']');
            $this->form_validation->set_rules('description', 'Description', 'trim|xss_clean');
            $this->form_validation->set_rules('status', 'Status', '');
            if ($this->form_validation->run() == TRUE) {
                $claim_type['type'] = ucwords(trim($this->input->post('type', TRUE)));
                $claim_type['description'] = ucwords(trim($this->input->post('description', TRUE)));
                $claim_type['status'] = $this->input->post('status', TRUE);
                $this->_update($id_claim_type, $claim_type);
                $this->session->set_flashdata('message', 'Claim type successfully updated');
                redirect('claim_type');
            }

            $data['id_claim_type'] = $id_claim_type;
            $sql = "select * from claim_type where id_domain=" . $this->ion_auth->get_id_domain() . " and id_claim_type=$id_claim_type";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('claim_type');
            }
            $claim_type_row = $query->row();

            // display edit form

            $data['type'] = array(
                'name' => 'type',
                'value' => $this->form_validation->set_value('type', $claim_type_row->type)
            );
            $data['description'] = array(
                'name' => 'description',
                'value' => $this->form_validation->set_value('description', $claim_type_row->description)
            );

            $data['status'] = $this->form_validation->set_value('status', $claim_type_row->status);

            $data['status_type'] = status_types();
            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'claim_type';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['claim_type_active'] = 'active';
            $data['heading'] = 'Claim';
            $data['subheading'] = 'fill form with claim type details';
            echo Modules::run('template/login_area', $data);
        }
    }

    // delete claim type
    function delete($id_claim_type) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_claim_type = (int) $id_claim_type;
        $sql = "delete from claim_type where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_claim_type=$id_claim_type";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Claim type successfully deleted');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Claim type unsuccessfully deleted</h6>');
        redirect('claim_type');
    }

    function get_table() {
        $this->load->model('claim_type_model');
        $table = $this->claim_type_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('claim_type_model');
        $query = $this->claim_type_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('claim_type_model');
        $query = $this->claim_type_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('claim_type_model');
        $query = $this->claim_type_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('claim_type_model');
        $query = $this->claim_type_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('claim_type_model');
        $this->claim_type_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('claim_type_model');
        $this->claim_type_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('claim_type_model');
        $this->claim_type_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('claim_type_model');
        $count = $this->claim_type_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('claim_type_model');
        $max_id = $this->claim_type_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('claim_type_model');
        $query = $this->claim_type_model->_custom_query($mysql_query);
        return $query;
    }

}
