<?php

if (!defined('BASEPATH'))  exit('No direct script access allowed');

class Maintenance extends MX_Controller {

    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->library(array('auth/ion_auth'));
    }

// db backup
    function db_backup() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {

            // Load the DB utility class
            $this->load->dbutil();
            $prefs = array(
                'format' => 'zip',
                'filename' => 'my_db_backup.sql',
                'foreign_key_checks' => TRUE,
                'newline' => "\n"
            );


            // Backup your entire database and assign it to a variable
            $backup = & $this->dbutil->backup($prefs);

            $db_name = 'backup-on-' . date("Y-m-d-H-i-s") . '.zip';
            $save = './uploads/backups/' . $db_name;
            // Load the file helper and write the file to your server
            $this->load->helper('file');
            write_file($save, $backup);

            // Load the download helper and send the file to your desktop
            $this->load->helper('download');
            force_download($db_name, $backup);
        }
    }

}
