<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Report extends MX_Controller {

    private static $domain_name;

    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->library(array('auth/ion_auth', 'form_validation'));
        $this->load->helper(array('url', 'language'));
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->set_domain($this->ion_auth->get_id_domain());
        define('DOMAIN_NAME', $this->get_domain());
    }

// claim_preview function
    function claim_preview() {

        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $load_report = FALSE;

            $this->form_validation->set_rules('date_range', 'Reported', 'trim|xss_clean');
            $this->form_validation->set_rules('damage_date', 'Damaged', 'trim|xss_clean');
            $this->form_validation->set_rules('type', 'Type', 'trim|xss_clean');
            $this->form_validation->set_rules('remark', 'Remark', 'trim|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $load_report = TRUE;
                $where_clause = '';
                $title = '';
                $title_2 = '';
                $from_date = '';
                $to_date = '';
                $damage_from_date = '';
                $damage_to_date = '';
                $date_range = $this->input->post('date_range');
                if ($date_range <> '') {
                    $date_range_array = explode('-', $date_range);
                    if (count($date_range_array) > 0) {
                        $from_date = $date_range_array[0];
                        $to_date = $date_range_array[1];
                        $where_clause.=" (reported_on between  " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
                    }
                }

                $damage_date = $this->input->post('damage_date');
                if ($damage_date <> '') {
                    $damage_date_array = explode('-', $damage_date);
                    if (count($damage_date_array) > 0) {
                        $damage_from_date = $damage_date_array[0];
                        $damage_to_date = $damage_date_array[1];
                        $where_clause.=" (lost_on between  " . date_to_int_string($damage_from_date) . " and " . date_to_int_string($damage_to_date) . ") and ";
                    }
                }

                $claim_type = $this->input->post('claim_type');
                $remark = $this->input->post('remark');
                $data['from_date'] = $from_date;
                $data['to_date'] = $to_date;

                $data['damage_from_date'] = $damage_from_date;
                $data['damage_to_date'] = $damage_to_date;
                $data['claim_type'] = $claim_type;
                $data['remark'] = $remark;

                if ($from_date != '') {

                    $title.= 'Reported FROM ' . $from_date ;
                }
                if ($to_date != '') {
                    $title.= ' TO ' . $to_date . ', ';
                }

                if ($damage_from_date != '') {

                    $title.= 'Damaged FROM ' . $damage_from_date ;
                }
                if ($damage_to_date != '') {
                    $title.= ' TO ' . $damage_to_date . ', ';
                }


                if ($claim_type != '') {
                    $title_2.=" " . strtoupper(claim_type_by_id($claim_type)) . ",";
                }

                if ($remark == 2) {
                    $title_2.=" OPEN CLAIMS,";
                }

                if ($remark == 1) {
                    $title_2.=" CLOSED CLAIMS,";
                }

                if ($remark == '') {
                    $title_2.=" CLOSED AND OPEN CLAIMS,";
                }

                if ($claim_type <> '') {
                    $where_clause.=" id_claim_type = $claim_type and ";
                }

                if ($remark <> '')
                    $where_clause.=" remark = $remark and ";

                $where_clause.=" id_domain=" . $this->ion_auth->get_id_domain() . " and ";

                $where_clause = ($where_clause != '') ? remove_last_word($where_clause) : $where_clause;
                $sql = "select * from " . Modules::run('claim/get_table');
                $sql.=($where_clause != '') ? " where $where_clause " : "";
                $sql . " order by reported_on ";

                $data['claims'] = Modules::run('claim/_custom_query', $sql)->result();
                $data['module'] = 'report';
                $data['view_file'] = 'claim/claim_preview';
                $data['report_active'] = 'active';
                $data['claim_report_active'] = 'active';
                $data['heading'] = 'Claim Report ';
                $data['subheading'] = $title . $title_2;
                echo Modules::run('template/login_area', $data);
            }

            if ($load_report == FALSE) {
             // display registration form
                $data['date_range'] = array(
                    'type' => 'text',
                    'name' => 'date_range',
                    'id' => 'reservation',
                    //'class' => 'form-control pull-right',
                    'size' => '25',
                    'value' => $this->form_validation->set_value('date_range')
                );

                $data['damage_date'] = array(
                    'type' => 'text',
                    'name' => 'damage_date',
                    'id' => 'reservation1',
                    //'class' => 'form-control pull-right',
                    'size' => '25',
                    'value' => $this->form_validation->set_value('damage_date')
                );

                $data['type'] = $this->form_validation->set_value('type');
                $data['remark'] = $this->form_validation->set_value('remark');

                $data['claim_type_list'] = Modules::run('claim_type/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();

                $data['module'] = 'report';
                $data['view_file'] = 'claim/option_form';
                $data['report_active'] = 'active';
                $data['claim_report_active'] = 'active';
                $data['heading'] = 'Report';
                $data['subheading'] = 'claim report';
                echo Modules::run('template/login_area', $data);
            }
        }
    }

    //print claim pdf
    function print_claim_pdf() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        define('SMARTWEB_PAGE', 'L');
        $this->load->library('pdf');

        // set document information
        $this->pdf->SetSubject('');
        $this->pdf->SetKeywords('');

        // set font
        $this->pdf->SetFont('times', '', 8.5);

        // add a page
        $this->pdf->AddPage();

        // footer margin
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE);

        $title = '';
        $title_2 = '';
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $claim_type = $this->input->post('claim_type');
        $remark = $this->input->post('remark');

        $where_clause = '';

        if ($from_date <> '' && $to_date <> '') {
            $title.= 'FROM ' . $from_date;
            $title.= ' TO ' . $to_date;
            $where_clause.=" (reported_on between  " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
        }

        if ($claim_type <> '') {
            $title_2.=" " . strtoupper(claim_type_by_id($claim_type)) . ",";
            $where_clause.=" id_claim_type = $claim_type and ";
        }

        $where_clause.=" id_domain=" . $this->ion_auth->get_id_domain() . " and ";

        if ($remark <> '') {
            if ($remark == 2) {
                $title_2.=" OPEN CLAIMS,";
            }

            if ($remark == 1) {
                $title_2.=" CLOSED CLAIMS,";
            }
            $where_clause.=" remark = $remark and ";
        } else {
            $title_2.=" CLOSED AND OPEN CLAIMS,";
        }

        $where_clause = ($where_clause != '') ? remove_last_word($where_clause) : $where_clause;
        $sql = "select * from " . Modules::run('claim/get_table');
        $sql.=($where_clause != '') ? " where $where_clause " : "";
        $sql . " order by reported_on ";

        $claims = Modules::run('claim/_custom_query', $sql)->result();

//////////////////////////////////////////////////////////////////////////
        $html = '<link rel="stylesheet" type="text/css" href="' . base_url() . 'media/css/style.css">';

        $html.='<style type="text/css">

.claim_table{
border-top:1px solid #4B4B4B;
border-left:1px solid #4B4B4B;
border-bottom:1px solid #4B4B4B;
}

.claim_table tr td{
border-top:1px solid #4B4B4B;
border-right:1px solid #4B4B4B;
padding:3px 0px 3px 2px;
}

.claim_table tr.header_report td{
background-color:lightblue;
padding:3px 0px 3px 2px;
border-top:0px solid #4B4B4B;
}
</style>

<h2 style="padding:0px;margin:0px;"> CLAIM REPORT ' . strtoupper($title) . '</h2>
<h2 style="padding:0px;margin:0px;">' . strtoupper(trim($title_2, '')) . '</h2>

<div style="margin:20px; width:900px;">
<div>
<table class="claim_table" cellpadding="0" cellspacing="0">
<tr class="header_report">
<td width="80"> No </td>
<td width="200"> Reported Date </td>
<td width="200"> Lost Date </td>
<td width="400"> Particulars </td>
<td width="250"> Contacts </td>
<td width="200"> Property </td>
<td width="200">Nature </td>
<td width="200"> Insurer</td>
<td width="200"> Number</td>
<td width="300"> Amount </td>
<td width="300"> Settlement</td><td width="400" align="center">Remarks</td>
</tr>';
        $i = 1;

        $total_claim_amount = 0;
        $total_settlement_amount = 0;
        foreach ($claims as $claim) {
            $insurance = Modules::run('insurance/get_where_custom', array('id_insurance' => $claim->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $insurer = Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();

            $html.='<tr>
<td align="center">' . $i++ . '</td>
<td>' . format_date('', $claim->reported_on) . '</td>
<td>' . format_date('', $claim->lost_on) . '</td>
<td> ' . $customer->name . ' </td>
<td> ' . $customer->phone . ' </td>
<td> ' . $property->number . ' </td>
<td> ' . Modules::run('claim_type/get_where_custom', array('id_claim_type' => $claim->id_claim_type, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->type . '</td>
<td> ' . $insurer->name . '</td>
<td> ' . $claim->claim_number . '</td>
<td align="center"> ' . number_format($claim->claim_amount) . ' </td>
<td align="center"> ' . number_format($claim->settlement_amount) . ' </td>';
            $remarks = remark_by_id($claim->remark) . ',';
            $remarks.=($claim->paid) ? ' Paid ' : ' Not paid ';
            $html.='<td align="center"> ' . $remarks . ' </td>';
            $html.='</tr>';
            $total_claim_amount+=$claim->claim_amount;
            $total_settlement_amount+=$claim->settlement_amount;
        }
        $html.='<tr>
<td colspan="9" align="right" style="background-color:#C9C9C9;">Grand Total:</td>
<td align="center" style="background-color:#C9C9C9;">' . number_format($total_claim_amount) . '</td>
<td align="center" style="background-color:#C9C9C9;">' . number_format($total_settlement_amount) . '</td>
<td align="center" style="background-color:#C9C9C9;">&nbsp;</td>
</tr>
</table>
</div>
</div>';
/////////////////////////////////////////////////////////////////////
// print a line using Cell()
        $this->pdf->writeHTML($html);

        $this->pdf->Output('claim_report.pdf', 'I');
    }

// print claim excel
    function print_claim_excel() {
        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $this->load->library("excel");
//$papersize = 'PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4';
// set properties
        $this->excel->getProperties()->setCreator("Lackson David");
        $this->excel->getProperties()->setLastModifiedBy("Lackson David");
        $this->excel->getProperties()->setTitle("ClaimReport");
        $this->excel->getProperties()->setSubject("");

        $title = '';
        $title_2 = '';
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $claim_type = $this->input->post('claim_type');
        $remark = $this->input->post('remark');

        $where_clause = '';

        if ($from_date <> '' && $to_date <> '') {
            $title.= 'FROM ' . $from_date;
            $title.= ' TO ' . $to_date;
            $where_clause.=" (reported_on between  " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
        }

        if ($claim_type <> '') {
            $title_2.=" " . strtoupper(claim_type_by_id($claim_type)) . ",";
            $where_clause.=" id_claim_type = $claim_type and ";
        }

        if ($remark <> '') {
            if ($remark == 2) {
                $title_2.=" OPEN CLAIMS,";
            }

            if ($remark == 1) {
                $title_2.=" CLOSED CLAIMS,";
                $where_clause.=" remark = $remark and ";
            }
        } else {
            $title_2.=" CLOSED AND OPEN CLAIMS,";
        }

        $where_clause.=" id_domain=" . $this->ion_auth->get_id_domain() . " and ";

        $where_clause = ($where_clause != '') ? remove_last_word($where_clause) : $where_clause;
        $sql = "select * from " . Modules::run('claim/get_table');
        $sql.=($where_clause != '') ? " where $where_clause " : "";
        $sql . " order by reported_on ";
        $claims = Modules::run('claim/_custom_query', $sql)->result();

        $style_title = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'size' => 12
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );

        $this->excel->setActiveSheetIndex(0);

//name the worksheet
        $this->excel->getActiveSheet()->setTitle('claim report');

        $this->excel->getActiveSheet()->setCellValue('C2', $this->get_domain());
        $this->excel->getActiveSheet()->getStyle('C2')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('C2:K2');
        $this->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);

        $this->excel->getActiveSheet()->setCellValue('C3', 'CLAIM REPORT' . strtoupper($title));
        $this->excel->getActiveSheet()->getStyle('C3')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('C3:K3');
        $this->excel->getActiveSheet()->getRowDimension(3)->setRowHeight(20);

        $this->excel->getActiveSheet()->setCellValue('C4', strtoupper($title_2));
        $this->excel->getActiveSheet()->getStyle('C4')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('C4:K4');
        $this->excel->getActiveSheet()->getRowDimension(4)->setRowHeight(20);


// style used in formating border of the cell
        $default_border = array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '1006A3'));

        $set_borders = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'font' => array(
                'name' => 'Arial',
                'italic' => false,
                'size' => 10,)
        );

        $style_header = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'E1E0F7'),
            ),
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'size' => 11,));


        $style_shade = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'DFD7CF'),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
        );

        $number = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,
            ),
            'font' => array(
                'name' => 'Arial',
                'italic' => false,
                'size' => 10,),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
        );

        $sheet = $this->excel->getActiveSheet();
        $col = "A";
        $rows = 7;
        $sheet->setCellValue($col . $rows, 'No');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;
        $sheet->setCellValue($col . $rows, 'Reported Date');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(12);
        $col++;
        $sheet->setCellValue($col . $rows, 'Lost Date');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(12);
        $col++;
        $sheet->setCellValue($col . $rows, 'Particular');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(25);
        $col++;

        $sheet->setCellValue($col . $rows, 'Contacts');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(12);
        $col++;

        $sheet->setCellValue($col . $rows, 'Property');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(12);
        $col++;

        $sheet->setCellValue($col . $rows, 'Nature');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;
        $sheet->setCellValue($col . $rows, 'Insurer');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;

        $sheet->setCellValue($col . $rows, 'Number');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(12);
        $col++;

        $sheet->setCellValue($col . $rows, 'Amont');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(12);
        $col++;
        $sheet->setCellValue($col . $rows, 'Settlement');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(12);
        $col++;
        $sheet->setCellValue($col . $rows, 'Remark');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;

        $rows++;
        $i = 1;

        $total_claim_amount = 0;
        $total_settlement_amount = 0;

        foreach ($claims as $claim) {
            $insurance = Modules::run('insurance/get_where_custom', array('id_insurance' => $claim->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $insurer = Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();


            $col = "A";
            $total_claim_amount+=$claim->claim_amount;
            $total_settlement_amount+=$claim->settlement_amount;


            $sheet->setCellValue($col . $rows, $i++);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, format_date('', $claim->reported_on));
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, format_date('', $claim->lost_on));
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $customer->name);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $customer->phone);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $property->number);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, Modules::run('claim_type/get_where_custom', array('id_claim_type' => $claim->id_claim_type, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->type);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;
            $sheet->setCellValue($col . $rows, $insurer->name);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $claim->claim_number);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, ($claim->claim_amount > 0) ? $claim->claim_amount : '-');
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, ($claim->settlement_amount > 0) ? $claim->settlement_amount : '-');
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;
            $remarks = remark_by_id($claim->remark) . ', ';
            $remarks.=($claim->paid) ? ' Paid ' : ' Not paid ';
            $sheet->setCellValue($col . $rows, $remarks);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);

            $col++;

            $rows++;
        }

        $col = "A";
        $sheet->setCellValue($col . $rows, 'Grand Total:');
        $col1 = $col;
        $col++;
        $col++;
        $col++;
        $col++;
        $col++;
        $col++;
        $col++;
        $col++;
        $sheet->mergeCells($col1 . $rows . ':' . $col . $rows);
        $sheet->getStyle($col1 . $rows)->applyFromArray($style_shade);
        $col++;
        $sheet->setCellValue($col . $rows, number_format($total_claim_amount));
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $col++;

        $sheet->setCellValue($col . $rows, number_format($total_settlement_amount));
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $col++;

        $sheet->setCellValue($col . $rows, '');
        $col1 = $col;

        $col++;

        $sheet->getStyle($col1 . $rows)->applyFromArray($style_shade);

        $filename = 'claim_report.xls'; //save our workbook as this file name
        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
//if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
//force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
    }

// insurance_preview function
    function insurance_preview() {

        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $load_report = FALSE;

            $this->form_validation->set_rules('insurer', 'Insurer', 'trim|xss_clean');
            $this->form_validation->set_rules('expired_status', 'Expired', 'trim|xss_clean');
            $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
            $this->form_validation->set_rules('status', 'Status', 'trim|xss_clean');
            $this->form_validation->set_rules('remark', 'Remark', 'trim|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $load_report = TRUE;
                $where_clause = '';
                $title = '';
                $title_2 = '';
                $from_date = '';
                $to_date = '';
                $date_range = $this->input->post('date_range');
                if ($date_range <> '') {
                    $date_range_array = explode('-', $date_range);
                    if (count($date_range_array) > 0) {
                        $from_date = $date_range_array[0];
                        $to_date = $date_range_array[1];
                    }
                }
                $id_insurer = $this->input->post('insurer');
                $expired_status = $this->input->post('expired_status');
                if ($expired_status <> '') {
                    $expired_status_array = explode('-', $expired_status);
                    if (count($expired_status_array) > 0) {
                        $expired_from_date = $expired_status_array[0];
                        $expired_to_date = $expired_status_array[1];
                    }
                }

                $type = $this->input->post('type');
                $remark = $this->input->post('remark');
                $data['from_date'] = $from_date;
                $data['to_date'] = $to_date;
                $data['id_insurer'] = $id_insurer;
                $data['expired_from_date'] = $expired_from_date;
                $data['expired_to_date'] = $expired_to_date;
                $data['type'] = $type;
                $data['remark'] = $remark;


                if ($id_insurer != '') {
                    $title.= 'FROM ' . insurer_by_id($id_insurer) . ', ';
                    $where_clause.=" id_insurer = $id_insurer and ";
                }

                if($expired_from_date <> '' && $expired_to_date <> ''){
                  $title.='Expired from ' . $expired_from_date;
                  $title.=' to ' . $expired_to_date;
                  $where_clause.=" (expired_on between " . date_to_int_string($expired_from_date) . " and " . date_to_int_string($expired_to_date) . ") and closed<1 and ";


                }else {
                    if ($from_date <> '' && $to_date <> '') {
                        $title.='Issued from ' . $from_date;
                        $title.=' to ' . $to_date;
                        $where_clause.=" (issued_on between " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
                    }
                }

                $title_2.=($type == 1) ? " MOTOR INSURANCE" : " NON MOTOR INSURANCE";
                $where_clause.=" type=$type and ";

                if ($remark != '') {
                    if ($remark == 1) {
                        $title_2.=" FULL PAID,";
                    }

                    if ($remark == 0) {
                        $title_2.=" NOT PAID,";
                    }
                    $where_clause.=" paid = $remark and ";
                }

                $where_clause.=" id_domain=" . $this->ion_auth->get_id_domain() . " and ";

                $where_clause = ($where_clause != '') ? remove_last_word($where_clause) : $where_clause;
                $sql = "select * from " . Modules::run('insurance/get_table');
                $sql.=($where_clause != '') ? " where $where_clause " : "";
                $sql.=" order by comm_on ";

                $data['insurances'] = Modules::run('insurance/_custom_query', $sql)->result();
                $data['module'] = 'report';
                $data['view_file'] = 'insurance/insurance_preview';
                $data['report_active'] = 'active';
                $data['insurance_report_active'] = 'active';
                $data['heading'] = 'Insurance Report ';
                $data['subheading'] = $title . $title_2;
                echo Modules::run('template/login_area', $data);
            }

            if ($load_report == FALSE) {
             // display registration form

                $data['date_range'] = array(
                    'type' => 'text',
                    'name' => 'date_range',
                    'id' => 'reservation',
                    //'class' => 'form-control pull-right',
                    'size' => '25',
                    'value' => $this->form_validation->set_value('date_range')
                );

                $data['expired_status'] = array(
                    'type' => 'text',
                    'name' => 'expired_status',
                    'id' => 'reservation1',
                    //'class' => 'form-control pull-right',
                    'size' => '25',
                    'value' => $this->form_validation->set_value('expired_status')
                );

                $data['type'] = $this->form_validation->set_value('type');
                $data['remark'] = $this->form_validation->set_value('remark');
                $data['insurer'] = $this->form_validation->set_value('insurer');

                $data['insurer_list'] = Modules::run('insurer/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
                // set the flash data error message if there is one
                $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
                $data['module'] = 'report';
                $data['view_file'] = 'insurance/option_form';
                $data['report_active'] = 'active';
                $data['insurance_report_active'] = 'active';
                $data['heading'] = 'Report';
                $data['subheading'] = 'insurance report';
                echo Modules::run('template/login_area', $data);
            }
        }
    }

//print insurance pdf
    function print_insurance_pdf() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        define('SMARTWEB_PAGE', 'L');
        $this->load->library('pdf');
        $this->pdf->set_id_domain($this->ion_auth->get_id_domain());
        // set document information
        $this->pdf->SetSubject('');
        $this->pdf->SetKeywords('');

        // set font
        $this->pdf->SetFont('times', '', 8.5);

        // add a page
        $this->pdf->AddPage();

        // footer margin
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $title = '';
        $title_2 = '';
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $insurer = $this->input->post('insurer');
        $expired_from_date = $this->input->post('expired_from_date');
        $expired_to_date = $this->input->post('expired_to_date');
        $type = $this->input->post('type');
        $remark = $this->input->post('remark');
        $colspan = 10;
        $top_heading = ($type == 1) ? "MOTOR" : "NON MOTOR";
        $where_clause = '';
        if ($insurer != '') {
            $title.= 'FROM ' . Modules::run('insurer/get_where_custom', array('id_insurer' => $insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->name . ', ';
            $where_clause.=" id_insurer = $insurer and ";
        }
        if($expired_from_date != ''){
          $title.='EXPIRED FROM ' . $expired_from_date;
          $title.=' TO ' . $expired_to_date;
          $where_clause.=" (expired_on between " . date_to_int_string($expired_from_date) . " and " . date_to_int_string($expired_to_date) . ") and closed<1 and ";

        }else {
            if ($from_date != '') {
                $title.='ISSUED FROM ' . $from_date;
                $title.=' TO ' . $to_date;
                $where_clause.=" (issued_on between " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
            }
        }

        $where_clause.=" type=$type and ";

        if ($remark != '') {
            if ($remark == 1) {
                $title_2.=" FULL PAID,";
            }

            if ($remark == 0) {
                $title_2.=" NOT PAID,";
            }
            $where_clause.=" paid = $remark and ";
        }

        $where_clause.=" id_domain=" . $this->ion_auth->get_id_domain() . " and ";
        $where_clause = ($where_clause != '') ? remove_last_word($where_clause) : $where_clause;
        $sql = "select * from " . Modules::run('insurance/get_table');
        $sql.=($where_clause != '') ? " where $where_clause " : "";
        $sql.=" order by comm_on asc";
        $insurances = Modules::run('insurance/_custom_query', $sql)->result();



//////////////////////////////////////////////////////////////////////////
        $html = '<link rel="stylesheet" type="text/css" href="' . base_url() . 'media/css/style.css">';

        $html.='<style type="text/css">

.claim_table{
border-top:1px solid #4B4B4B;
border-left:1px solid #4B4B4B;
border-bottom:1px solid #4B4B4B;
}

.claim_table tr td{
border-top:1px solid #4B4B4B;
border-right:1px solid #4B4B4B;
padding:3px 0px 3px 2px;
}

.claim_table tr.header_report td{
background-color:lightblue;
padding:3px 0px 3px 2px;
border-top:0px solid #4B4B4B;
}
</style>

<h2 style="padding:0px;margin:0px;">' . $top_heading . ' INSURANCE REPORT ' . strtoupper($title) . '</h2>
<h2 style="padding:0px;margin:0px;">' . strtoupper(trim($title_2, '')) . '</h2>

<div style="margin:20px; width:900px;">
<div>
<table class="claim_table" cellpadding="0" cellspacing="0">
<tr class="header_report">
<td style="width:80px;"> No </td>
<td width="200"> Comm Date </td>
<td width="200"> Expire Date</td>
<td width="width:400">Issued Name </td><td width="200">Phone</td>';
        if ($insurer == '') {
            $html.='<td width="200"> Insurer</td>';
        } else {
            $colspan--;
        }

        $html.='<td width="160"> Covernote</td>';
        if ($type == 1)
            $html.='<td width="150"> Sticker</td>';
        else
            $colspan--;

        $html.='<td width="200"> Number</td><td width="200"> Value</td>
<td width="width:200"> Premium</td><td width="width:200">Vat </td><td width="width:250"> Premium + Vat</td><td width="width:200"> Amount</td><td width="width:200"> Bal</td></tr>';
        $i = 1;
        $total_premium_amount = 0;
        $total_paid_amount = 0;
        $total_vat_amount = 0;

        foreach ($insurances as $insurance) {
            if ($insurer == '') {
                $insurer_row = Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            }
            $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();


            $payment_query = Modules::run('payment/get_where_custom', array('id_insurance' => $insurance->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()));
            $paid_amount = ($payment_query->num_rows() > 0) ? Modules::run('payment/sum_where', array('id_insurance' => $insurance->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain())) : 0;
            $html.='<tr>
<td align="center">' . $i++ . '</td>
<td>' . format_date('', $insurance->comm_on) . '</td>
<td> ' . format_date('', $insurance->expired_on) . ' </td>
<td> ' . $customer->name . ' </td><td> ' . $customer->phone . ' </td> ';
            if ($insurer == '') {
                $html.='<td> ' . $insurer_row->name . '</td>';
            }
            $html.= '<td> ' . $insurance->cover_note . '</td>';
            $html.=($type == 1) ? '<td> ' . $insurance->sticker_number . '</td>' : '';
            $html.='<td> ' . $property->number . '</td>
<td> ' . $property->property_value . '</td>
<td align="right"> ' . number_format($insurance->premium) . '&nbsp;&nbsp; </td>
<td align="right"> ';
            $html.=($insurance->vat > 0) ? number_format($insurance->vat) : '';
            $html.=' &nbsp;&nbsp; </td>
<td align="right"> ' . number_format($insurance->premium + $insurance->vat) . '&nbsp;&nbsp; </td>
<td align="right"> ';
            $html.=($paid_amount > 0) ? number_format($paid_amount) : '';
            $html.='&nbsp;&nbsp;  </td><td align="right"> ' . number_format($insurance->premium + $insurance->vat - $paid_amount) . ' &nbsp;&nbsp; </td></tr>';
            $total_premium_amount+=$insurance->premium;
            $total_paid_amount+=$paid_amount;
            $total_vat_amount+=$insurance->vat;
        }
        $html.='
            <tr>
<td colspan="' . $colspan . '" align="right" style="background-color:#C9C9C9;">Grand Total:</td>
<td align="right" style="background-color:#C9C9C9;">' . number_format($total_premium_amount) . '&nbsp;&nbsp;&nbsp;</td>
<td align="right" style="background-color:#C9C9C9;">' . number_format($total_vat_amount) . '&nbsp;&nbsp;&nbsp;</td>
<td align="right" style="background-color:#C9C9C9;">' . number_format($total_premium_amount + $total_vat_amount) . '&nbsp;&nbsp;&nbsp;</td>
    <td align="right" style="background-color:#C9C9C9;">' . number_format($total_paid_amount) . '&nbsp;&nbsp;&nbsp;</td>
<td align="right" style="background-color:#C9C9C9;">' . number_format($total_premium_amount + $total_vat_amount - $total_paid_amount) . '&nbsp;&nbsp;&nbsp;</td></tr>
</table>
</div>
</div>';
/////////////////////////////////////////////////////////////////////
// print a line using Cell()
        $this->pdf->writeHTML($html);
        $this->pdf->Output('insurance_report.pdf', 'I');
    }

//print insurance excel
    function print_insurance_excel() {
        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $this->load->library("excel");
//$papersize = 'PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4';
// set properties
        $this->excel->getProperties()->setCreator("Lackson David");
        $this->excel->getProperties()->setLastModifiedBy("Lackson David");
        $this->excel->getProperties()->setTitle("InsuranceReport");
        $this->excel->getProperties()->setSubject("");


        $title = '';
        $title_2 = '';
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $id_insurer = $this->input->post('insurer');
        $expired_from_date = $this->input->post('expired_from_date');
        $expired_to_date = $this->input->post('expired_to_date');
        $type = $this->input->post('type');
        $remark = $this->input->post('remark');
        $colspan = 10;
        $top_heading = ($type == 1) ? "MOTOR" : "NON MOTOR";
        $where_clause = '';
        if ($id_insurer <> '') {
            $title.= 'FROM ' . insurer_by_id($id_insurer) . ', ';
            $where_clause.=" id_insurer = $id_insurer and ";
        }

        if($expired_from_date != ''){
          $title.='EXPIRED FROM ' . $expired_from_date;
          $title.=' TO ' . $expired_to_date;
          $where_clause.=" (expired_on between " . date_to_int_string($expired_from_date) . " and " . date_to_int_string($expired_to_date) . ") and closed<1 and ";

        } else {
            if ($from_date <> '' && $to_date <> '') {
                $title.='ISSUED FROM ' . $from_date;
                $title.=' TO ' . $to_date;
                $where_clause.=" (issued_on between " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
            }
        }



        $where_clause.=" type=$type and ";

        if ($remark != '') {
            if ($remark == 1) {
                $title_2.=" FULL PAID,";
            }

            if ($remark == 0) {
                $title_2.=" NOT PAID,";
            }
            $where_clause.=" paid = $remark and ";
        }

        $where_clause.=" id_domain=" . $this->ion_auth->get_id_domain() . " and ";

        $where_clause = ($where_clause != '') ? remove_last_word($where_clause) : $where_clause;
        $sql = "select * from " . Modules::run('insurance/get_table');
        $sql.=($where_clause != '') ? " where $where_clause " : "";
        $sql.=' order by comm_on ';
        $insurances = Modules::run('insurance/_custom_query', $sql)->result();


        $style_title = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'size' => 12
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );

        $this->excel->setActiveSheetIndex(0);

//name the worksheet
        $this->excel->getActiveSheet()->setTitle('insurance report');

        $this->excel->getActiveSheet()->setCellValue('C2', $this->get_domain());
        $this->excel->getActiveSheet()->getStyle('C2')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('C2:F2');
        $this->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
        $this->excel->getActiveSheet()->setCellValue('C3', $top_heading . ' INSURANCE REPORT ' . strtoupper($title));
        $this->excel->getActiveSheet()->getStyle('C3')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('C3:F3');
        $this->excel->getActiveSheet()->getRowDimension(3)->setRowHeight(20);

        $this->excel->getActiveSheet()->setCellValue('C4', strtoupper($title_2));
        $this->excel->getActiveSheet()->getStyle('C4')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('C4:F4');
        $this->excel->getActiveSheet()->getRowDimension(4)->setRowHeight(20);


// style used in formating border of the cell
        $default_border = array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '1006A3'));

        $set_borders = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'font' => array(
                'name' => 'Arial',
                'italic' => false,
                'size' => 10,)
        );

        $style_header = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'E1E0F7'),
            ),
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'size' => 11,));


        $style_shade = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'DFD7CF'),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
        );

        $number = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,
            ),
            'font' => array(
                'name' => 'Arial',
                'italic' => false,
                'size' => 10,),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
        );

        $sheet = $this->excel->getActiveSheet();
        $col = "B";
        $rows = 7;
        $sheet->setCellValue($col . $rows, 'No');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(8);
        $col++;
        $sheet->setCellValue($col . $rows, 'Comm Date');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;
        $sheet->setCellValue($col . $rows, 'Expire Date');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;
        $sheet->setCellValue($col . $rows, 'Issued Name');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(20);
        $col++;
        $sheet->setCellValue($col . $rows, 'Phone');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;

        if ($id_insurer == '') {
            $sheet->setCellValue($col . $rows, 'Insurer');
            $sheet->getStyle($col . $rows)->applyFromArray($style_header);
            $sheet->getColumnDimension($col)->setWidth(17);
            $col++;
        }
        $sheet->setCellValue($col . $rows, 'Covernote');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;
        if ($type == 1):
            $sheet->setCellValue($col . $rows, 'Sticker');
            $sheet->getStyle($col . $rows)->applyFromArray($style_header);
            $sheet->getColumnDimension($col)->setWidth(10);
            $col++;
        endif;
        $sheet->setCellValue($col . $rows, 'Number');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;
        $sheet->setCellValue($col . $rows, 'Value');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;

        $sheet->setCellValue($col . $rows, 'Premium');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;

        $sheet->setCellValue($col . $rows, 'Vat');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;

        $sheet->setCellValue($col . $rows, 'Premium + Vat');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;

        $sheet->setCellValue($col . $rows, 'Paid Amount');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;

        $sheet->setCellValue($col . $rows, 'Out Bal');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;

        if ($remark == '') {
            $sheet->setCellValue($col . $rows, 'Remark');
            $sheet->getStyle($col . $rows)->applyFromArray($style_header);
            $sheet->getColumnDimension($col)->setWidth(10);
            $col++;
        }


        $rows++;
        $i = 1;
        $total_premium_amount = 0;
        $total_paid_amount = 0;
        $total_vat_amount = 0;

        foreach ($insurances as $insurance) {
            if ($id_insurer == '') {
                $insurer = Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            }
            $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();

            $payment_query = Modules::run('payment/get_where_custom', array('id_insurance' => $insurance->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()));
            $paid_amount = ($payment_query->num_rows() > 0) ? Modules::run('payment/sum_where', array('id_insurance' => $insurance->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain())) : 0;

            $col = "B";
            $total_premium_amount+=$insurance->premium;
            $total_paid_amount+=$paid_amount;
            $total_vat_amount+=$insurance->vat;

            $sheet->setCellValue($col . $rows, $i++);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, format_date('', $insurance->comm_on));
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, format_date('', $insurance->expired_on));
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $customer->name);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $customer->phone);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            if ($id_insurer == '') {
                $sheet->setCellValue($col . $rows, $insurer->name);
                $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
                $col++;
            }

            $sheet->setCellValue($col . $rows, $insurance->cover_note);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            if ($type == 1):
                $sheet->setCellValue($col . $rows, ($insurance->sticker_number > 0) ? $insurance->sticker_number : '');
                $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
                $col++;
            endif;

            $sheet->setCellValue($col . $rows, $property->number);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $property->property_value);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $insurance->premium);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, ($insurance->vat > 0) ? $insurance->vat : '');
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, $insurance->premium + $insurance->vat);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, ($paid_amount > 0) ? $paid_amount : '');
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, $insurance->premium + $insurance->vat - $paid_amount);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            if ($remark == '') {
                $sheet->setCellValue($col . $rows, paid_status_by_id($insurance->paid));
                $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
                $col++;
            }

            $rows++;
        }

        $col = "B";
        $sheet->setCellValue($col . $rows, 'Grand Total:');
        $col1 = $col;
        $col++;
        $col++;
        $col++;
        $col++;
        $col++;
        if ($type == 1)
            $col++;
        $col++;
        $col++;
        if ($id_insurer == '')
            $col++;

        $sheet->mergeCells($col1 . $rows . ':' . $col . $rows);
        $sheet->getStyle($col1 . $rows)->applyFromArray($style_shade);
        $col++;
        $sheet->setCellValue($col . $rows, $total_premium_amount);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $sheet->setCellValue($col . $rows, $total_vat_amount);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $sheet->setCellValue($col . $rows, $total_premium_amount + $total_vat_amount);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $sheet->setCellValue($col . $rows, $total_paid_amount);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $sheet->setCellValue($col . $rows, $total_premium_amount + $total_vat_amount - $total_paid_amount);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        if ($remark == '') {
            $sheet->setCellValue($col . $rows, '');
            $col1 = $col;

            $col++;
            $sheet->getStyle($col1 . $rows)->applyFromArray($style_shade);
        }

        $filename = 'insurance_report.xls'; //save our workbook as this file name
        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
        //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
        //if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
    }

// premium_remmitance function
    function premium_remmitance() {

        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $load_report = FALSE;

            $this->form_validation->set_rules('insurer', 'Insurer', 'trim|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $load_report = TRUE;
                $where_clause = '';
                $title = '';
                $title_2 = '';
                $from_date = '';
                $to_date = '';
                $date_range = $this->input->post('date_range');
                if ($date_range <> '') {
                    $date_range_array = explode('-', $date_range);
                    if (count($date_range_array) > 0) {
                        $from_date = $date_range_array[0];
                        $to_date = $date_range_array[1];
                        $title.='Paid from ' . $from_date;
                        $title.=' to ' . $to_date;
                        $where_clause.=" (paid_on between " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
                    }
                }

                $id_insurer = $this->input->post('insurer');
                $data['from_date'] = $from_date;
                $data['to_date'] = $to_date;
                $data['id_insurer'] = $id_insurer;

                if ($id_insurer <> '') {
                    $title.= 'FROM ' . insurer_by_id($id_insurer) . ', ';
                    $where_clause.=" id_insurance in (select id_insurance from insurance where id_insurer=$id_insurer) and ";
                }

                $where_clause.=' status=1 and id_domain=' . $this->ion_auth->get_id_domain() . ' and ';


                $where_clause = ($where_clause <> '') ? remove_last_word($where_clause) : $where_clause;
                $sql = "select * from " . Modules::run('payment/get_table');
                $sql.=($where_clause <> '') ? " where $where_clause " : "";

                $data['payments'] = Modules::run('payment/_custom_query', $sql)->result();
                $data['module'] = 'report';
                $data['view_file'] = 'remmitance/remmitance_preview';
                $data['report_active'] = 'active';
                $data['remmitance_report_active'] = 'active';
                $data['heading'] = 'Remmitance Report ';
                $data['subheading'] = $title . $title_2;
                echo Modules::run('template/login_area', $data);
            }

            if ($load_report == FALSE) {
// display registration form
                $data['date_range'] = array(
                    'type' => 'text',
                    'name' => 'date_range',
                    'id' => 'reservation',
                    //'class' => 'form-control pull-right',
                    'size' => '25',
                    'value' => $this->form_validation->set_value('date_range')
                );

                $data['insurer_list'] = Modules::run('insurer/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();

                $data['module'] = 'report';
                $data['view_file'] = 'remmitance/option_form';
                $data['report_active'] = 'active';
                $data['remmitance_report_active'] = 'active';
                $data['heading'] = 'Report';
                $data['subheading'] = 'remmitance report';
                echo Modules::run('template/login_area', $data);
            }
        }
    }

//print remmitance excel
    function print_remmitance_excel() {
        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $this->load->library("excel");
//$papersize = 'PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4';
// set properties
        $this->excel->getProperties()->setCreator("Lackson David");
        $this->excel->getProperties()->setLastModifiedBy("Lackson David");
        $this->excel->getProperties()->setTitle("RemmitanceReport");
        $this->excel->getProperties()->setSubject("");


        $where_clause = '';
        $title = '';
        $title_2 = '';

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $id_insurer = $this->input->post('insurer');

        if ($id_insurer <> '') {
            $title.= 'FROM ' . insurer_by_id($id_insurer) . ', ';
            $where_clause.=" id_insurance in (select id_insurance from insurance where id_insurer=$id_insurer) and ";
        }

        if ($from_date <> '' && $to_date <> '') {
            $title.='Paid from ' . $from_date;
            $title.=' to ' . $to_date;
            $where_clause.=" (paid_on between " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
        }

        $where_clause.=' status=1 and id_domain=' . $this->ion_auth->get_id_domain() . ' and ';


        $where_clause = ($where_clause <> '') ? remove_last_word($where_clause) : $where_clause;
        $sql = "select * from " . Modules::run('payment/get_table');
        $sql.=($where_clause <> '') ? " where $where_clause " : "";
        $payments = Modules::run('payment/_custom_query', $sql)->result();


        $style_title = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'size' => 12
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );

        $this->excel->setActiveSheetIndex(0);

//name the worksheet
        $this->excel->getActiveSheet()->setTitle('premium remmitance report');

        $this->excel->getActiveSheet()->setCellValue('C2', $this->get_domain());
        $this->excel->getActiveSheet()->getStyle('C2')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('C2:K2');
        $this->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);

        $this->excel->getActiveSheet()->setCellValue('C3', 'INSURANCE REPORT' . strtoupper($title));
        $this->excel->getActiveSheet()->getStyle('C3')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('C3:K3');
        $this->excel->getActiveSheet()->getRowDimension(3)->setRowHeight(20);

        $this->excel->getActiveSheet()->setCellValue('C4', strtoupper($title_2));
        $this->excel->getActiveSheet()->getStyle('C4')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('C4:K4');
        $this->excel->getActiveSheet()->getRowDimension(4)->setRowHeight(20);


// style used in formating border of the cell
        $default_border = array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '1006A3'));

        $set_borders = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'font' => array(
                'name' => 'Arial',
                'italic' => false,
                'size' => 10,)
        );

        $style_header = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'E1E0F7'),
            ),
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'size' => 11,));


        $style_shade = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'DFD7CF'),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
        );

        $number = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,
            ),
            'font' => array(
                'name' => 'Arial',
                'italic' => false,
                'size' => 10,),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
        );

        $sheet = $this->excel->getActiveSheet();
        $col = "B";
        $rows = 7;
        $sheet->setCellValue($col . $rows, 'No');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(12);
        $col++;
        $sheet->setCellValue($col . $rows, 'Date');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(12);
        $col++;
        $sheet->setCellValue($col . $rows, 'Issued Name');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(25);
        $col++;

        if ($id_insurer == '') {
            $sheet->setCellValue($col . $rows, 'Insurer');
            $sheet->getStyle($col . $rows)->applyFromArray($style_header);
            $sheet->getColumnDimension($col)->setWidth(17);
            $col++;
        }
        $sheet->setCellValue($col . $rows, 'Covernote');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;
        $sheet->setCellValue($col . $rows, 'Receipt');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;
        $sheet->setCellValue($col . $rows, 'Premium Paid');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;

        $sheet->setCellValue($col . $rows, 'Vat');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;
        $sheet->setCellValue($col . $rows, 'Commission');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;
        $sheet->setCellValue($col . $rows, 'Premium Remmited');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;

        $sheet->setCellValue($col . $rows, 'Premium Remmited+Vat');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;


        $rows++;
        $i = 1;
        $total_amount = 0;
        $total_vat = 0;
        $total_commission = 0;

        foreach ($payments as $payment) {
            $insurance = Modules::run('insurance/get_where_custom', array('id_insurance' => $payment->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();

            $vat = $insurance->vat;
            $commission = $insurance->commission;

            $col = "B";
            $total_amount+=$payment->amount;
            $total_vat+=$vat;
            $total_commission+=$commission;

            $sheet->setCellValue($col . $rows, $i++);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, format_date('', $payment->paid_on));
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $customer->name);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;


            if ($id_insurer == '') {
                $sheet->setCellValue($col . $rows, Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->name);
                $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
                $col++;
            }

            $sheet->setCellValue($col . $rows, $insurance->cover_note);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $payment->receipt);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $payment->amount - $vat);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, ($vat > 0) ? $vat : '');
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, $commission);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, $payment->amount - $commission - $vat);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, $payment->amount - $commission);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $rows++;
        }

        $col = "B";
        $sheet->setCellValue($col . $rows, 'Grand Total:');
        $col1 = $col;
        $col++;
        $col++;
        $col++;
        $col++;
        if ($id_insurer == '')
            $col++;
        $sheet->mergeCells($col1 . $rows . ':' . $col . $rows);
        $sheet->getStyle($col1 . $rows)->applyFromArray($style_shade);
        $col++;
        $sheet->setCellValue($col . $rows, $total_amount - $total_vat);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $sheet->setCellValue($col . $rows, $total_vat);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $sheet->setCellValue($col . $rows, $total_commission);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $sheet->setCellValue($col . $rows, $total_amount - $total_commission - $total_vat);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $sheet->setCellValue($col . $rows, $total_amount - $total_commission);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $filename = 'premium_remmitance_report.xls'; //save our workbook as this file name
        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
//if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
//force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
    }

//print remmitance pdf
    function print_remmitance_pdf() {
        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        define('SMARTWEB_PAGE', 'L');
        $this->load->library('pdf');

// set document information
        $this->pdf->SetSubject('');
        $this->pdf->SetKeywords('');

// set font
        $this->pdf->SetFont('times', '', 8.5);
// add a page
        $this->pdf->AddPage();

// footer margin
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $where_clause = '';
        $title = '';
        $title_2 = '';
        $colspan = 6;

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $id_insurer = $this->input->post('insurer');

        if ($id_insurer <> '') {
            $title.= 'FROM ' . insurer_by_id($id_insurer) . ', ';
            $where_clause.=" id_insurance in (select id_insurance from insurance where id_insurer=$id_insurer) and ";
        }

        if ($from_date <> '' && $to_date <> '') {
            $title.='Paid from ' . $from_date;
            $title.=' to ' . $to_date;
            $where_clause.=" (paid_on between " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
        }

        $where_clause.=' status=1 and id_domain=' . $this->ion_auth->get_id_domain() . ' and ';

        $where_clause = ($where_clause <> '') ? remove_last_word($where_clause) : $where_clause;
        $sql = "select * from " . Modules::run('payment/get_table');
        $sql.=($where_clause <> '') ? " where $where_clause " : "";

        $payments = Modules::run('payment/_custom_query', $sql)->result();

//////////////////////////////////////////////////////////////////////////
        $html = '<link rel = "stylesheet" type = "text/css" href = "' . base_url() . 'media/css/style.css">';

        $html.='<style type = "text/css">

.claim_table{
border-top:1px solid #4B4B4B;
border-left:1px solid #4B4B4B;
border-bottom:1px solid #4B4B4B;
}

.claim_table tr td{
border-top:1px solid #4B4B4B;
border-right:1px solid #4B4B4B;
padding:3px 0px 3px 2px;
}

.claim_table tr.header_report td{
background-color:lightblue;
padding:3px 0px 3px 2px;
border-top:0px solid #4B4B4B;
}
</style>
<h2 style="padding:0px;margin:0px;"> INSURANCE REPORT ' . strtoupper($title) . '</h2>
<h2 style="padding:0px;margin:0px;">' . strtoupper(trim($title_2, '')) . '</h2>

<div style = "margin:20px; width:900px;">
<div>
<table class = "claim_table" cellpadding = "0" cellspacing = "0">
<tr class = "header_report">
<td style = "width:80px;"> No </td>
<td width = "200"> Date </td>
<td width = "width:400">Issued Name </td>';
        if ($id_insurer == '') {
            $html.='<td width = "200"> Insurer</td>';
        } else {
            $colspan--;
        }
        $html.='<td width = "150">Covernote </td>
<td width = "150">Receipt </td>
<td width = "width:200">Premium Paid </td><td width = "width:200"> Vat</td><td width = "width:200">Commission</td><td width = "width:200"> Premium Remmited</td><td width = "width:200">Premium Remmited+Vat</td></tr>';


        $i = 1;
        $total_amount = 0;
        $total_vat = 0;
        $total_commission = 0;

        foreach ($payments as $payment) {
            $insurance = Modules::run('insurance/get_where_custom', array('id_insurance' => $payment->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();

            $vat = $insurance->vat;
            $commission = $insurance->commission;
            $total_amount+=$payment->amount;
            $total_vat+=$vat;
            $total_commission+=$commission;

            $html.='<tr>
<td align = "center">' . $i++ . '</td>
<td>' . format_date('', $payment->paid_on) . '</td>
<td> ' . $customer->name . ' </td> ';
            if ($id_insurer == '') {
                $html.='<td> ' . Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->name . '</td>';
            }

            $html.= '<td> ' . $insurance->cover_note . '</td><td> ' . $payment->receipt . '</td>
<td align = "right"> ' . number_format($payment->amount - $vat) . ' </td>
<td align = "right"> ';
            $html.=($vat > 0) ? number_format($vat) : '';
            $html.=' </td>
<td align = "right"> ' . number_format($commission) . ' </td>
<td align = "right"> ' . number_format($payment->amount - $commission - $vat) . ' </td><td align = "right"> ' . number_format($payment->amount - $commission) . ' </td></tr>';
        }
        $html.='
<tr>
<td colspan = "' . $colspan . '" align = "right" style = "background-color:#C9C9C9;">Grand Total:</td>
<td align = "right" style = "background-color:#C9C9C9;">' . number_format($total_amount - $total_vat) . '</td>
<td align = "right" style = "background-color:#C9C9C9;">' . number_format($total_vat) . '</td>
<td align = "right" style = "background-color:#C9C9C9;">' . number_format($total_commission) . '</td>
<td align = "right" style = "background-color:#C9C9C9;">' . number_format($total_amount - $total_commission - $total_vat) . '</td>
<td align = "right" style = "background-color:#C9C9C9;">' . number_format($total_amount - $total_commission) . '</td></tr>
</table>
</div>
</div>';
/////////////////////////////////////////////////////////////////////
        // print a line using Cell()
        $this->pdf->writeHTML($html);

        $this->pdf->Output('premium_remmitance_report.pdf', 'I');
    }

    // quarter_return function
    function quarter_return() {
        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('site/login', 'refresh');
        } else {
            $this->form_validation->set_rules('year', 'Year', 'trim|numeric|max_length[4]|xss_clean');
            $this->form_validation->set_rules('quarter', 'Quarter', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('insurer', 'Insurer', 'trim|xss_clean');

            if ($this->form_validation->run() == TRUE) {

                $this->load->library("excel");
                // set properties
                $this->excel->getProperties()->setCreator("Lackson David");
                $this->excel->getProperties()->setLastModifiedBy("Lackson David");
                $this->excel->getProperties()->setTitle("QuarterReturn");
                $this->excel->getProperties()->setSubject("");

                $inner_join = '';
                $where_clause = '';
                $title = '';
                $title_2 = '';
                $from_date = '';
                $to_date = '';

                $year = $this->input->post('year');
                $quarter = $this->input->post('quarter');
                $id_insurer = $this->input->post('insurer');
                $type = $this->input->post('type');

                if ($quarter == 1) {
                    $from_month = 1;
                    $to_month = 3;
                    $from_date = '-01-01';
                    $to_date = '-03-31';
                } elseif ($quarter == 2) {
                    $from_month = 4;
                    $to_month = 6;
                    $from_date = '-04-01';
                    $to_date = '-06-30';
                } elseif ($quarter == 3) {
                    $from_month = 7;
                    $to_month = 9;
                    $from_date = '-07-01';
                    $to_date = '-09-30';
                } else {
                    $from_month = 10;
                    $to_month = 12;
                    $from_date = '-10-01';
                    $to_date = '-12-31';
                }
                if ($year <> '') {
                    $from_year = $year;
                    $from_date = $from_year . $from_date;
                    $to_date = $from_year . $to_date;
                } else {
                    $from_year = date('Y');
                    $from_date = $from_year . $from_date;
                    $to_date = $from_year . $to_date;
                }

                $title.=" Brokers Premium Collections Report- Quarter Ended $to_date ";

                if ($id_insurer <> '') {
                    $title_2.= 'FROM ' . insurer_by_id($id_insurer) . ', ';
                    $inner_join.=" inner join insurance on payment.id_insurance=insurance.id_insurance ";
                    $where_clause.=" insurance.id_insurer = $id_insurer and ";
                }


                $where_clause.=" FROM_UNIXTIME(paid_on,'%Y-%m-%d') between  '" . $from_date . "' and '" . $to_date . "' and ";


                $where_clause.=' payment.status = 1 and payment.id_domain=' . $this->ion_auth->get_id_domain() . ' and ';


                $where_clause = ($where_clause != '') ? remove_last_word($where_clause) : $where_clause;
                $sql = "select sum( `amount`) as amount  , FROM_UNIXTIME( paid_on,'%Y-%m-%d') as paid_on  from " . Modules::run('payment/get_table');
                $sql.=$inner_join;
                $sql.=($where_clause <> '') ? " where $where_clause group by FROM_UNIXTIME( paid_on,'%Y-%m-%d')  order by paid_on " : "";

                $payments = Modules::run('payment/_custom_query', $sql)->result();
                $returns = array();
                foreach ($payments as $payment) {
                    $returns[$payment->paid_on] = $payment->amount;
                }

                $style_title = array(
                    'font' => array(
                        'name' => 'Arial',
                        'bold' => true,
                        'italic' => false,
                        'size' => 12
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    ),
                );

                $this->excel->setActiveSheetIndex(0);

                //name the worksheet
                $this->excel->getActiveSheet()->setTitle('quarter return report');

                $this->excel->getActiveSheet()->setCellValue('A2', $this->get_domain());
                $this->excel->getActiveSheet()->getStyle('A2')->applyFromArray($style_title);
                $this->excel->getActiveSheet()->mergeCells('A2:F2');
                $this->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);

                $this->excel->getActiveSheet()->setCellValue('A3', strtoupper($title));
                $this->excel->getActiveSheet()->getStyle('A3')->applyFromArray($style_title);
                $this->excel->getActiveSheet()->mergeCells('A3:F3');
                $this->excel->getActiveSheet()->getRowDimension(3)->setRowHeight(20);

                $this->excel->getActiveSheet()->setCellValue('A4', strtoupper($title_2));
                $this->excel->getActiveSheet()->getStyle('A4')->applyFromArray($style_title);
                $this->excel->getActiveSheet()->mergeCells('A4:F4');
                $this->excel->getActiveSheet()->getRowDimension(4)->setRowHeight(20);


                // style used in formating border of the cell
                $default_border = array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '1006A3'));

                $set_borders = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,),
                    'font' => array(
                        'name' => 'Arial',
                        'italic' => false,
                        'size' => 10,)
                );

                $style_header = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array(
                            'rgb' => 'E1E0F7'),
                    ),
                    'font' => array(
                        'name' => 'Arial',
                        'bold' => true,
                        'italic' => false,
                        'size' => 11,));


                $style_shade = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array(
                            'rgb' => 'DFD7CF'),
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    ),
                );

                $number = array(
                    'borders' => array(
                        'bottom' => $default_border,
                        'left' => $default_border,
                        'top' => $default_border,
                        'right' => $default_border,
                    ),
                    'font' => array(
                        'name' => 'Arial',
                        'italic' => false,
                        'size' => 10,),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    ),
                );

                $sheet = $this->excel->getActiveSheet();
                $col = "A";
                $rows = 7;
                $sheet->setCellValue($col . $rows, 'Date of Month');
                $sheet->getStyle($col . $rows)->applyFromArray($style_header);
                $sheet->getColumnDimension($col)->setWidth(15);
                $col++;
                $sheet->setCellValue($col . $rows, '1st Month');
                $sheet->getStyle($col . $rows)->applyFromArray($style_header);
                $sheet->getColumnDimension($col)->setWidth(15);
                $col++;
                $sheet->setCellValue($col . $rows, '2nd Month');
                $sheet->getStyle($col . $rows)->applyFromArray($style_header);
                $sheet->getColumnDimension($col)->setWidth(15);
                $col++;
                $sheet->setCellValue($col . $rows, '3rd Month');
                $sheet->getStyle($col . $rows)->applyFromArray($style_header);
                $sheet->getColumnDimension($col)->setWidth(15);
                $col++;
                $rows++;

                $col = "A";
                $sheet->setCellValue($col . $rows, '');
                $sheet->getStyle($col . $rows)->applyFromArray($style_header);
                $sheet->getColumnDimension($col)->setWidth(15);
                $col++;
                $sheet->setCellValue($col . $rows, 'Current Quarter (A)');
                $sheet->getStyle($col . $rows)->applyFromArray($style_header);
                $sheet->getColumnDimension($col)->setWidth(15);
                $col++;
                $sheet->setCellValue($col . $rows, 'Current Quarter (B)');
                $sheet->getStyle($col . $rows)->applyFromArray($style_header);
                $sheet->getColumnDimension($col)->setWidth(15);
                $col++;
                $sheet->setCellValue($col . $rows, 'Current Quarter (C)');
                $sheet->getStyle($col . $rows)->applyFromArray($style_header);
                $sheet->getColumnDimension($col)->setWidth(15);
                $rows++;

                $total_1 = 0;
                $total_2 = 0;
                $total_3 = 0;
                for ($day = 1; $day <= 31; $day++) {
                    $col = "A";

                    $sheet->setCellValue($col . $rows, ordinal($day));
                    $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
                    $col++;

                    for ($start_month = $from_month; $start_month <= $to_month; $start_month++) {

                        $paid_on = $from_year . '-';
                        $paid_on.=($start_month > 9) ? $start_month : "0$start_month";
                        $paid_on.='-';
                        $paid_on.=($day > 9) ? $day : "0$day";
                        $amount = (array_key_exists($paid_on, $returns)) ? $returns[$paid_on] : '';
                        if ($start_month - $from_month == 0)
                            $total_1+=$amount;
                        elseif ($start_month - $from_month == 1)
                            $total_2+=$amount;
                        else
                            $total_3+=$amount;

                        $sheet->setCellValue($col . $rows, $amount);
                        $sheet->getStyle($col . $rows)->applyFromArray($number);
                        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
                        $col++;
                    }
                    $rows++;
                }

                $col = "A";
                $sheet->setCellValue($col . $rows, 'Grand Total:');

                $sheet->mergeCells($col . $rows . ':' . $col . $rows);
                $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
                $col++;
                $sheet->setCellValue($col . $rows, $total_1);
                $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
                $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
                $col++;

                $sheet->setCellValue($col . $rows, $total_2);
                $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
                $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
                $col++;

                $sheet->setCellValue($col . $rows, $total_3);
                $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
                $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
                $col++;

                $rows++;
                $col = "A";
                $col1 = $col;
                $sheet->setCellValue($col . $rows, ' Total Premium Collection-Current Quarter A+B+C');
                $col1++;
                $col1++;

                $sheet->mergeCells($col . $rows . ':' . $col1 . $rows);
                $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
                $col++;
                $col1++;

                $sheet->setCellValue($col1 . $rows, ($total_1 + $total_2 + $total_3));
                $sheet->getStyle($col1 . $rows)->applyFromArray($style_shade);
                $sheet->getStyle($col1 . $rows)->getNumberFormat()->setFormatCode('#,##0');
                $col++;

                $filename = 'quarter_return_report.xls'; //save our workbook as this file name
                header('Content-Type: application/vnd.ms-excel'); //mime type
                header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
                header('Cache-Control: max-age=0'); //no cache
//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
//if you want to save it as .XLSX Excel 2007 format
                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
//force user to download the Excel file without writing it to server's HD
                $objWriter->save('php://output');
            }
// display registration form

            $data['year'] = array(
                'type' => 'text',
                'name' => 'year',
                'placeholder' => 'eg 2014',
                'size' => '20',
                'value' => $this->form_validation->set_value('year')
            );

            $data['quarter'] = $this->form_validation->set_value('quarter');
            $data['insurer'] = $this->form_validation->set_value('insurer');
            $data['type'] = $this->form_validation->set_value('type');



            $data['insurer_list'] = Modules::run('insurer/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();
            // set the flash data error message if there is one
            $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
            // display registration form
            $data['module'] = 'report';
            $data['view_file'] = 'quarter_return/form';
            $data['report_active'] = 'active';
            $data['quarter_return_report_active'] = 'active';
            $data['heading'] = 'Report';
            $data['subheading'] = 'quarter return';
            echo Modules::run('template/login_area', $data);
        }
    }

// premium_collection function
    function premium_collection() {

        if (!$this->ion_auth->logged_in()) {
         // redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        } else {
            $load_report = FALSE;

            $this->form_validation->set_rules('insurer', 'Insurer', 'trim|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $load_report = TRUE;
                $where_clause = '';
                $title = '';
                $title_2 = '';
                $from_date = '';
                $to_date = '';
                $date_range = $this->input->post('date_range');
                if ($date_range <> '') {
                    $date_range_array = explode('-', $date_range);
                    if (count($date_range_array) > 0) {
                        $title.='Paid from ' . $from_date;
                        $title.=' to ' . $to_date;
                        $from_date = $date_range_array[0];
                        $to_date = $date_range_array[1];
                        $where_clause.=" (paid_on between " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
                    }
                }

                $id_insurer = $this->input->post('insurer');
                $data['from_date'] = $from_date;
                $data['to_date'] = $to_date;
                $data['id_insurer'] = $id_insurer;

                if ($id_insurer <> '') {
                    $insurer=Modules::run('insurer/get_where_custom', array('id_insurer' => $id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
                    $title.= 'FROM ' . insurer_by_id($id_insurer) . ', ';
                    $where_clause.=" payment.id_insurance in (select insurance.id_insurance from insurance where insurance.id_insurer=$id_insurer) and ";
                }
                $where_clause.=" payment.status=1 and payment.id_domain=" . $this->ion_auth->get_id_domain()." and insurance.paid=1";

                $sql = "select * from " . Modules::run('payment/get_table'). " inner join insurance on payment.id_insurance=insurance.id_insurance";
                $sql.=($where_clause <> '') ? " where $where_clause " : "";

                $data['payments'] = Modules::run('payment/_custom_query', $sql)->result();
                $data['module'] = 'report';
                $data['view_file'] = 'premium_collection/preview';
                $data['report_active'] = 'active';
                $data['premium_collection_report_active'] = 'active';
                $data['heading'] = 'Premium Collection ';
                $data['subheading'] = $title . $title_2;
                echo Modules::run('template/login_area', $data);
            }

            if ($load_report == FALSE) {
// display registration form
                $data['date_range'] = array(
                    'type' => 'text',
                    'name' => 'date_range',
                    'id' => 'reservation',
                    'size' => '25',
                    'value' => $this->form_validation->set_value('date_range')
                );

                $data['insurer_list'] = Modules::run('insurer/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();

                $data['module'] = 'report';
                $data['view_file'] = 'premium_collection/form';
                $data['report_active'] = 'active';
                $data['premium_collection_report_active'] = 'active';
                $data['heading'] = 'Report';
                $data['subheading'] = 'premium collection';
                echo Modules::run('template/login_area', $data);
            }
        }
    }

//print premium collection excel
    function print_premium_collection_excel() {
        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $this->load->library("excel");
//$papersize = 'PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4';
// set properties
        $this->excel->getProperties()->setCreator("Lackson David");
        $this->excel->getProperties()->setLastModifiedBy("Lackson David");
        $this->excel->getProperties()->setTitle("PremiumCollection");
        $this->excel->getProperties()->setSubject("");


        $where_clause = '';
        $title = '';
        $title_2 = '';

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $id_insurer = $this->input->post('insurer');

        if ($id_insurer != '') {
            $title.= 'FROM ' . insurer_by_id($id_insurer) . ', ';
            $where_clause.=" payment.id_insurance in (select insurance.id_insurance from insurance where insurance.id_insurer=$id_insurer) and ";
        }

        if ($from_date <> '' && $to_date <> '') {
            $title.='Paid from ' . $from_date;
            $title.=' to ' . $to_date;
            $where_clause.=" (paid_on between " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
        }

        $where_clause.=' payment.status=1 and payment.id_domain=' . $this->ion_auth->get_id_domain() . ' and insurance.paid=1';

        $sql = "select * from " . Modules::run('payment/get_table')." inner join insurance on payment.id_insurance=insurance.id_insurance";
        $sql.=($where_clause <> '') ? " where $where_clause " : "";

        $payments = Modules::run('payment/_custom_query', $sql)->result();


        $style_title = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'size' => 12
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );

        $this->excel->setActiveSheetIndex(0);

//name the worksheet
        $this->excel->getActiveSheet()->setTitle('premium collection report');

        $this->excel->getActiveSheet()->setCellValue('B2', $this->get_domain());
        $this->excel->getActiveSheet()->getStyle('B2')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('B2:F2');
        $this->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);

        $this->excel->getActiveSheet()->setCellValue('B3', 'PREMIUM COLLECTION REPORT' . strtoupper($title));
        $this->excel->getActiveSheet()->getStyle('B3')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('B3:F3');
        $this->excel->getActiveSheet()->getRowDimension(3)->setRowHeight(20);

        $this->excel->getActiveSheet()->setCellValue('B4', strtoupper($title_2));
        $this->excel->getActiveSheet()->getStyle('B4')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('B4:F4');
        $this->excel->getActiveSheet()->getRowDimension(4)->setRowHeight(20);


// style used in formating border of the cell
        $default_border = array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '1006A3'));

        $set_borders = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'font' => array(
                'name' => 'Arial',
                'italic' => false,
                'size' => 10,)
        );

        $style_header = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'E1E0F7'),
            ),
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'size' => 11,));


        $style_shade = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'DFD7CF'),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
        );

        $number = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,
            ),
            'font' => array(
                'name' => 'Arial',
                'italic' => false,
                'size' => 10,),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
        );

        $sheet = $this->excel->getActiveSheet();
        $col = "A";
        $rows = 7;
        $sheet->setCellValue($col . $rows, 'No');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(8);
        $col++;
        $sheet->setCellValue($col . $rows, 'Date');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;
        $sheet->setCellValue($col . $rows, 'Issued Name');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(20);
        $col++;

        $sheet->setCellValue($col . $rows, 'Receipt');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;
        $sheet->setCellValue($col . $rows, 'Covernote');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;
        $sheet->setCellValue($col . $rows, 'Amount');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;


        $sheet->setCellValue($col . $rows, 'Commission');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;


        $sheet->setCellValue($col . $rows, 'Amount to Insurer');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;


        if ($id_insurer == '') {
            $sheet->setCellValue($col . $rows, 'Insurer');
            $sheet->getStyle($col . $rows)->applyFromArray($style_header);
            $sheet->getColumnDimension($col)->setWidth(17);
            $col++;
        }


        $rows++;
        $i = 1;
        $total_amount = 0;
        $total_vat = 0;
        $total_commission = 0;

        foreach ($payments as $payment) {
            $insurance = Modules::run('insurance/get_where_custom', array('id_insurance' => $payment->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();

            $vat = $insurance->vat;
            $commission = $insurance->commission;

            $col = "A";
            $total_amount+=$payment->amount;
            $total_vat+=$vat;
            $total_commission+=$commission;

            $sheet->setCellValue($col . $rows, $i++);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, format_date('', $payment->paid_on));
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $customer->name);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;


            $sheet->setCellValue($col . $rows, $payment->receipt);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $insurance->cover_note);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $payment->amount);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, $commission);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $sheet->setCellValue($col . $rows, ($payment->amount - $commission));
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            if ($id_insurer == '') {
                $sheet->setCellValue($col . $rows, Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->name);
                $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
                $col++;
            }


            $rows++;
        }

        $col = "A";
        $sheet->setCellValue($col . $rows, 'Grand Total:');
        $col1 = $col;
        $col++;
        $col++;
        $col++;
        $col++;

        $sheet->mergeCells($col1 . $rows . ':' . $col . $rows);
        $sheet->getStyle($col1 . $rows)->applyFromArray($style_shade);
        $col++;
        $sheet->setCellValue($col . $rows, $total_amount);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $sheet->setCellValue($col . $rows, $total_commission);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;

        $sheet->setCellValue($col . $rows, $total_amount - $total_commission);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;
        if ($id_insurer == '') {
            $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
            $col++;
        }

        $filename = 'premium_collection_report.xls'; //save our workbook as this file name
        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
//if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
//force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
    }

//print premium collection pdf
    function print_premium_collection_pdf() {
        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        define('SMARTWEB_PAGE', 'L');
        $this->load->library('pdf');

// set document information
        $this->pdf->SetSubject('');
        $this->pdf->SetKeywords('');

// set font
        $this->pdf->SetFont('times', '', 8.5);

// add a page
        $this->pdf->AddPage();

// footer margin
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $where_clause = '';
        $title = '';
        $title_2 = '';
        $colspan = 5;

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $id_insurer = $this->input->post('insurer');

        if ($id_insurer <> '') {
            $title.= 'FROM ' . insurer_by_id($id_insurer) . ', ';
            $where_clause.=" payment.id_insurance in (select payment.id_insurance from insurance where id_insurer=$id_insurer) and ";
        }

        if ($from_date <> '' && $to_date <> '') {
            $title.='Paid from ' . $from_date;
            $title.=' to ' . $to_date;
            $where_clause.=" (paid_on between " . date_to_int_string($from_date) . " and " . date_to_int_string($to_date) . ") and ";
        }

        $where_clause.=' payment.status=1 and payment.id_domain=' . $this->ion_auth->get_id_domain() . ' and insurance.paid=1';

        $sql = "select * from " . Modules::run('payment/get_table')." inner join insurance on payment.id_insurance=insurance.id_insurance";
        $sql.=($where_clause <> '') ? " where $where_clause " : "";

        $payments = Modules::run('payment/_custom_query', $sql)->result();

//////////////////////////////////////////////////////////////////////////
        $html = '<link rel="stylesheet" type="text/css" href="' . base_url() . 'media/css/style.css">';

        $html.='<style type="text/css">

.claim_table{
border-top:1px solid #4B4B4B;
border-left:1px solid #4B4B4B;
border-bottom:1px solid #4B4B4B;
}

.claim_table tr td{
border-top:1px solid #4B4B4B;
border-right:1px solid #4B4B4B;
padding:3px 0px 3px 2px;
}

.claim_table tr.header_report td{
background-color:lightblue;
padding:3px 0px 3px 2px;
border-top:0px solid #4B4B4B;
}
</style>

<h2 style="padding:0px;margin:0px;"> PREMIUM COLLECTION REPORT ' . strtoupper($title) . '</h2>
<h2 style="padding:0px;margin:0px;">' . strtoupper(trim($title_2, '')) . '</h2>

<div style="margin:20px; width:900px;">
<div>
<table class="claim_table" cellpadding="0" cellspacing="0">
<tr class="header_report">
<td width="80"> No </td>
<td width="300" align="center"> Date </td>
<td width="600" align="center">Issued Name </td>
<td width="200" align="center">Receipt </td>
<td width="200" align="center">Covernote </td>
<td width="300" align="center">Amount</td>
<td width="300" align="center">Commission</td>
<td width="300" align="center">Amount to Insurer</td>';
        if ($id_insurer == '') {
            $html.='<td width="500"> Insurer</td>';
        }
        $html.='</tr>';


        $i = 1;
        $total_amount = 0;
        $total_vat = 0;
        $total_commission = 0;

        foreach ($payments as $payment) {
            $insurance = Modules::run('insurance/get_where_custom', array('id_insurance' => $payment->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();

            $vat = $insurance->vat;
            $commission = $insurance->commission;
            $total_amount+=$payment->amount;
            $total_vat+=$vat;
            $total_commission+=$commission;

            $html.='<tr>
<td align="center">' . $i++ . '</td>
<td align="center">' . format_date('', $payment->paid_on) . '</td>
<td align="center"> ' . $customer->name . ' </td>
<td align="center"> ' . $payment->receipt . '</td><td align="center"> ' . $insurance->cover_note . '</td>
<td align="center"> ' . number_format($payment->amount) . ' </td>
<td align="center"> ' . number_format($commission) . ' </td>
<td align="center"> ' . number_format($payment->amount - $commission) . ' </td>';
            if ($id_insurer == '') {
                $html.='<td> ' . Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->name . '</td>';
            }
            $html.='</tr>';
        }
        $html.='
            <tr>
<td colspan="' . $colspan . '" align="right" style="background-color:#C9C9C9;">Grand Total:</td>
<td align="center" style="background-color:#C9C9C9;">' . number_format($total_amount) . '</td>
<td align="center" style="background-color:#C9C9C9;">' . number_format($total_commission) . '</td>
<td align="center" style="background-color:#C9C9C9;">' . number_format($total_amount - $total_commission) . '</td>';
        if ($id_insurer == '') {
            $html.='<td style="background-color:#C9C9C9;"></td>';
        }
        $html.='</tr></table>
</div>
</div>';
/////////////////////////////////////////////////////////////////////
// print a line using Cell()
        $this->pdf->writeHTML($html);

        $this->pdf->Output('premium_collection_report.pdf', 'I');
    }

// bank_transaction
    function bank_transaction() {

        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
// redirect to error message
            $this->show_error();
        } else {
            $load_report = FALSE;

            $this->form_validation->set_rules('transaction_type', 'Transaction Type', 'trim|xss_clean');

            if ($this->form_validation->run() == TRUE) {
                $load_report = TRUE;
                $where_clause = '';
                $title = ' bank transaction ';
                $title_2 = '';
                $from_date = '';
                $to_date = '';
                $date_range = $this->input->post('date_range');
                if ($date_range <> '') {
                    $date_range_array = explode('-', $date_range);
                    if (count($date_range_array) > 0) {
                        $from_date = $date_range_array[0];
                        $to_date = $date_range_array[1];
                    }
                }

                $data['from_date'] = $from_date;
                $data['to_date'] = $to_date;
                $id_transaction_type = $this->input->post('transaction_type');
                $id_bank_account = $this->input->post('bank_account');
                $data['id_transaction_type'] = $id_transaction_type;
                $data['id_bank_account'] = $id_bank_account;

                if ($id_transaction_type != '') {
                    $title.= Modules::run('transaction_type/get_where', $id_transaction_type)->row()->type . ', ';
                    $where_clause.=" id_transaction_type=$id_transaction_type and ";
                }

                if ($id_bank_account != '') {
                    $bank_account_row = Modules::run('bank_account/get_where_custom', array('id_bank_account' => $id_bank_account, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
                    $title.= ' from ' . $bank_account_row->bank_name . ' [' . $bank_account_row->description . '], ';
                    $where_clause.=" id_bank_account=$id_bank_account and ";
                }


                if ($from_date != '') {
                    $title.='Issued from ' . $from_date;
                    $where_clause.=" issued_on >= " . date_to_int_string($from_date) . " and ";
                }

                if ($to_date != '') {
                    $title.=' to ' . $to_date;
                    $where_clause.=" issued_on <= " . date_to_int_string($to_date) . " and ";
                }
                $where_clause.=" id_domain=" . $this->ion_auth->get_id_domain() . " and ";

                $where_clause = ($where_clause != '') ? remove_last_word($where_clause) : $where_clause;
                $sql = "select * from " . Modules::run('bank_transaction/get_table');
                $sql.=($where_clause != '') ? " where $where_clause " : "";

                $data['bank_transactions'] = Modules::run('bank_transaction/_custom_query', $sql)->result();
                $data['module'] = 'report';
                $data['view_file'] = 'bank_transaction/preview';
                $data['report_active'] = 'active';
                $data['bank_transaction_active'] = 'active';
                $data['heading'] = 'Report ';
                $data['subheading'] = $title . $title_2;
                echo Modules::run('template/login_area', $data);
            }

            if ($load_report == FALSE) {
// display registration form
                $data['date_range'] = array(
                    'type' => 'text',
                    'name' => 'date_range',
                    'id' => 'reservation',
                    'size' => '25',
                    'value' => $this->form_validation->set_value('date_range')
                );

                $data['transaction_type_list'] = Modules::run('transaction_type/get', 'type')->result_array();
                $data['bank_account_list'] = Modules::run('bank_account/get_where_custom', array('id_domain' => $this->ion_auth->get_id_domain()))->result_array();

                $data['module'] = 'report';
                $data['view_file'] = 'bank_transaction/form';
                $data['report_active'] = 'active';
                $data['bank_transaction_active'] = 'active';
                $data['heading'] = 'Report';
                $data['subheading'] = 'bank transaction';
                echo Modules::run('template/login_area', $data);
            }
        }
    }

//print bank transaction excel
    function print_bank_transaction_excel() {
        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        $this->load->library("excel");
//$papersize = 'PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4';
// set properties
        $this->excel->getProperties()->setCreator("Lackson David");
        $this->excel->getProperties()->setLastModifiedBy("Lackson David");
        $this->excel->getProperties()->setTitle("PremiumCollection");
        $this->excel->getProperties()->setSubject("");


        $where_clause = '';
        $title = ' ';
        $title_2 = ' ';

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $id_transaction_type = $this->input->post('transaction_type');
        $id_bank_account = $this->input->post('bank_account');

        if ($id_transaction_type != '') {
            $title.= Modules::run('transaction_type/get_where', $id_transaction_type)->row()->type . ', ';
            $where_clause.=" id_transaction_type=$id_transaction_type and ";
        }

        if ($id_bank_account != '') {
            $bank_account_row = Modules::run('bank_account/get_where_custom', array('id_bank_account' => $id_bank_account, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $title.= ' from ' . $bank_account_row->bank_name . ' [' . $bank_account_row->description . '], ';
            $where_clause.=" id_bank_account=$id_bank_account and ";
        }

        if ($from_date != '') {
            $title.='Issued from ' . $from_date;
            $where_clause.=" issued_on >= " . date_to_int_string($from_date) . " and ";
        }

        if ($to_date != '') {
            $title.=' to ' . $to_date;
            $where_clause.=" issued_on <= " . date_to_int_string($to_date) . " and ";
        }

        $where_clause.=" id_domain=" . $this->ion_auth->get_id_domain() . " and ";

        $where_clause = ($where_clause != '') ? remove_last_word($where_clause) : $where_clause;
        $sql = "select * from " . Modules::run('bank_transaction/get_table');
        $sql.=($where_clause != '') ? " where $where_clause " : "";
        $sql.=' order by issued_on ';

        $bank_transactions = Modules::run('bank_transaction/_custom_query', $sql)->result();


        $style_title = array(
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'size' => 12
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );

        $this->excel->setActiveSheetIndex(0);

//name the worksheet
        $this->excel->getActiveSheet()->setTitle('bank transaction report');

        $this->excel->getActiveSheet()->setCellValue('B2', $this->get_domain());
        $this->excel->getActiveSheet()->getStyle('B2')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('B2:J2');
        $this->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);

        $this->excel->getActiveSheet()->setCellValue('B3', 'BANK TRANSACTION REPORT' . strtoupper($title));
        $this->excel->getActiveSheet()->getStyle('B3')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('B3:J3');
        $this->excel->getActiveSheet()->getRowDimension(3)->setRowHeight(20);

        $this->excel->getActiveSheet()->setCellValue('B4', strtoupper($title_2));
        $this->excel->getActiveSheet()->getStyle('B4')->applyFromArray($style_title);
        $this->excel->getActiveSheet()->mergeCells('B4:J4');
        $this->excel->getActiveSheet()->getRowDimension(4)->setRowHeight(20);


// style used in formating border of the cell
        $default_border = array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '1006A3'));

        $set_borders = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'font' => array(
                'name' => 'Arial',
                'italic' => false,
                'size' => 10,)
        );

        $style_header = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'E1E0F7'),
            ),
            'font' => array(
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'size' => 11,));


        $style_shade = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => 'DFD7CF'),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
        );

        $number = array(
            'borders' => array(
                'bottom' => $default_border,
                'left' => $default_border,
                'top' => $default_border,
                'right' => $default_border,
            ),
            'font' => array(
                'name' => 'Arial',
                'italic' => false,
                'size' => 10,),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            ),
        );

        $sheet = $this->excel->getActiveSheet();
        $col = "A";
        $rows = 7;
        $sheet->setCellValue($col . $rows, 'No');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(8);
        $col++;

        if ($id_transaction_type == '') {
            $sheet->setCellValue($col . $rows, 'Type');
            $sheet->getStyle($col . $rows)->applyFromArray($style_header);
            $sheet->getColumnDimension($col)->setWidth(12);
            $col++;
        }
        $sheet->setCellValue($col . $rows, 'Date');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;
        $sheet->setCellValue($col . $rows, 'Particular');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(25);
        $col++;

        $sheet->setCellValue($col . $rows, 'Bank');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;

        $sheet->setCellValue($col . $rows, 'Description');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(17);
        $col++;

        $sheet->setCellValue($col . $rows, 'Cheque');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;

        $sheet->setCellValue($col . $rows, 'Debit');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;



        $sheet->setCellValue($col . $rows, 'Credit');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;



        $sheet->setCellValue($col . $rows, 'Balance');
        $sheet->getStyle($col . $rows)->applyFromArray($style_header);
        $sheet->getColumnDimension($col)->setWidth(10);
        $col++;


        $rows++;
        $i = 1;
        $balance = 0;
        $total_debit = 0;
        $total_credit = 0;

        foreach ($bank_transactions as $bank_transaction) {
            if ($bank_transaction->id_transaction_type == 1) {
                $total_debit+=$bank_transaction->amount;
                $balance+=$bank_transaction->amount;
            } else {
                $total_credit+=$bank_transaction->amount;
                $balance-=$bank_transaction->amount;
            }


            $col = "A";

            $sheet->setCellValue($col . $rows, $i++);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            if ($id_transaction_type == '') {
                $sheet->setCellValue($col . $rows, Modules::run('transaction_type/get_where', $bank_transaction->id_transaction_type)->row()->type);
                $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
                $col++;
            }

            $sheet->setCellValue($col . $rows, format_date('', $bank_transaction->issued_on));
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;


            $sheet->setCellValue($col . $rows, $bank_transaction->particular);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;


            $sheet->setCellValue($col . $rows, Modules::run('bank_account/get_where_custom', array('id_bank_account' => $bank_transaction->id_bank_account, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->bank_name);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $bank_transaction->comment);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, $bank_transaction->cheque_number);
            $sheet->getStyle($col . $rows)->applyFromArray($set_borders);
            $col++;

            $sheet->setCellValue($col . $rows, ($bank_transaction->id_transaction_type == 1) ? $bank_transaction->amount : '');
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;



            $sheet->setCellValue($col . $rows, ($bank_transaction->id_transaction_type == 1) ? '' : $bank_transaction->amount);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;


            $sheet->setCellValue($col . $rows, $balance);
            $sheet->getStyle($col . $rows)->applyFromArray($number);
            $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
            $col++;

            $rows++;
        }

        $col = "A";
        $sheet->setCellValue($col . $rows, 'Grand Total:');
        $col1 = $col;
        $col++;
        $col++;
        $col++;
        $col++;
        $col++;
        if ($id_transaction_type == '') {

            $col++;
        }

        $sheet->mergeCells($col1 . $rows . ':' . $col . $rows);
        $sheet->getStyle($col1 . $rows)->applyFromArray($style_shade);
        $col++;

        $sheet->setCellValue($col . $rows, ($total_debit > 0) ? $total_debit : '');
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;


        $sheet->setCellValue($col . $rows, ($total_credit > 0) ? $total_credit : '');
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;



        $sheet->setCellValue($col . $rows, $balance);
        $sheet->getStyle($col . $rows)->applyFromArray($style_shade);
        $sheet->getStyle($col . $rows)->getNumberFormat()->setFormatCode('#,##0');
        $col++;


        $filename = 'premium_collection_report.xls'; //save our workbook as this file name
        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache
//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
//if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
//force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');
    }

//print bank transaction pdf
    function print_bank_transaction_pdf() {
        if (!$this->ion_auth->logged_in()) {
// redirect them to the login page
            redirect('site/login', 'refresh');
        } elseif (!$this->check_access()) {
            $this->show_error();
        }

        define('SMARTWEB_PAGE', 'L');
        $this->load->library('pdf');

// set document information
        $this->pdf->SetSubject('');
        $this->pdf->SetKeywords('');

// set font
        $this->pdf->SetFont('times', '', 8.5);

// add a page
        $this->pdf->AddPage();

// footer margin
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $where_clause = '';
        $title = ' ';
        $title_2 = ' ';
        $colspan = 7;

        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $id_transaction_type = $this->input->post('transaction_type');
        $id_bank_account = $this->input->post('bank_account');

        if ($id_transaction_type != '') {
            $title.= Modules::run('transaction_type/get_where', $id_transaction_type)->row()->type . ', ';
            $where_clause.=" id_transaction_type=$id_transaction_type and ";
        }

        if ($id_bank_account != '') {
            $bank_account_row = Modules::run('bank_account/get_where_custom', array('id_bank_account' => $id_bank_account, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
            $title.= ' from ' . $bank_account_row->bank_name . ' [' . $bank_account_row->description . '], ';
            $where_clause.=" id_bank_account=$id_bank_account and ";
        }

        if ($from_date != '') {
            $title.='Issued from ' . $from_date;
            $where_clause.=" issued_on >= " . date_to_int_string($from_date) . " and ";
        }

        if ($to_date != '') {
            $title.=' to ' . $to_date;
            $where_clause.=" issued_on <= " . date_to_int_string($to_date) . " and ";
        }

        $where_clause.=" id_domain=" . $this->ion_auth->get_id_domain() . " and ";

        $where_clause = ($where_clause != '') ? remove_last_word($where_clause) : $where_clause;
        $sql = "select * from " . Modules::run('bank_transaction/get_table');
        $sql.=($where_clause != '') ? " where $where_clause " : "";
        $sql.=' order by issued_on ';

        $bank_transactions = Modules::run('bank_transaction/_custom_query', $sql)->result();

//////////////////////////////////////////////////////////////////////////
        $html = '<link rel="stylesheet" type="text/css" href="' . base_url() . 'media/css/style.css">';

        $html.='<style type="text/css">

.claim_table{
border-top:1px solid #4B4B4B;
border-left:1px solid #4B4B4B;
border-bottom:1px solid #4B4B4B;
}

.claim_table tr td{
border-top:1px solid #4B4B4B;
border-right:1px solid #4B4B4B;
padding:3px 0px 3px 2px;
}

.claim_table tr.header_report td{
background-color:lightblue;
padding:3px 0px 3px 2px;
border-top:0px solid #4B4B4B;
}
</style>

<h2 style="padding:0px;margin:0px;"> BANK TRANSACTION REPORT ' . strtoupper($title) . '</h2>';
        $html.=($title_2 <> '') ? '<h2 style="padding:0px;margin:0px;">' . strtoupper(trim($title_2, '')) . '</h2>' : '';

        $html.='<div style="margin:20px; width:900px;">
<div>
<table class="claim_table" cellpadding="0" cellspacing="0">
<tr class="header_report">
<td width="80"> No </td>';
        $html.=($id_transaction_type == '') ? '<td width="200" align="center"> Type </td>' : '';
        $html.='<td width="200" align="center"> Date </td>
<td width="600" align="center">Particular </td>
<td width="200" align="center">Bank </td>
<td width="600" align="center">Description </td>
<td width="200" align="center">Cheque </td>
<td width="250" align="center">Debit</td>
<td width="250" align="center">Credit</td>
<td width="250" align="center">Balance</td></tr>';
        $i = 1;
        $balance = 0;
        $total_debit = 0;
        $total_credit = 0;

        foreach ($bank_transactions as $bank_transaction) {
            if ($bank_transaction->id_transaction_type == 1) {
                $total_debit+=$bank_transaction->amount;
                $balance+=$bank_transaction->amount;
            } else {
                $total_credit+=$bank_transaction->amount;
                $balance-=$bank_transaction->amount;
            }

            $html.='<tr>
<td align="center">' . $i++ . '</td>';
            $html.= ($id_transaction_type == '') ? '<td align="center">' . Modules::run('transaction_type/get_where', $bank_transaction->id_transaction_type)->row()->type . '</td> ' : '';
            $html.='<td align="center">' . format_date('', $bank_transaction->issued_on) . '</td>
<td align="center"> ' . $bank_transaction->particular . ' </td>
<td align="center"> ' . Modules::run('bank_account/get_where_custom', array('id_bank_account' => $bank_transaction->id_bank_account, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->bank_name . '</td><td align="center"> ' . $bank_transaction->comment . '</td>
<td align="center"> ' . $bank_transaction->cheque_number . ' </td><td align="center">';
            $html.=($bank_transaction->id_transaction_type == 1) ? number_format($bank_transaction->amount) : '';
            $html.='</td><td align="center"> ';
            $html.=($bank_transaction->id_transaction_type == 1) ? '' : number_format($bank_transaction->amount);
            $html.='</td><td align="center"> ' . number_format($balance) . ' </td> </tr>';
        }
        $html.='
            <tr>
<td colspan="' . $colspan . '" align="right" style="background-color:#C9C9C9;">Grand Total:</td>
<td align="center" style="background-color:#C9C9C9;">' . number_format($total_debit) . '</td>
<td align="center" style="background-color:#C9C9C9;">' . number_format($total_credit) . '</td>
<td align="center" style="background-color:#C9C9C9;">' . number_format($balance) . '</td>';
        $html.='</tr></table>
</div>
</div>';
/////////////////////////////////////////////////////////////////////
// print a line using Cell()
        $this->pdf->writeHTML($html);

        $this->pdf->Output('bank_transaction_report.pdf', 'I');
    }

    public function set_domain($domain) {
        self::$domain_name = strtoupper(Modules::run('domain/get_where', $domain)->row()->name);
    }

    public function get_domain() {
        return self::$domain_name;
    }

}
