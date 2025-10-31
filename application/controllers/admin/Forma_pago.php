<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Forma_pago extends AdminController
{
    function __construct()
    {
        parent::__construct();

        $this->load->model('forma_pago_model');
    }

    public function add_forma_pago()
    {
        if (!is_admin()) {
            access_denied('FormaPago');
        }

        if ($this->input->is_ajax_request()) {

            $data = $this->input->post();
            if ($data['id'] == '') {
                $id = $this->forma_pago_model->add_forma_pago($data);
                $message = $id ? _l('added_successfully', "FormaPago") : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id' => $id,
                    'description' => $data['description'],
                ]);
            }
        }
    }

    function index()
    {
        echo "test";
    }
}