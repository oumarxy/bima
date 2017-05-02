<?php

if (!defined('BASEPATH'))  exit('No direct script access allowed');

class Customer extends MX_Controller {

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
            $sql = "select * from customer where id_domain=" . $this->ion_auth->get_id_domain();
            $sql.=is_numeric($id_customer) ? " and id_customer=" . $id_customer : "";
            $sql.=" order by name";
            $data['customers'] = $this->_custom_query($sql)->result();

            $data['module'] = 'customer';
            $data['view_file'] = 'index';
            $data['customer_active'] = 'active';
            $data['customer_information_active'] = 'active';
            $data['heading'] = 'Customer';
            $data['subheading'] = 'customer information';
            echo Modules::run('template/login_area', $data);
        }
    }

        // active customer function
        function active_customer() {

            if (!$this->ion_auth->logged_in()) {
                // redirect them to the login page
                redirect('site/login', 'refresh');
            } elseif (!$this->check_access()) {
                $this->show_error();
            } else {
              $today = date_to_int_string(date('d/m/Y'));
            	$where_clause.=' insurance.closed<1 and customer.status=1 and customer.id_domain=' . $this->ion_auth->get_id_domain().' and expired_on > '.$today
            	;

            	$sql = "select distinct customer.id_customer,customer.name,customer.address,customer.phone,
              customer.registered_on,customer.updated_on,customer.status from customer inner join property on customer.id_customer=property.id_customer
            	          inner join insurance on property.id_property=insurance.id_property";
            	$sql.=($where_clause <> '') ? " where $where_clause " : "";
              $sql.=' order by customer.name';

              $data['customers'] = $this->_custom_query($sql)->result();

                $data['module'] = 'customer';
                $data['view_file'] = 'index';
                $data['customer_active'] = 'active';
                $data['customer_information_active'] = 'active';
                $data['heading'] = 'Customer';
                $data['subheading'] = 'active customer information';
                echo Modules::run('template/login_area', $data);
            }
        }


// register customer
    function register() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $table = $this->get_table();
            $this->form_validation->set_rules('name', 'Name', 'trim|required|is_unique[' . $table . '.name]|xss_clean');
            $this->form_validation->set_rules('address', 'Address', 'trim|xss_clean');
            $this->form_validation->set_rules('phone', 'Phone', 'trim|validate_phone|xss_clean');
            $this->form_validation->set_rules('status', 'Status', '');

            if ($this->form_validation->run() == TRUE) {
                $customer['name'] = strtoupper($this->input->post('name', TRUE));
                $customer['address'] = $this->input->post('address', TRUE);
                $customer['phone'] = $this->input->post('phone', TRUE);
                $customer['user_id'] = $this->ion_auth->user()->row()->id;
                $customer['id_domain'] = $this->ion_auth->get_id_domain();
                $customer['registered_on'] = time();
                $this->_insert($customer);
                $this->session->set_flashdata('message', 'Customer successfully registered');

                redirect('customer');
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
            $data['phone'] = $this->form_validation->set_value('phone');

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'customer';
            $data['view_file'] = 'form';
            $data['customer_active'] = 'active';
            $data['register_customer_active'] = 'active';
            $data['heading'] = 'Customer';
            $data['subheading'] = 'fill form with customer details';
            echo Modules::run('template/login_area', $data);
        }
    }

// edit customer
    function edit($id_customer) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $id_customer = (int) $id_customer;
            $table = $this->get_table();
            $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean|edit_unique[' . $table . '.name.' . $id_customer . ']');
            $this->form_validation->set_rules('address', 'Address', 'trim|xss_clean');
            $this->form_validation->set_rules('phone', 'Phone', 'trim|validate_phone|xss_clean');
            $this->form_validation->set_rules('status', 'Status', '');

            if ($this->form_validation->run() == TRUE) {
                $customer['name'] = strtoupper($this->input->post('name', TRUE));
                $customer['address'] = strtoupper($this->input->post('address', TRUE));
                $customer['phone'] = $this->input->post('phone', TRUE);
                $customer['status'] = $this->input->post('status', TRUE);
                $customer['updated_on'] = time();
                $this->_update($id_customer, $customer);
                $this->session->set_flashdata('message', 'Customer successfully updated');

                redirect('customer');
            }

            $data['id_customer'] = $id_customer;
            $sql = "select * from customer where id_domain=" . $this->ion_auth->get_id_domain() . " and id_customer=$id_customer";
            $query = $this->_custom_query($sql);
            if (!$query->num_rows()) {
                redirect('customer');
            }
            $customer_row = $query->row();

            // display form
            $data['name'] = array(
                'name' => 'name',
                'value' => $this->form_validation->set_value('name', $customer_row->name)
            );
            $data['address'] = array(
                'name' => 'address',
                'value' => $this->form_validation->set_value('address', $customer_row->address)
            );

            $data['phone'] = $this->form_validation->set_value('phone', $customer_row->phone);

            $data['status'] = $this->form_validation->set_value('status', $customer_row->status);
            $data['status_type'] = status_types();

            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            $data['module'] = 'customer';
            $data['view_file'] = 'form';
            $data['customer_active'] = 'active';
            $data['register_customer_active'] = 'active';
            $data['heading'] = 'Customer';
            $data['subheading'] = 'fill form with customer details';
            echo Modules::run('template/login_area', $data);
        }
    }

    // delete customer
    function delete($id_customer) {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $id_customer = (int) $id_customer;
        $data['id_customer'] = $id_customer;
        $sql = "delete from customer where
                id_domain=" . $this->ion_auth->get_id_domain() . " and id_customer=$id_customer";

        $this->_custom_query($sql);
        if ($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', 'Customer successfully deleted');
        else
            $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Customer unsuccessfully deleted.</h6>');


        redirect('customer');
    }

// import bulk customers
    function import() {

        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $excel_upload = TRUE;
            $upload_error = '';
            if ($this->input->post('post_data')) {
                if (isset($_FILES['userfile']['name']) && empty($_FILES['userfile']['name'])) {
                    $upload_error = '<p>You must upload excel file.</p>';
                    $excel_upload = FALSE;
                } elseif (isset($_FILES['userfile']['name']) && (get_file_extension($_FILES['userfile']['name']) != 'xlsx' && get_file_extension($_FILES['userfile']['name']) != 'xls')) {
                    $upload_error = '<p>File uploaded must be in xls or xlxs format.</p>';
                    $excel_upload = FALSE;
                }

                if ($excel_upload == TRUE) {
                    move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/temp/customers_import.xlsx');
                    // set file path
                    $file = './uploads/temp/customers_import.xlsx';
                    //load the excel library
                    $this->load->library('excel');
                    //read file from path
                    $objPHPExcel = PHPExcel_IOFactory::load($file);
                    //get only the Cell Collection
                    $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
                    //extract to a PHP readable array format
                    foreach ($cell_collection as $cell) {
                        $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
                        $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
                        $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
                        //header will/should be in row 1 only. of course this can be modified to suit your need.
                        if ($row == 1) {
                            $header[$row][$column] = $data_value;
                        } else {

                            $arr_data[$row][$column] = $data_value;
                        }
                    }
                    foreach ($arr_data as $row) {

                        if (trim($row['A']) <> '' && trim($row['B']) <> '' && trim($row['C']) <> '') {
                            $customer['name'] = strtoupper(trim($row['A']));
                            $customer['address'] = strtoupper(trim($row['B']));
                            $customer['phone'] = trim($row['C']);
                            $customer['user_id'] = $this->ion_auth->user()->row()->id;
                            $customer['id_domain']=$this->ion_auth->get_id_domain();
                            if (!empty($customer)) {
                                $customer['registered_on'] = time();
                                $this->_insert($customer);
                                $customer = array();
                            }
                        }
                    }
                    $this->load->helper('file');
                    delete_files('./uploads/temp');
                    $this->session->set_flashdata('message', 'Customer successfully imported');
                    redirect('customer');
                }
            }

            // display form
            // set the flash data error message if there is one
            $data['message'] = $upload_error ? $upload_error : $this->session->flashdata('message');
            $data['module'] = 'customer';
            $data['view_file'] = 'import_customer';
            $data['customer_active'] = 'active';
            $data['import_customer_active'] = 'active';
            $data['heading'] = 'Customer';
            $data['subheading'] = 'import bulk customers file';
            echo Modules::run('template/login_area', $data);
        }
    }

    function get_table() {
        $this->load->model('customer_model');
        $table = $this->customer_model->get_table();
        return $table;
    }

    function get($order_by) {
        $this->load->model('customer_model');
        $query = $this->customer_model->get($order_by);
        return $query;
    }

    function get_with_limit($limit, $offset, $order_by) {
        $this->load->model('customer_model');
        $query = $this->customer_model->get_with_limit($limit, $offset, $order_by);
        return $query;
    }

    function get_where($id) {
        $this->load->model('customer_model');
        $query = $this->customer_model->get_where($id);
        return $query;
    }

    function get_where_custom($col, $value) {
        $this->load->model('customer_model');
        $query = $this->customer_model->get_where_custom($col, $value);
        return $query;
    }

    function _insert($data) {
        $this->load->model('customer_model');
        $this->customer_model->_insert($data);
    }

    function _update($id, $data) {
        $this->load->model('customer_model');
        $this->customer_model->_update($id, $data);
    }

    function _delete($id) {
        $this->load->model('customer_model');
        $this->customer_model->_delete($id);
    }

    function count_where($column, $value) {
        $this->load->model('customer_model');
        $count = $this->customer_model->count_where($column, $value);
        return $count;
    }

    function get_max() {
        $this->load->model('customer_model');
        $max_id = $this->customer_model->get_max();
        return $max_id;
    }

    function _custom_query($mysql_query) {
        $this->load->model('customer_model');
        $query = $this->customer_model->_custom_query($mysql_query);
        return $query;
    }

}
