<?php

if (!defined('BASEPATH'))  exit('No direct script access allowed');

class Vehicle_class extends MX_Controller {

    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->library(array('auth/ion_auth', 'form_validation'));
        $this->load->helper(array('url', 'language'));

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
    }

// default function
    function index() {

         if (!$this->ion_auth->logged_in() ) {
             // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) { 
            // redirect them to the home page because they must be an access to view this
            $this->show_error();
        } else {
            // set the flash data error message if there is one
            $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $data['vehicle_classes'] = $this->get('class')->result();

            $data['module'] = 'vehicle_class';
            $data['view_file'] = 'index';
            $data['setting_active'] = 'active';
            $data['vehicle_class_active'] = 'active';
            $data['heading'] = 'Vehicle';
            $data['subheading'] = 'Vehicle class informationn';
            echo Modules::run('template/login_area', $data);
        }
    }

// add vehicle class
    function add() {
        if (!$this->ion_auth->logged_in() ) {
             // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) { 
            // redirect them to the home page because they must be an access to view this
            $this->show_error();
        } else {


            $table = $this->get_table();
            $this->form_validation->set_rules('class', 'Class', 'trim|required|is_unique[' . $table . '.class]|xss_clean');
            $this->form_validation->set_rules('description', 'Description', 'trim|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $vehicle_class['class'] = $this->input->post('class', TRUE);
                $vehicle_class['description'] = $this->input->post('description', TRUE);
                $this->_insert($vehicle_class);
                $this->session->set_flashdata('message', 'Vehicle Class Successfully Created');

                redirect('vehicle_class');
            }

            // display form
            $data['class'] = array(
                'name' => 'class',
                'value' => $this->form_validation->set_value('class')
            );
            $data['description'] = array(
                'name' => 'description',
                'value' => $this->form_validation->set_value('description')
            );
            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'vehicle_class';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['vehicle_class_active'] = 'active';
            $data['heading'] = 'Vehicle';
            $data['subheading'] = 'fill form with vehicle class details';
            echo Modules::run('template/login_area', $data);
        }
    }

// edit vehicle class
    function edit($id_vehicle_class) {
       if (!$this->ion_auth->logged_in() ) {
             // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) { 
            // redirect them to the home page because they must be an access to view this
            $this->show_error();
        } else {

            $id_vehicle_class = (int) $id_vehicle_class;
            $table = $this->get_table();
            $this->form_validation->set_rules('class', 'Class', 'trim|required|edit_unique[' . $table . '.class.' . $id_vehicle_class . ']');
            $this->form_validation->set_rules('description', 'Description', 'trim|xss_clean');
            $this->form_validation->set_rules('status', 'Status', 'trim|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $vehicle_class['class'] = $this->input->post('class', TRUE);
                $vehicle_class['description'] = $this->input->post('description', TRUE);
                $vehicle_class['status'] = $this->input->post('status', TRUE);
                $this->_update($id_vehicle_class, $vehicle_class);
                $this->session->set_flashdata('message', 'Vehicle Class Successfully Updated');

                redirect('vehicle_class');
            }

            $data['id_vehicle_class'] = $id_vehicle_class;
            $vehicle_class_row = $this->get_where($id_vehicle_class)->row();

            // display form
            $data['class'] = array(
                'name' => 'class',
                'value' => $this->form_validation->set_value('class', $vehicle_class_row->class)
            );
            $data['description'] = array(
                'name' => 'description',
                'value' => $this->form_validation->set_value('description', $vehicle_class_row->description)
            );

            $status = $this->form_validation->set_value('description', $vehicle_class_row->status);
            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'vehicle_class';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['vehicle_class_active'] = 'active';
            $data['heading'] = 'Vehicle';
            $data['subheading'] = 'fill form with vehicle class details';
            $data['status_type'] = status_types();
            echo Modules::run('template/login_area', $data);
        }
    }

    // delete vehicle class
    function delete($id_vehicle_class) {
       if (!$this->ion_auth->logged_in() ) {
             // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) { 
            // redirect them to the home page because they must be an access to view this
            $this->show_error();
        }

        $id_vehicle_class = (int) $id_vehicle_class;
        $this->_delete($id_vehicle_class);
        $this->session->set_flashdata('message', 'Vehicle Class Successfully Deleted');
        redirect('vehicle_class');
    }

    function get_table() {
        $this->load->model('vehicle_class_model');
        $table = $this->vehicle_class_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('vehicle_class_model');
        $query = $this->vehicle_class_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('vehicle_class_model');
        $query = $this->vehicle_class_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('vehicle_class_model');
        $query = $this->vehicle_class_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('vehicle_class_model');
        $query = $this->vehicle_class_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('vehicle_class_model');
        $this->vehicle_class_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('vehicle_class_model');
        $this->vehicle_class_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('vehicle_class_model');
        $this->vehicle_class_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('vehicle_class_model');
        $count = $this->vehicle_class_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('vehicle_class_model');
        $max_id = $this->vehicle_class_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('vehicle_class_model');
        $query = $this->vehicle_class_model->_custom_query($mysql_query);
        return $query;
    }

}
