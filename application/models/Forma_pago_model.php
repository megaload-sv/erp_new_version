<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Forma_pago_model extends CI_Model
{
    public function get_all_forma_pagos()
    {

        $this->db->order_by('description', 'asc');

        $data = $this->db->get('tblforma_pagos');

        return $data->result_array();

    }

    public function add_forma_pago($values){
        $data = array( 'description' => $values['description']
        );

        $this->db->insert('tblforma_pagos', $data);

        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function get_description_forma_pago_by_id($forma_pago_id){

        $this->db->select('description')->where('id', $forma_pago_id);

        $forma_pago = $this->db->get('tblforma_pagos')->row();

        return $forma_pago->description;

    }

}