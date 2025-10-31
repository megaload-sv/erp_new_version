<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Giros
 *
 * @author joseluisroot
 */
class Giros extends AdminController
{

    function __construct()
    {
        parent::__construct();

        $this->load->model('giros_model');
    }

    public function add_giro()
    {
        if (!is_admin()) {
            access_denied('Giros');
        }

        if ($this->input->is_ajax_request()) {

            $data = $this->input->post();
            if ($data['id'] == '') {
                $id = $this->giros_model->add_giro($data);
                $message = $id ? _l('added_successfully', "Giro") : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id' => $id,
                    'name' => $data['name_giro'],
                ]);
            }
        }
    }

    public function test()
    {
        echo "entro";
    }

}
