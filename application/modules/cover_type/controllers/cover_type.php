<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cover_type extends MX_Controller {

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
            $sql = "select * from cover_type where id_domain=" . $this->ion_auth->get_id_domain();
            $sql.=" order by type";
            $data['cover_types'] = $this->_custom_query($sql)->result();

            $data['module'] = 'cover_type';
            $data['view_file'] = 'index';
            $data['setting_active'] = 'active';
            $data['cover_type_active'] = 'active';
            $data['heading'] = 'Cover';
            $data['subheading'] = 'cover type information';
            echo Modules::run('template/login_area', $data);
        }
    }

// add cover type
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
                $cover_type['type'] = ucwords(trim($this->input->post('type', TRUE)));
                $cover_type['description'] = ucwords(trim($this->input->post('description', TRUE)));
                $cover_type['id_domain'] = $this->ion_auth->get_id_domain();
                $this->_insert($cover_type);
                $this->session->set_flashdata('message', 'Cover type successfully created');

                redirect('cover_type');
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


            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'cover_type';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['cover_type_active'] = 'active';
            $data['heading'] = 'Cover';
            $data['subheading'] = 'fill form with cover type details';
            echo Modules::run('template/login_area', $data);
        }
    }

// edit cover type
    function edit($id_cover_type) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {

            $id_cover_type = (int) $id_cover_type;
            $table = $this->get_table();
            $this->form_validation->set_rules('type', 'Type', 'trim|required|edit_unique[' . $table . '.type.' . $id_cover_type . ']');
            $this->form_validation->set_rules('description', 'Description', 'trim|xss_clean');
            $this->form_validation->set_rules('status', 'Status', '');

            if ($this->form_validation->run() == TRUE) {
                $cover_type['type'] = $this->input->post('type', TRUE);
                $cover_type['description'] = $this->input->post('description', TRUE);
                $cover_type['status'] = $this->input->post('status', TRUE);
                $this->_update($id_cover_type, $cover_type);
                $this->session->set_flashdata('message', 'Cover type successfully updated');
                redirect('cover_type');
            }

            $data['id_cover_type'] = $id_cover_type;
            $sql = "select * from cover_type where id_domain=" . $this->ion_auth->get_id_domain() . " and id_cover_type=$id_cover_type";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('cover_type');
            }
            $cover_type_row = $query->row();

            // display form
            $data['type'] = array(
                'name' => 'type',
                'value' => $this->form_validation->set_value('type', $cover_type_row->type)
            );
            $data['description'] = array(
                'name' => 'description',
                'value' => $this->form_validation->set_value('description', $cover_type_row->description)
            );

            $data['status'] = $this->form_validation->set_value('status', $cover_type_row->status);
            $data['status_type'] = status_types();
            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'cover_type';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['cover_type_active'] = 'active';
            $data['heading'] = 'Cover';
            $data['subheading'] = 'fill form with cover type details';
            $data['status_type'] = status_types();
            echo Modules::run('template/login_area', $data);
        }
    }

// delete cover type
    function delete($id_cover_type) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_cover_type = (int) $id_cover_type;
        $sql = "delete from cover_type where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_cover_type=$id_cover_type";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Cover type successfully deleted');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Cover type unsuccessfully deleted</h6>');
        redirect('cover_type');
    }

    function get_table() {
        $this->load->model('cover_type_model');
        $table = $this->cover_type_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('cover_type_model');
        $query = $this->cover_type_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('cover_type_model');
        $query = $this->cover_type_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('cover_type_model');
        $query = $this->cover_type_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('cover_type_model');
        $query = $this->cover_type_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('cover_type_model');
        $this->cover_type_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('cover_type_model');
        $this->cover_type_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('cover_type_model');
        $this->cover_type_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('cover_type_model');
        $count = $this->cover_type_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('cover_type_model');
        $max_id = $this->cover_type_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('cover_type_model');
        $query = $this->cover_type_model->_custom_query($mysql_query);
        return $query;
    }

}
