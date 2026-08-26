<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->model('Vless_users_model');
    }

    public function index()
    {
        $data['users'] = $this->Vless_users_model->get_all();

        $this->load->view('admin/users/index', $data);
    }
}