<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {

    protected $CI;

    public function __construct() {
        parent::__construct();
        $this->CI = & get_instance();
        $this->CI->load->database();
    }

    /**
     * validate_phone
     *
     * @access  public
     * @param   numeric  $phone
     * @return  bool
     */
    public function validate_phone($phone) {
        if ($phone != "") {
            $this->CI->form_validation->set_message('validate_phone', 'The %s field must contain a valid phone number ');
            return (!preg_match("/^[0-9]{4}-[0-9]{3}-[0-9]{3}$/", $phone)) ? FALSE : TRUE;
        }
    }

    /**
     * validate_date
     *
     * @access  public
     * @param   string  $date
     * @return  bool
     */
    function validate_date($date) {
        if ($date != "") {
            if (!preg_match("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/", $date)) {
                $this->CI->form_validation->set_message('validate_date', 'The %s entered accepted format is mm/dd/yyyy');
                return FALSE;
            } else {
                list($month, $day, $year) = explode("/", $date);
                if (!checkdate($month, $day, $year)) {
                    $this->CI->form_validation->set_message('validate_date', 'The %s entered is invalid ');
                    return FALSE;
                } else {
                    return TRUE;
                }
            }
        }
    }

    /**
     * edit_unique
     *
     * @access  public
     * @param  $value,$params
     * @return  bool
     */
    function edit_unique($value, $params) {
        $this->CI->form_validation->set_message('edit_unique', "The %s field must contain a unique value.");

        list($table, $field, $current_id) = explode(".", $params);

        $query = $this->CI->db->select()->from($table)->where($field, $value)->limit(1)->get();
        $field2 ='id_' .$table ;

        if ($query->row() && $query->row()->$field2 != $current_id) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    
    
    /**
     * is_exist
     *
     * @access  public
     * @param  $value,$params
     * @return  bool
     */
    function is_exist($value, $params) {
        $this->CI->form_validation->set_message('is_exist', "The %s value does not exist.");

        list($table, $field) = explode(".", $params);

        $query = $this->CI->db->select()->from($table)->where($field, $value)->limit(1)->get();

        if ($query->num_rows()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
