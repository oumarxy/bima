<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Site extends MX_Controller {


	function __construct(){
		parent::__construct();
		$this->load->library('auth/ion_auth');
		$this->load->library('session');

	}


	public function index()
	{

	if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect('site/login', 'refresh');
		}

				$data['customer']= $this->active_customer();
		    $data['insurer']=$this->active_insurer();
				$data['claim']= $this->claim();
        $data['module']='site';
        $data['view_file']='site';
        $data['title']='Dashboard';
        $data['heading']='Dashboard';
        $data['subheading']='Welcome to Insurance Brokers Management System';
        echo Modules::run('template/login_area',$data);
}

public function active_customer(){

	if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect('site/login', 'refresh');
		}

	$today = date_to_int_string(date('d/m/Y'));
	$where_clause.=' insurance.closed<1 and customer.status=1 and property.id_domain='.$this->ion_auth->get_id_domain().' and insurance.id_domain='.$this->ion_auth->get_id_domain().' and customer.id_domain=' . $this->ion_auth->get_id_domain().' and insurance.expired_on > '.$today
	;

	$sql = "select distinct customer.id_customer from customer inner join property on customer.id_customer=property.id_customer
	          inner join insurance on property.id_property=insurance.id_property";
	$sql.=($where_clause <> '') ? " where $where_clause " : "";

  return  Modules::run('customer/_custom_query', $sql)->num_rows();

}

public function active_insurer(){
	if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect('site/login', 'refresh');
		}
	$today = date_to_int_string(date('d/m/Y'));
	$where_clause.=' insurance.closed<1 and insurer.status=1 and insurance.id_domain='.$this->ion_auth->get_id_domain().' and  insurer.id_domain=' . $this->ion_auth->get_id_domain().' and insurance.expired_on > '.$today
	;

	$sql = "select distinct insurer.id_insurer from insurer inner join insurance on insurer.id_insurer=insurance.id_insurer";
	$sql.=($where_clause <> '') ? " where $where_clause " : "";


  return  Modules::run('insurer/_custom_query', $sql)->num_rows();

}


public function claim(){
	if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect('site/login', 'refresh');
		}

return Modules::run('claim/count_where', array('id_domain'=>$this->ion_auth->get_id_domain()));
}

}

/* End of file site.php */
/* Location: ./application/modules/site/controllers/site.php */
