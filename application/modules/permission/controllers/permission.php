<?php

if (!defined('BASEPATH'))    exit('No direct script access allowed');

class Permission extends MX_Controller {

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

            $data['perms'] = $this->get('perm_name')->result();
            $data['module'] = 'permission';
            $data['view_file'] = 'index';
            $data['role_management_active'] = 'active';
            $data['permission_active'] = 'active';
            $data['heading'] = 'Role Management';
            $data['subheading'] = 'permission list';
            echo Modules::run('template/login_area', $data);
        }
    }

// add permssion
    function add() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $table = $this->get_table();
            $this->form_validation->set_rules('perm_key', 'Key', 'trim|required|is_unique[' . $table . '.perm_key]|xss_clean');
            $this->form_validation->set_rules('perm_name', 'Description', 'trim|required|xss_clean');
            if ($this->form_validation->run() == TRUE) {
                $permission['perm_key'] = $this->input->post('perm_key', TRUE);
                $permission['perm_name'] = $this->input->post('perm_name', TRUE);
                $this->_insert($permission);
                $this->session->set_flashdata('message', 'Permission successfully added');


                redirect('permission');
            }

            // display adding form
            $data['perm_key'] = array(
                'name' => 'perm_key',
                'value' => $this->form_validation->set_value('perm_key')
            );

            $data['perm_name'] = array(
                'name' => 'perm_name',
                'value' => $this->form_validation->set_value('perm_name')
            );

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'permission';
            $data['view_file'] = 'form';
            $data['role_management_active'] = 'active';
            $data['permission_active'] = 'active';
            $data['heading'] = 'Role Management';
            $data['subheading'] = 'fill form with permission details';
            echo Modules::run('template/login_area', $data);
        }
    }

    //edit permission
    function edit($id_perm) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $id_perm = (int) $id_perm;
            $table = $this->get_table();
            $this->form_validation->set_rules('perm_key', 'Key', 'trim|required|edit_unique[' . $table . '.perm_key.' . $id_perm . ']|xss_clean');
            $this->form_validation->set_rules('perm_name', 'Description', 'trim|required|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $permission['perm_key'] = $this->input->post('perm_key', TRUE);
                $permission['perm_name'] = $this->input->post('perm_name', TRUE);
                $this->_update($id_perm, $permission);
                $this->session->set_flashdata('message', 'Permission successfully updated');


                redirect('permission');
            }
            $data['id_perm'] = $id_perm;
            $permission_row = $this->get_where($id_perm)->row();

            // display adding form
            $data['perm_key'] = array(
                'name' => 'perm_key',
                'value' => $this->form_validation->set_value('perm_key', $permission_row->perm_key)
            );

            $data['perm_name'] = array(
                'name' => 'perm_name',
                'value' => $this->form_validation->set_value('perm_name', $permission_row->perm_name)
            );

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'permission';
            $data['view_file'] = 'form';
            $data['role_management_active'] = 'active';
            $data['permission_active'] = 'active';
            $data['heading'] = 'Role Management';
            $data['subheading'] = 'fill form with permission details';
            echo Modules::run('template/login_area', $data);
        }
    }
    
    
       // delete permission
    function delete($id_perm) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_perm = (int) $id_perm;
        $this->_delete($id_perm);
        if($this->db->affected_rows()>0)
        $this->session->set_flashdata('message', 'Permission successfully deleted');
        else
         $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Permission unsuccessfully deleted.</h6>');   
           
        redirect('permission/index');
    }
    

    function get_table() {
        $this->load->model('permission_model');
        $table = $this->permission_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('permission_model');
        $query = $this->permission_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('permission_model');
        $query = $this->permission_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('permission_model');
        $query = $this->permission_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('permission_model');
        $query = $this->permission_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('permission_model');
        $this->permission_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('permission_model');
        $this->permission_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('permission_model');
        $this->permission_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('permission_model');
        $count = $this->permission_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('permission_model');
        $max_id = $this->permission_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('permission_model');
        $query = $this->permission_model->_custom_query($mysql_query);
        return $query;
    }

}
