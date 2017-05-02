<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Assignment extends MX_Controller {

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

            $data['role_perms'] = $this->get('group_id')->result();
            $data['module'] = 'assignment';
            $data['view_file'] = 'index';
            $data['role_management_active'] = 'active';
            $data['assignment_active'] = 'active';
            $data['heading'] = 'Assignment';
            $data['subheading'] = 'permission assignment list';
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
            $this->form_validation->set_rules('role', 'Role', 'trim|required|xss_clean');
            $this->form_validation->set_rules('perm', 'Permission', 'trim|required|xss_clean');
            $this->form_validation->set_rules('grant', 'Grant', 'trim|required|xss_clean');
            if ($this->form_validation->run() == TRUE) {
                $assignment['group_id'] = $this->input->post('role', TRUE);
                $assignment['perm_id'] = $this->input->post('perm', TRUE);
                $assignment['value'] = $this->input->post('grant', TRUE);
                $sql = "select * from " . $this->get_table() . " where group_id=" . $assignment['group_id'] . " and perm_id=" . $assignment['perm_id'];
                $query = $this->_custom_query($sql);
                if ($query->num_rows() == 0) {
                    $this->_insert($assignment);
                    $this->session->set_flashdata('message', 'Assignment successfully added');
                } else {
                    $this->session->set_flashdata('message', '<p class="text-aqua" >Assignment with selected particulars found in system.</p>');
                }
                redirect('assignment');
            }

            // display adding form
            $data['id_role'] = $this->form_validation->set_value('role');
            $data['id_perm'] = $this->form_validation->set_value('perm');
            $data['id_grant'] = $this->form_validation->set_value('grant');

            $roles = $this->ion_auth->groups()->result_array();
            $perms = Modules::run('permission/get', 'perm_name')->result_array();
            $data['roles'] = $roles;
            $data['perms'] = $perms;


            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'assignment';
            $data['view_file'] = 'form';
            $data['role_management_active'] = 'active';
            $data['assignment_active'] = 'active';
            $data['heading'] = 'Assignment';
            $data['subheading'] = 'fill form with assignment details';
            echo Modules::run('template/login_area', $data);
        }
    }

   // remove permission
    function remove($id_perm) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_perm = (int) $id_perm;
        $assignment['value'] = 0;
        $this->_update($id_perm, $assignment);
        $this->session->set_flashdata('message', 'Permission successfully removed');
        redirect('assignment/index');
    }
    
    // grant permission
    function grant($id_perm) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_perm = (int) $id_perm;
        $assignment['value'] = 1;
        $this->_update($id_perm, $assignment);
        $this->session->set_flashdata('message', 'Permission successfully granted');
        redirect('assignment/index');
    }
    

    function get_table() {
        $this->load->model('assignment_model');
        $table = $this->assignment_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('assignment_model');
        $query = $this->assignment_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('assignment_model');
        $query = $this->assignment_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('assignment_model');
        $query = $this->assignment_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('assignment_model');
        $query = $this->assignment_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('assignment_model');
        $this->assignment_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('assignment_model');
        $this->assignment_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('assignment_model');
        $this->assignment_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('assignment_model');
        $count = $this->assignment_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('assignment_model');
        $max_id = $this->assignment_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('assignment_model');
        $query = $this->assignment_model->_custom_query($mysql_query);
        return $query;
    }

}
