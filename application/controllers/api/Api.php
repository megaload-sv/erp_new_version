<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");


class Api extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->model('User_model', 'use');
    }

    public function index_get()
    {
        echo "entro";
    }

}