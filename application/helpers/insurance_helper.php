<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
if (!function_exists('status_dropdown')) {

    function status_types() {
        $status_types = array(
            '1' => 'Active',
            '0' => 'Inactive'
        );
        return $status_types;
    }

}

if (!function_exists('paid_status_by_id')) {

    function paid_status_by_id($id) {
        if ($id)
            return 'Paid';
        else
            return 'Not paid';
    }

}


if (!function_exists('confirmed_status_by_id')) {

    function confirmed_status_by_id($id) {
        if ($id)
            return 'Confirmed';
        else
            return 'Not confirmed';
    }

}


if (!function_exists('remark_by_id')) {

    function remark_by_id($id) {
        if ($id == 1)
            return 'Closed';
        else
            return 'Open';
    }

}



if (!function_exists('status_claim')) {

    function status_claim() {
        $claim_status = array(
            '0' => 'Claim Free',
            '1' => 'Claim Records'
        );
        return $claim_status;
    }

}

if (!function_exists('format_date')) {

    function format_date($datestring = '', $time) {
        $datestring = ($datestring == '') ? "%d/%m/%Y" : $datestring;
        return mdate($datestring, $time);
    }

}

if (!function_exists('format_phone')) {

    function format_phone($number) {
        $phone= ltrim(implode(array_filter(str_split($number, 1), "is_numeric")), '0');
        $phone='+255'.$phone;
        return $phone;

}
}


if (!function_exists('date_to_int_string')) {

    function date_to_int_string($date) {
        $arr = explode('/', $date);
        $date = implode('-', $arr);
        return strtotime($date);
    }

}

if (!function_exists('status_by_id')) {

    function status_by_id($id) {
        if ($id)
            return 'Active';
        else
            return 'Inactive';
    }

}


if (!function_exists('get_file_extension')) {

    function get_file_extension($filename) {
        $filename = strtolower($filename);
        $ext = explode(".", $filename);
        $n = count($ext) - 1;
        $ext = $ext[$n];
        return $ext;
    }

}


if (!function_exists('format_name')) {

    function format_name($name) {
        $name = trim($name);
        if (str_word_count($name) > 1)
            return ucwords($name);
        else
            return ucfirst(strtolower(trim($name)));
    }

}



if (!function_exists('claim_status_by_id')) {

    function claim_status_by_id($id) {
        if (!$id)
            return 'Claim Free';
        else
            return 'Claim Records';
    }

}



if (!function_exists('customer_name_by_id')) {

    function customer_name_by_id($id) {
        $query = Modules::run('customer/get_where', $id);
        if (!$query->num_rows()) {
            return '-';
        } else {
            $row = $query->row();
            return $row->name;
        }
    }

}

if (!function_exists('property_type_by_id')) {

    function property_type_by_id($id) {
        $row = Modules::run('property/get_where', $id)->row();
        return $row->type;
    }

}

if (!function_exists('property_number_by_id')) {

    function property_number_by_id($id) {
        $row = Modules::run('property/get_where', $id)->row();
        return $row->number;
    }

}


if (!function_exists('vehicle_class_by_id')) {

    function vehicle_class_by_id($id) {
        $row = Modules::run('vehicle_class/get_where', $id)->row();
        return $row->class;
    }

}

if (!function_exists('cover_note_by_id')) {

    function cover_note_by_id($id) {
        $row = Modules::run('insurance/get_where', $id)->row();
        return $row->cover_note;
    }

}


if (!function_exists('claim_type_by_id')) {

    function claim_type_by_id($id) {
        $row = Modules::run('claim_type/get_where', $id)->row();
        return $row->type;
    }

}


if (!function_exists('id_insurance_by_cover_note')) {

    function id_insurance_by_cover_note($cover_note) {
        $data['cover_note'] = $cover_note;
        $row = Modules::run('insurance/get_where_custom', $data)->row();
        return $row->id_insurance;
    }

}

if (!function_exists('cover_note_by_id_insurance')) {

    function cover_note_by_id_insurance($id_insurance) {
        $row = Modules::run('insurance/get_where', $id_insurance)->row();
        return $row->cover_note;
    }

}


if (!function_exists('premium_by_id')) {

    function premium_by_id($id) {
        $row = Modules::run('insurance/get_where', $id)->row();
        return $row->premium;
    }

}



if (!function_exists('customer_by_id_property')) {

    function customer_by_id_property($id_property) {

        $row = Modules::run('property/get_where', $id_property)->row();
        $id_customer = $row->id_customer;
        $name = customer_name_by_id($id_customer);
        return $name;
    }

}


if (!function_exists('customer_id_by_id_property')) {

    function customer_id_by_id_property($id_property) {

        $row = Modules::run('property/get_where', $id_property)->row();
        $id_customer = $row->id_customer;
        return $id_customer;
    }

}


if (!function_exists('customer_by_id_insurance')) {

    function customer_by_id_insurance($id_insurance) {

        $row = Modules::run('insurance/get_where', $id_insurance)->row();
        $id_property = $row->id_property;
        $name = customer_by_id_property($id_property);
        return $name;
    }

}


if (!function_exists('vehicle_class_list')) {

    function vehicle_class_list() {
        $table = Modules::run('vehicle_class/get_table');
        $sql = "select id_$table,class from $table order by class";
        $array = Modules::run('vehicle_class/_custom_query', $sql)->result_array();
        foreach ($array as $row) {
            $data[$row['id_' . $table]] = $row['class'];
        }
        return $data;
    }

}

if (!function_exists('insurer_list')) {

    function insurer_list() {
        $table = Modules::run('insurer/get_table');
        $sql = "select id_$table,name from $table order by name";
        $array = Modules::run('insurer/_custom_query', $sql)->result_array();
        foreach ($array as $row) {
            $data[$row['id_' . $table]] = $row['name'];
        }
        return $data;
    }

}

if (!function_exists('duration_list')) {

    function duration_list() {
        $CI = & get_instance();
        $array = $CI->db->get('duration')->result_array();
        foreach ($array as $row) {
            $data[$row['moth/year']] = $row['description'];
        }
        return $data;
    }

}


if (!function_exists('insurer_by_id_insurance')) {

    function insurer_by_id_insurance($id_insurance) {
        $row = Modules::run('insurance/get_where', $id_insurance)->row();
        $id_insurer = $row->id_insurer;
        $insurer = insurer_by_id($id_insurer);
        return $insurer;
    }

}

if (!function_exists('insurer_by_id')) {

    function insurer_by_id($id) {
        $insurer = Modules::run('insurer/get_where', $id)->row();
        return $insurer->name;
    }

}



if (!function_exists('cover_type_list')) {

    function cover_type_list() {
        $table = Modules::run('cover_type/get_table');
        $sql = "select id_$table,type from $table order by type";
        $array = Modules::run('cover_type/_custom_query', $sql)->result_array();
        foreach ($array as $row) {
            $data[$row['id_' . $table]] = $row['type'];
        }
        return $data;
    }

}



if (!function_exists('tax_list')) {

    function tax_list() {
        $table = Modules::run('tax/get_table');
        $sql = "select percentage,type from $table where status=1 LIMIT 1";
        $array = Modules::run('tax/_custom_query', $sql)->result_array();
        foreach ($array as $row) {
            $row['type'] = $row['percentage'] . ' %';
            $data[$row['percentage']] = $row['type'];
        }
        return $data;
    }

}


if (!function_exists('tax_type_list')) {

    function tax_type_list() {
        $tax_type_list = array(
            '1' => 'VAT',
            '2' => 'Commission'
        );
        return $tax_type_list;
    }

}


if (!function_exists('get_expire_date')) {

    function get_expire_date($time, $duration = 12) {
        return strtotime("+$duration month -1 day", $time);
    }

}

if (!function_exists('format_currency')) {

    function format_currency($number) {
        setlocale(LC_MONETARY, 'en_US');
        return money_format('%i', floatval($number));
    }

}


if (!function_exists('calculate_tax')) {

    function calculate_tax($premium, $rate) {
        return ($premium * $rate / 100);
    }

}

if (!function_exists('calculate_commission')) {

    function calculate_commission($premium, $rate) {
        return ($premium * $rate / 100);
    }

}

if (!function_exists('remove_last_word')) {

    function remove_last_word($string) {
        $words = explode(' ', $string);
        if (count($words) >= 2) {
            array_pop($words);
            array_pop($words);
        }
        $words = implode(' ', $words);
        return $words;
    }

}

if (!function_exists('return_quarter_month')) {

    function return_quarter_month($month) {
        if ($month == 3)
            $month = 'March';
        elseif ($month == 6)
            $month = 'June';
        elseif ($month == 9)
            $month = 'September';
        elseif ($month == 12)
            $month = 'December';

        return $month;
    }

}


if (!function_exists('navigation_link')) {

    function quick_navigation_menu($link) {
        $menu = '';
        $link_array = explode('_', $link);
        if (count($link_array) == 2)
            $menu = $link_array[0] . ' ' . $link_array[1];
        elseif (count($link_array) == 3)
            $menu = $link_array[0] . ' ' . $link_array[1] . ' ' . $link_array[2];
        else
            $menu = $link;

        return ucfirst($menu);
    }

}



if (!function_exists('ordinal')) {

        function ordinal($number) {

            if ($number % 100 > 10 && $number % 100 <
                    14):
                $suffix = "th";
            else:
                switch ($number % 10) {

                    case 0:
                        $suffix = "th";
                        break;

                    case 1:
                        $suffix = "st";
                        break;

                    case 2:
                        $suffix = "nd";
                        break;

                    case 3:
                        $suffix = "rd";
                        break;

                    default:
                        $suffix = "th";
                        break;
                }

            endif;

            return "${number}$suffix day";
        }

}
?>
