<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$this->load->view('header');

$this->load->view($module.'/'.$view_file);

$this->load->view('footer');
