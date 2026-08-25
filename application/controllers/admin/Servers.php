<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Servers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();

        $this->load->model('V2ray_servers_model');
    }

    public function index()
    {
        $data['servers'] = $this->V2ray_servers_model->get_all();

        $this->load->view('admin/servers/index', $data);
    }
}