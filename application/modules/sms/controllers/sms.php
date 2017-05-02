<?php  (! defined('BASEPATH')) and exit('No direct script access allowed');

class Sms extends MX_Controller
{
   protected $_url='https://bulksms.vsms.net/eapi/submission/send_sms/2/2.0';
   protected $_username='lacksinho';
   protected $_password='g80j85e93i94';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('sms_helper');

    }

    public function index()
    {
    }

    public function notify($id_insurance){

          if (!$this->ion_auth->logged_in()) {
              // redirect them to the login page
              redirect('site/login', 'refresh');
          } elseif (!$this->check_access()) {
              $this->show_error();
          } else {
              $id_insurance = (int) $id_insurance;
              $data['id_insurance'] = $id_insurance;
              $data['id_domain']=$this->ion_auth->get_id_domain();
              $insurance_count = Modules::run('insurance/count_where', $data);
              if (!$insurance_count) {
                  $this->session->set_flashdata('message', '<h6 class="box-title text-aqua" >No such records found</h6>');
                  redirect('insurance');
              }

              $insurance=Modules::run('insurance/get_where',$id_insurance)->row();
              $property=Modules::run('property/get_where',$insurance->id_property)->row();
              $customer=Modules::run('customer/get_where',$property->id_customer)->row();

              $this->form_validation->set_rules('phone', 'Phone Number', 'required|trim|xss_clean');
              $this->form_validation->set_rules('ujumbe', 'Message', 'required|trim|xss_clean');
              if ($this->form_validation->run() == TRUE) {
                 $id_insurance = $id_insurance;
                 $phone= $this->input->post('phone', TRUE);
                 $ujumbe=$this->input->post('ujumbe', TRUE);

                  // Sending 7-bit message
                  $post_body = seven_bit_sms( $this->_username, $this->_password, $ujumbe, $phone );
                  $result = send_message( $post_body, $this->_url );
                  //$message=print_ln( formatted_server_response( $result ) );
                  if( $result['success'] ) {
                  //$this->session->set_flashdata('message', '<h6 class="box-title text-green" >Message successfully sent</h6>');
                  }
                  else {
                 // $this->session->set_flashdata('message', '<h6 class="box-title text-red" >Message unsuccessfully sent</h6>');
                  }
                  redirect('insurance');
              }

              // display registration form

              $phone=($customer->phone<>'') ? format_phone($customer->phone) : '';
              $data['phone'] = array(
                  'name' => 'phone',
                  'value' => $this->form_validation->set_value('phone',$phone)
              );
              $today=date_to_int_string(date('d/m/Y'));
              $expired_on=$insurance->expired_on;
              $ujumbe="Ndugu mteja, tukukumbusha kuwa bima ya gari namba $property->number $property->description ";
              $ujumbe.=($expired_on<$today) ? "imekwisha " : "itakwisha ";
              $premium=number_format($insurance->premium);
              $vat=number_format($insurance->vat);
              $total=number_format($insurance->premium+$insurance->vat);
              $ujumbe.=format_date('',$expired_on)." Premium itakuwa ni $premium/= Vat 18% ni $vat/= Jumla ni $total/=.Tunasubiri maelekezo yako au unaweza wasiliana kwa namba +255655268495 kwa maelezo zaidi.Thorn Limited";
              $data['ujumbe'] = array(
                  'name' => 'ujumbe',
                  'value' => $this->form_validation->set_value('ujumbe',$ujumbe),
              );
              // set the flash data error message if there is one
              $data['message'] = validation_errors() ? validation_errors() : $this->session->flashdata('message');
              $data['module'] = 'sms';
              $data['view_file'] = 'form';
              $data['insurance_active'] = 'active';
              $data['insurance_information_active'] = 'active';
              $data['heading'] = 'Insurance';
              $data['subheading'] = 'Send message to customer';
              echo Modules::run('template/login_area', $data);
          }

    }
}
/* End of file example.php */
/* Location: ./application/controllers/example.php */
