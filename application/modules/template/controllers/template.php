<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Template extends MX_Controller
{

function __construct() {
parent::__construct();
}

function public_area($data)
{
$this->load->view('public_area',$data);	
	}
	
function login_area($data)
{
$this->load->view('login_area',$data);	
	}


}

