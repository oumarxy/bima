<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(array('ion_auth', 'form_validation'));
        $this->load->helper(array('url', 'language'));

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

        $this->lang->load('auth');
    }

    // redirect if needed, otherwise display the user list
    function index() {

        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } if (!$this->check_access()) {
            $this->show_error();
        } else {
            // set the flash data error message if there is one
            $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            //list the users
            $data['users'] = $this->ion_auth->users()->result();
            foreach ($data['users'] as $k => $user) {
                $data['users'][$k]->groups = $this->ion_auth->get_users_groups($user->id)->result();
            }

            $data['module'] = 'auth';
            $data['view_file'] = 'index';
            $data['title'] = 'Users';
            $data['users_active'] = 'active';
            $data['list_users_active'] = 'active';
            $data['heading'] = lang('index_heading');
            $data['subheading'] = lang('index_subheading');
            echo Modules::run('template/login_area', $data);
            //$this->_render_page('auth/index', $data);
        }
    }

    // log the user in
    function login() {
        $data['title'] = "Login";
        $data['module'] = 'auth';
        $data['view_file'] = 'login';

        //validate form input
        $this->form_validation->set_rules('identity', 'Identity', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == true) {
            // check to see if the user is logging in
            // check for "remember me"
            $remember = (bool) $this->input->post('remember');

            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                //if the login is successful
                //redirect them back to the home page
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect('site', 'refresh');
            } else {
                // if the login was un-successful
                // redirect them back to the login page
                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect('site/login', 'refresh'); // use redirects instead of loading views for compatibility with MY_Controller libraries
            }
        } else {
            // the user is not logging in so display the login page
            // set the flash data error message if there is one
            $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $data['identity'] = array('name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('identity'),
            );
            $data['password'] = array('name' => 'password',
                'id' => 'password',
                'type' => 'password',
            );

            echo Modules::run('template/public_area', $data);
        }
    }

    // log the user out
    function logout() {
        $data['title'] = "Logout";

        // log the user out
        $logout = $this->ion_auth->logout();

        // redirect them to the login page
        $this->session->set_flashdata('message', $this->ion_auth->messages());
        redirect('site/login', 'refresh');
    }

    // change password
    function change_password() {
        $this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
        $this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
        $this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        $user = $this->ion_auth->user()->row();

        if ($this->form_validation->run() == false) {
            // display the form
            // set the flash data error message if there is one
            $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
            $data['old_password'] = array(
                'name' => 'old',
                'id' => 'old',
                'type' => 'password',
            );
            $data['new_password'] = array(
                'name' => 'new',
                'id' => 'new',
                'type' => 'password',
                'pattern' => '^.{' . $data['min_password_length'] . '}.*$',
            );
            $data['new_password_confirm'] = array(
                'name' => 'new_confirm',
                'id' => 'new_confirm',
                'type' => 'password',
                'pattern' => '^.{' . $data['min_password_length'] . '}.*$',
            );
            $data['user_id'] = array(
                'name' => 'user_id',
                'id' => 'user_id',
                'type' => 'hidden',
                'value' => $user->id,
            );

            // render
            $this->_render_page('auth/change_password', $data);
        } else {
            $identity = $this->session->userdata('identity');

            $change = $this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));

            if ($change) {
                //if the password was successfully changed
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $this->logout();
            } else {
                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect('auth/change_password', 'refresh');
            }
        }
    }

    // forgot password
    function forgot_password() {
        $data['title'] = "Forgot Password";
        $data['module'] = 'auth';
        $data['view_file'] = 'forgot_password';
        // setting validation rules by checking wheather identity is username or email
        if ($this->config->item('identity', 'ion_auth') != 'email') {
            $this->form_validation->set_rules('identity', $this->lang->line('forgot_password_identity_label'), 'required');
        } else {
            $this->form_validation->set_rules('email', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email');
        }


        if ($this->form_validation->run() == false) {
            // setup the input
            $data['email'] = array('name' => 'email',
                'id' => 'email',
            );

            if ($this->config->item('identity', 'ion_auth') != 'email') {
                $data['identity_label'] = $this->lang->line('forgot_password_identity_label');
            } else {
                $data['identity_label'] = $this->lang->line('forgot_password_email_identity_label');
            }

            // set any errors and display the form
            $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            //$this->_render_page('auth/forgot_password', $data);
            echo Modules::run('template/public_area', $data);
        } else {
            $identity_column = $this->config->item('identity', 'ion_auth');
            $identity = $this->ion_auth->where($identity_column, $this->input->post('email'))->users()->row();

            if (empty($identity)) {

                if ($this->config->item('identity', 'ion_auth') != 'email') {
                    $this->ion_auth->set_error('forgot_password_identity_not_found');
                } else {
                    $this->ion_auth->set_error('forgot_password_email_not_found');
                }

                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect("auth/forgot_password", 'refresh');
            }

            // run the forgotten password method to email an activation code to the user
            $forgotten = $this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')});

            if ($forgotten) {
                // if there were no errors
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("auth/login", 'refresh'); //we should display a confirmation page here instead of the login page
            } else {
                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect("auth/forgot_password", 'refresh');
            }
        }
    }

    // reset password - final step for forgotten password
    public function reset_password($code = NULL) {
        if (!$code) {
            show_404();
        }

        $user = $this->ion_auth->forgotten_password_check($code);

        if ($user) {
            // if the code is valid then display the password reset form

            $this->form_validation->set_rules('new', $this->lang->line('reset_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', $this->lang->line('reset_password_validation_new_password_confirm_label'), 'required');

            if ($this->form_validation->run() == false) {
                // display the form
                // set the flash data error message if there is one
                $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

                $data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $data['new_password'] = array(
                    'name' => 'new',
                    'id' => 'new',
                    'type' => 'password',
                    'pattern' => '^.{' . $data['min_password_length'] . '}.*$',
                );
                $data['new_password_confirm'] = array(
                    'name' => 'new_confirm',
                    'id' => 'new_confirm',
                    'type' => 'password',
                    'pattern' => '^.{' . $data['min_password_length'] . '}.*$',
                );
                $data['user_id'] = array(
                    'name' => 'user_id',
                    'id' => 'user_id',
                    'type' => 'hidden',
                    'value' => $user->id,
                );
                $data['csrf'] = $this->_get_csrf_nonce();
                $data['code'] = $code;

                // render
                $this->_render_page('auth/reset_password', $data);
            } else {
                // do we have a valid request?
                if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->post('user_id')) {

                    // something fishy might be up
                    $this->ion_auth->clear_forgotten_password_code($code);

                    show_error($this->lang->line('error_csrf'));
                } else {
                    // finally change the password
                    $identity = $user->{$this->config->item('identity', 'ion_auth')};

                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));

                    if ($change) {
                        // if the password was successfully changed
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        redirect("auth/login", 'refresh');
                    } else {
                        $this->session->set_flashdata('message', $this->ion_auth->errors());
                        redirect('auth/reset_password/' . $code, 'refresh');
                    }
                }
            }
        } else {
            // if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('message', $this->ion_auth->errors());
            redirect("auth/forgot_password", 'refresh');
        }
    }

    // activate the user
    function activate($id, $code = false) {
        if ($code !== false) {
            $activation = $this->ion_auth->activate($id, $code);
        } else if ($this->ion_auth->is_admin()) {
            $activation = $this->ion_auth->activate($id);
        }

        if ($activation) {
            // redirect them to the auth page
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect("user", 'refresh');
        } else {
            // redirect them to the forgot password page
            $this->session->set_flashdata('message', $this->ion_auth->errors());
            redirect("auth/forgot_password", 'refresh');
        }
    }

    // deactivate the user
    function deactivate($id = NULL) {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            // redirect them to the home page because they must be an administrator to view this
            return show_error('You must be an administrator to view this page.');
        }

        $id = (int) $id;

        $this->load->library('form_validation');
        $this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required');
        $this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric');

        if ($this->form_validation->run() == FALSE) {
            if (!$this->ion_auth->user($id)->num_rows()) {
                redirect('user');
            }
            // insert csrf check
            $data['csrf'] = $this->_get_csrf_nonce();
            $data['user'] = $this->ion_auth->user($id)->row();

            $data['module'] = 'auth';
            $data['title'] = 'Deactivate User';
            $data['view_file'] = 'deactivate_user';
            $data['users_active'] = 'active';
            $data['heading'] = 'Deactivate User';
            $data['subheading'] = 'block user from accessing system';
            echo Modules::run('template/login_area', $data);
            //$this->_render_page('user/deactivate_user', $data);
        } else {
            // do we really want to deactivate?
            if ($this->input->post('confirm') == 'yes') {
                // do we have a valid request?
                if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id')) {
                    show_error($this->lang->line('error_csrf'));
                }

                // do we have the right userlevel?
                if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
                    $this->ion_auth->deactivate($id);
                }
            }

            // redirect them back to the auth page
            redirect('user', 'refresh');
        }
    }

    // create a new user
    function create_user() {
        $data['title'] = "Create User";

        if (!$this->ion_auth->logged_in() || !$this->check_access()) {
            redirect('auth', 'refresh');
        }

        $tables = $this->config->item('tables', 'ion_auth');

        // validate form input
        $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required');
        $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required');
        $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
        $this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'required');
        //$this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'required');
        $this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');

        if ($this->form_validation->run() == true) {
            $username = strtolower($this->input->post('first_name')) . ' ' . strtolower($this->input->post('last_name'));
            $email = strtolower($this->input->post('email'));
            $password = $this->input->post('password');

            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'company' => $this->input->post('company'),
                'phone' => $this->input->post('phone'),
                'id_domain' => $this->ion_auth->get_id_domain()
            );
        }
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data)) {
            $id_user = $this->db->insert_id();
            move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/user_image/' . $id_user . '.jpg');
            // check to see if we are creating the user
            // redirect them back to the admin page
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect("user", 'refresh');
        } else {
            // display the create user form
            // set the flash data error message if there is one
            $data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

            $data['first_name'] = array(
                'name' => 'first_name',
                'id' => 'first_name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('first_name'),
            );
            $data['last_name'] = array(
                'name' => 'last_name',
                'id' => 'last_name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('last_name'),
            );
            $data['email'] = array(
                'name' => 'email',
                'id' => 'email',
                'type' => 'text',
                'value' => $this->form_validation->set_value('email'),
            );
            $data['company'] = array(
                'name' => 'company',
                'id' => 'company',
                'type' => 'text',
                'value' => $this->form_validation->set_value('company'),
            );
            $data['phone'] = array(
                'name' => 'phone',
                'id' => 'phone',
                'type' => 'text',
                'value' => $this->form_validation->set_value('phone'),
            );
            $data['password'] = array(
                'name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'value' => $this->form_validation->set_value('password'),
            );
            $data['password_confirm'] = array(
                'name' => 'password_confirm',
                'id' => 'password_confirm',
                'type' => 'password',
                'value' => $this->form_validation->set_value('password_confirm'),
            );

            $data['module'] = 'auth';
            $data['users_active'] = 'active';
            $data['create_user_active'] = 'active';
            $data['view_file'] = 'create_user';
            $data['heading'] = 'Create User';
            $data['subheading'] = 'Fill form below';
            echo Modules::run('template/login_area', $data);
            //$this->_render_page('auth/create_user', $data);
        }
    }

    // edit a user
    function edit_user($id) {
        $data['title'] = "User Profile";
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } if (!$this->check_access()) {
            $this->show_error();
        }
        
        
           if (!$this->ion_auth->user($id)->num_rows()) {
            redirect('user');
        }
        $user = $this->ion_auth->user($id)->row();
        $groups = $this->ion_auth->groups()->result_array();
        $currentGroups = $this->ion_auth->get_users_groups($id)->result();

        // validate form input
        $this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'required');
        $this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'required');
        $this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'required');
        //$this->form_validation->set_rules('company', $this->lang->line('edit_user_validation_company_label'), 'required');

        if (isset($_POST) && !empty($_POST)) {
            // do we have a valid request?
            if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id')) {
                show_error($this->lang->line('error_csrf'));
            }

            // update the password if it was posted
            if ($this->input->post('password')) {
                $this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
                $this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');
            }

            if ($this->form_validation->run() === TRUE) {
                $data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'phone' => $this->input->post('phone'),
                );

                // update the password if it was posted
                if ($this->input->post('password')) {
                    $data['password'] = $this->input->post('password');
                }



                // Only allow updating groups if user is admin
                if ($this->ion_auth->is_admin()) {
                    // update the domain belong to users
                    $id_domain = $this->input->post('id_domain');
                    if (isset($id_domain) && $id_domain <> '') {
                        $data['id_domain'] = $this->input->post('id_domain');
                    }

                    //Update the groups user belongs to
                    $groupData = $this->input->post('groups');


                    if (isset($groupData) && !empty($groupData)) {

                        $this->ion_auth->remove_from_group('', $id);

                        foreach ($groupData as $grp) {
                            $this->ion_auth->add_to_group($grp, $id);
                        }
                    }
                }

                // check to see if we are updating the user
                if ($this->ion_auth->update($user->id, $data)) {
                    move_uploaded_file($_FILES['userfile']['tmp_name'], 'uploads/user_image/' . $id . '.jpg');
                    // redirect them back to the admin page if admin, or to the base url if non admin
                    $this->session->set_flashdata('message', $this->ion_auth->messages());
                    if ($this->ion_auth->is_admin()) {
                        redirect('user', 'refresh');
                    } else {
                        redirect('/', 'refresh');
                    }
                } else {
                    // redirect them back to the admin page if admin, or to the base url if non admin
                    $this->session->set_flashdata('message', $this->ion_auth->errors());
                    if ($this->ion_auth->is_admin()) {
                        redirect('user', 'refresh');
                    } else {
                        redirect('/', 'refresh');
                    }
                }
            }
        }

        // display the edit user form
        $data['csrf'] = $this->_get_csrf_nonce();

        // set the flash data error message if there is one
        $data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

        // pass the user to the view
        $data['user'] = $user;
        $data['groups'] = $groups;
        $data['currentGroups'] = $currentGroups;

        $data['first_name'] = array(
            'name' => 'first_name',
            'id' => 'first_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('first_name', $user->first_name),
        );
        $data['last_name'] = array(
            'name' => 'last_name',
            'id' => 'last_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('last_name', $user->last_name),
        );
        $data['domain'] = array(
            'name' => 'domain',
            'id' => 'domain',
            'type' => 'text',
            'value' => $this->form_validation->set_value('domain', $user->id_domain),
        );
        $data['phone'] = array(
            'name' => 'phone',
            'id' => 'phone',
            'type' => 'text',
            'value' => $this->form_validation->set_value('phone', $user->phone),
        );
        $data['password'] = array(
            'name' => 'password',
            'id' => 'password',
            'type' => 'password'
        );
        $data['password_confirm'] = array(
            'name' => 'password_confirm',
            'id' => 'password_confirm',
            'type' => 'password'
        );
        $data['module'] = 'auth';
        $data['view_file'] = 'edit_user';
        $data['users_active'] = 'active';
        $data['user_profile_active'] = 'active';
        $data['heading'] = 'User Profile';
        $data['subheading'] = 'Edit User Informations';
        echo Modules::run('template/login_area', $data);

        //$this->_render_page('auth/edit_user', $data);
    }

    // create a new group
    function create_group() {
        $data['title'] = $this->lang->line('create_group_title');

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('user', 'refresh');
        }

        // validate form input
        $this->form_validation->set_rules('group_name', $this->lang->line('create_group_validation_name_label'), 'required|alpha_dash');

        if ($this->form_validation->run() == TRUE) {
            $new_group_id = $this->ion_auth->create_group($this->input->post('group_name'), $this->input->post('description'));
            if ($new_group_id) {
                // check to see if we are creating the group
                // redirect them back to the admin page
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("user", 'refresh');
            }
        } else {
            // display the create group form
            // set the flash data error message if there is one
            $data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

            $data['group_name'] = array(
                'name' => 'group_name',
                'id' => 'group_name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('group_name'),
            );
            $data['description'] = array(
                'name' => 'description',
                'id' => 'description',
                'type' => 'text',
                'value' => $this->form_validation->set_value('description'),
            );

            $data['module'] = 'auth';
            $data['title'] = 'Create Group';
            $data['view_file'] = 'create_group';
            $data['users_active'] = 'active';
            $data['heading'] = 'Create Group';
            $data['subheading'] = 'fill group details';
            echo Modules::run('template/login_area', $data);

            //$this->_render_page('auth/create_group', $data);
        }
    }

    // edit a group
    function edit_group($id) {
        // bail if no group id given
        if (!$id || empty($id)) {
            redirect('user', 'refresh');
        }

        $data['title'] = $this->lang->line('edit_group_title');

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('user', 'refresh');
        }

        $group = $this->ion_auth->group($id)->row();

        // validate form input
        $this->form_validation->set_rules('group_name', $this->lang->line('edit_group_validation_name_label'), 'required|alpha_dash');

        if (isset($_POST) && !empty($_POST)) {
            if ($this->form_validation->run() === TRUE) {
                $group_update = $this->ion_auth->update_group($id, $_POST['group_name'], $_POST['group_description']);

                if ($group_update) {
                    $this->session->set_flashdata('message', $this->lang->line('edit_group_saved'));
                } else {
                    $this->session->set_flashdata('message', $this->ion_auth->errors());
                }
                redirect("user", 'refresh');
            }
        }

        // set the flash data error message if there is one
        $data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

        // pass the user to the view
        $data['group'] = $group;

        $readonly = $this->config->item('admin_group', 'ion_auth') === $group->name ? 'readonly' : '';

        $data['group_name'] = array(
            'name' => 'group_name',
            'id' => 'group_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('group_name', $group->name),
            $readonly => $readonly,
        );
        $data['group_description'] = array(
            'name' => 'group_description',
            'id' => 'group_description',
            'type' => 'text',
            'value' => $this->form_validation->set_value('group_description', $group->description),
        );
        $data['module'] = 'auth';
        $data['title'] = 'Edit Group';
        $data['view_file'] = 'edit_group';
        $data['users_active'] = 'active';
        $data['heading'] = 'Edit Group';
        $data['subheading'] = 'fill group details';
        echo Modules::run('template/login_area', $data);
        //$this->_render_page('auth/edit_group', $data);
    }

    function _get_csrf_nonce() {
        $this->load->helper('string');
        $key = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return array($key => $value);
    }

    function _valid_csrf_nonce() {
        if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
                $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function _render_page($view, $data = null, $returnhtml = false) {//I think this makes more sense
        $this->viewdata = (empty($data)) ? $data : $data;

        $view_html = $this->load->view($view, $this->viewdata, $returnhtml);

        if ($returnhtml)
            return $view_html; //This will return html on 3rd argument being true
    }

}
