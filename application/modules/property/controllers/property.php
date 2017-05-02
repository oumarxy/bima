<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Property extends MX_Controller {

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
            $id_customer = $this->uri->segment(3);
            if (is_numeric($id_customer))
                $data['id_customer'] = $id_customer;
            $sql = "select * from property where id_domain=" . $this->ion_auth->get_id_domain();
            $sql.=is_numeric($id_customer) ? " and id_customer=" . $id_customer : "";
            $sql.=" order by description";

            $data['properties'] = $this->_custom_query($sql)->result();


            $data['module'] = 'property';
            $data['view_file'] = 'index';
            $data['insurance_active'] = 'active';
            $data['property_information_active'] = 'active';
            $data['heading'] = 'Property';
            $data['subheading'] = 'Property information';
            echo Modules::run('template/login_area', $data);
        }
    }

// register property
    function register($id_customer) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {

            $id_customer = (int) $id_customer;
            $data['id_customer'] = $id_customer;

            $customer_count = Modules::run('customer/count_where', $data);
            if (!$customer_count) {
                $this->session->set_flashdata('message', '<h6 class="box-title text-aqua" >Search a record, under options column click <i class="fa fa-sticky-note-o"></i> to view and register property.</h6>');
                redirect('customer');
            }

            $this->form_validation->set_rules('property_type', 'Type', 'trim|required|xss_clean');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|xss_clean');
            if (isset($_POST['number']))
                $this->form_validation->set_rules('number', 'Number', 'trim|required|xss_clean');
            $this->form_validation->set_rules('property_value', 'Value', 'trim|xss_clean');
            $this->form_validation->set_rules('claim', 'Claim', 'trim|required|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $property['id_property_type'] = strtoupper($this->input->post('property_type', TRUE));
                $property['description'] = $this->input->post('description', TRUE);
                if ($this->input->post('number', TRUE) <> '')
                    $property['number'] = strtoupper($this->input->post('number', TRUE));
                //$property['year'] = $this->input->post('year', TRUE);
                $property['property_value'] = $this->input->post('property_value', TRUE);
                $property['claim'] = $this->input->post('claim', TRUE);
                $user = $this->ion_auth->user()->row();
                $property['user_id'] = $user->id;
                $property['id_domain'] = $this->ion_auth->get_id_domain();
                $property['id_customer'] = $id_customer;
                $this->_insert($property);
                move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/property_image/' . $this->db->insert_id() . '.jpg');
                $this->session->set_flashdata('message', 'Property successfully registered');

                redirect('property');
            }

            // display form
            $data['description'] = array(
                'name' => 'description',
                'value' => $this->form_validation->set_value('description')
            );
            $data['number'] = $this->form_validation->set_value('number');
            $data['property_value'] = array(
                'name' => 'property_value',
                'value' => $this->form_validation->set_value('property_value')
            );

            $data['claim'] = $this->form_validation->set_value('claim');
            $data['id_property_type'] = $this->form_validation->set_value('property_type');

            $data['status_claim'] = status_claim();
            $data['property_type_list'] = Modules::run('property_type/get', 'id_property_type')->result_array();

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'property';
            $data['view_file'] = 'form';
            $data['insurance_active'] = 'active';
            $data['property_information_active'] = 'active';
            $data['heading'] = 'Property';
            $data['subheading'] = 'fill form with property details';
            echo Modules::run('template/login_area', $data);
        }
    }

// edit property
    function edit($id_property) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {

            $id_property = (int) $id_property;
            $table = $this->get_table();
            $this->form_validation->set_rules('property_type', 'Type', 'trim|required|xss_clean');
            $this->form_validation->set_rules('description', 'Description', 'trim|required|xss_clean');
            $this->form_validation->set_rules('number', 'Number', 'trim|required|xss_clean');
            $this->form_validation->set_rules('property_value', 'Value', 'trim|xss_clean');
            $this->form_validation->set_rules('claim', 'Claim', 'trim|required|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $property['id_property_type'] = strtoupper($this->input->post('property_type', TRUE));
                $property['description'] = strtoupper($this->input->post('description', TRUE));
                if ($this->input->post('number', TRUE) <> '')
                    $property['number'] = strtoupper($this->input->post('number', TRUE));
                $property['property_value'] = $this->input->post('property_value', TRUE);
                $property['claim'] = $this->input->post('claim', TRUE);
                $this->_update($id_property, $property);
                move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/property_image/' . $id_property . '.jpg');
                $this->session->set_flashdata('message', 'Property successfully updated');

                redirect('property');
            }

            $data['id_property'] = $id_property;
            $sql = "select * from property where id_domain=" . $this->ion_auth->get_id_domain() . " and id_property=$id_property";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('property');
            }
            $property_row = $query->row();

            // display form
            $data['description'] = array(
                'name' => 'description',
                'value' => $this->form_validation->set_value('description', $property_row->description)
            );

            $data['number'] = $this->form_validation->set_value('number', $property_row->number);

            $data['property_value'] = array(
                'name' => 'property_value',
                'value' => $this->form_validation->set_value('property_value', $property_row->property_value)
            );

            $data['id_property_type'] = $this->form_validation->set_value('property_type', $property_row->id_property_type);

            $data['claim'] = $this->form_validation->set_value('claim', $property_row->claim);
            $data['property_type_list'] = Modules::run('property_type/get', 'id_property_type')->result_array();
            $data['status_claim'] = status_claim();

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'property';
            $data['view_file'] = 'form';
            $data['insurance_active'] = 'active';
            $data['property_information_active'] = 'active';
            $data['heading'] = 'Property';
            $data['subheading'] = 'fill form with property details';
            echo Modules::run('template/login_area', $data);
        }
    }

    // delete property
    function delete($id_property) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_property = (int) $id_property;
        $sql = "delete from property where 
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_property=$id_property";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Property Successfully Deleted');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Property Unsuccessfully Deleted.</h6>');

        redirect('property');
    }

    function view($id_property) {

        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            // set the flash data error message if there is one
            $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            if (is_numeric($id_property))
                $data['id_property'] = $id_property;
            $sql = "select * from property where id_property=$id_property and id_domain=" . $this->ion_auth->get_id_domain();

            $query = $this->_custom_query($sql);
                if (!$query->num_rows()) {
                redirect('property');
            }
            $data['property'] = $query->row();
            $id_customer = $query->row()->id_customer;
            $data['customer'] = Modules::run('customer/get_where_custom', array('id_customer' => $id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();

            $data['module'] = 'property';
            $data['view_file'] = 'view';
            $data['insurance_active'] = 'active';
            $data['property_information_active'] = 'active';
            $data['heading'] = 'Property';
            $data['subheading'] = 'Property information';
            echo Modules::run('template/login_area', $data);
        }
    }

    function get_table() {
        $this->load->model('property_model');
        $table = $this->property_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('property_model');
        $query = $this->property_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('property_model');
        $query = $this->property_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('property_model');
        $query = $this->property_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('property_model');
        $query = $this->property_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('property_model');
        $this->property_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('property_model');
        $this->property_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('property_model');
        $this->property_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('property_model');
        $count = $this->property_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('property_model');
        $max_id = $this->property_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('property_model');
        $query = $this->property_model->_custom_query($mysql_query);
        return $query;
    }

}
