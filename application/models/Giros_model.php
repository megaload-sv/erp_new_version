<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Giro_model
 *
 * @author joseluisroot
 */
 #[\AllowDynamicProperties]
 
class Giros_model extends CI_Model {
    
    public function get_all_giros(){
        
        $this->db->order_by('name', 'asc');

        $data = $this->db->get('tblgiros');
        
        return $data->result_array();
       
    }
    
    public function add_giro($values){
        $data = array( 'name' => $values['name_giro']
        );

        $this->db->insert('tblgiros', $data);
        
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }
    
    public function get_giro_by_id($id){
        
        $this->db->select('name');
        $this->db->where('id', $id);
        
        $data = $this->db->get('tblgiros');
        
        return $data->result_array();
        
    }
    
}
