<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Error extends MX_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function error_403() {
        $this->output->set_status_header('404');
        $data['module'] = 'error';
        $data['view_file'] = '403';
        $data['heading'] = '404 Error Page ';
        $data['subheading'] = '';
        echo Modules::run('template/login_area', $data);
    }
    
     public function error_404() {
        $this->output->set_status_header('404');
        $data['module'] = 'error';
        $data['view_file'] = '404';
        $data['heading'] = '404 Error Page ';
        $data['subheading'] = '';
        echo Modules::run('template/login_area', $data);
    }

}

/* End of file site.php */
/* Location: ./application/modules/my404/controllers/my404.php */
