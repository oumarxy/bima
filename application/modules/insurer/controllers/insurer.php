<?php

if (!defined('BASEPATH'))    exit('No direct script access allowed');

class Insurer extends MX_Controller {

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
            $sql = "select * from insurer where id_domain=" . $this->ion_auth->get_id_domain();
            $sql.=" order by name";

            $data['insurers'] = $this->_custom_query($sql)->result();

            $data['module'] = 'insurer';
            $data['view_file'] = 'index';
            $data['setting_active'] = 'active';
            $data['insurer_active'] = 'active';
            $data['heading'] = 'Insurer';
            $data['subheading'] = 'Insurer information';
            echo Modules::run('template/login_area', $data);
        }
    }

    // default function
        function active_insurer() {

            if (!$this->ion_auth->logged_in()) {
                // redirect them to the login page
                redirect('site/login', 'refresh');
            } elseif (!$this->check_access()) {
                $this->show_error();
            } else {
              $today = date_to_int_string(date('d/m/Y'));
              $where_clause.=' insurance.closed<1 and insurer.status=1 and insurance.id_domain='.$this->ion_auth->get_id_domain().' and  insurer.id_domain=' . $this->ion_auth->get_id_domain().' and insurance.expired_on > '.$today;
              $sql = "select  distinct insurer.id_insurer,insurer.name,insurer.phone,insurer.contact_person,insurer.email,insurer.status from insurer inner join insurance on insurer.id_insurer=insurance.id_insurer";
              $sql.=($where_clause <> '') ? " where $where_clause " : "";
              $sql.=" order by name";

              $data['insurers'] = $this->_custom_query($sql)->result();

              $data['module'] = 'insurer';
              $data['view_file'] = 'index';
              $data['setting_active'] = 'active';
              $data['insurer_active'] = 'active';
              $data['heading'] = 'Insurer';
              $data['subheading'] = 'active insurer information';
              echo Modules::run('template/login_area', $data);
            }
        }

// add insurer
    function add() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {

            $table = $this->get_table();
            $this->form_validation->set_rules('name', 'Name', 'trim|required|is_unique[' . $table . '.name]|xss_clean');
            $this->form_validation->set_rules('phone', 'Phone', 'trim|required|validate_phone|xss_clean');
            $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|xss_clean');
            $this->form_validation->set_rules('address', 'Address', 'trim|xss_clean');
            $this->form_validation->set_rules('contact_person', 'Contact Person', 'trim|required|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $insurer['name'] = format_name($this->input->post('name', TRUE));
                $insurer['address'] = $this->input->post('address', TRUE);
                $insurer['contact_person'] = format_name($this->input->post('contact_person', TRUE));
                $insurer['phone'] = $this->input->post('phone', TRUE);
                $insurer['email'] = $this->input->post('email', TRUE);
                $insurer['id_domain'] = $this->ion_auth->get_id_domain();
                $this->_insert($insurer);
                $this->session->set_flashdata('message', 'Insurer successfully registered');


                redirect('insurer');
            }

            // display form
            $data['name'] = array(
                'name' => 'name',
                'value' => $this->form_validation->set_value('name')
            );
            $data['address'] = array(
                'name' => 'address',
                'value' => $this->form_validation->set_value('address')
            );
            $data['contact_person'] = array(
                'name' => 'contact_person',
                'value' => $this->form_validation->set_value('contact_person')
            );
            $data['phone'] = $this->form_validation->set_value('phone');

            $data['email'] = array(
                'name' => 'email',
                'value' => $this->form_validation->set_value('email')
            );
            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'insurer';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['insurer_active'] = 'active';
            $data['heading'] = 'Insurer';
            $data['subheading'] = 'fill form with insurer details';
            $data['status_type'] = status_types();
            echo Modules::run('template/login_area', $data);
        }
    }

// edit insurer
    function edit($id_insurer) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {

            $id_insurer = (int) $id_insurer;
            $table = $this->get_table();
            $this->form_validation->set_rules('name', 'Name', 'trim|required|edit_unique[' . $table . '.name.' . $id_insurer . ']|xss_clean');
            $this->form_validation->set_rules('phone', 'Phone', 'trim|required|validate_phone|xss_clean');
            $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|xss_clean');
            $this->form_validation->set_rules('address', 'Address', 'trim|xss_clean');
            $this->form_validation->set_rules('contact_person', 'Contact Person', 'trim|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $insurer['name'] = format_name($this->input->post('name', TRUE));
                $insurer['address'] = $this->input->post('address', TRUE);
                $insurer['contact_person'] = format_name($this->input->post('contact_person', TRUE));
                $insurer['phone'] = $this->input->post('phone', TRUE);
                $insurer['email'] = $this->input->post('email', TRUE);
                $insurer['status'] = $this->input->post('status', TRUE);
                $this->_update($id_insurer, $insurer);
                $this->session->set_flashdata('message', 'Insurer successfully updated');

                redirect('insurer');
            }

            $data['id_insurer'] = $id_insurer;
            $sql = "select * from insurer where id_domain=" . $this->ion_auth->get_id_domain() . " and id_insurer=$id_insurer";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('insurer');
            }
            $insurer_row = $query->row();

            // display form
            $data['name'] = array(
                'name' => 'name',
                'value' => $this->form_validation->set_value('name', $insurer_row->name)
            );
            $data['address'] = array(
                'name' => 'address',
                'value' => $this->form_validation->set_value('address', $insurer_row->address)
            );
            $data['contact_person'] = array(
                'name' => 'contact_person',
                'value' => $this->form_validation->set_value('contact_person', $insurer_row->contact_person)
            );

            $data['phone'] = $this->form_validation->set_value('phone', $insurer_row->phone);

            $data['email'] = array(
                'name' => 'email',
                'value' => $this->form_validation->set_value('email', $insurer_row->email)
            );

            $data['status'] = $this->form_validation->set_value('status', $insurer_row->status);

            $data['status_type'] = status_types();
            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'insurer';
            $data['view_file'] = 'form';
            $data['setting_active'] = 'active';
            $data['insurer_active'] = 'active';
            $data['heading'] = 'Insurer';
            $data['subheading'] = 'fill form with insurer details';
            echo Modules::run('template/login_area', $data);
        }
    }

    // delete insurer
    function delete($id_insurer) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_insurer = (int) $id_insurer;
        $sql = "delete from insurer where
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_insurer=$id_insurer";
        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Insurer successfully deleted');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Insurer unsuccessfully deleted</h6>');
        redirect('insurer');
    }

    function get_table() {
        $this->load->model('insurer_model');
        $table = $this->insurer_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('insurer_model');
        $query = $this->insurer_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('insurer_model');
        $query = $this->insurer_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('insurer_model');
        $query = $this->insurer_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('insurer_model');
        $query = $this->insurer_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('insurer_model');
        $this->insurer_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('insurer_model');
        $this->insurer_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('insurer_model');
        $this->insurer_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('insurer_model');
        $count = $this->insurer_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('insurer_model');
        $max_id = $this->insurer_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('insurer_model');
        $query = $this->insurer_model->_custom_query($mysql_query);
        return $query;
    }

}
