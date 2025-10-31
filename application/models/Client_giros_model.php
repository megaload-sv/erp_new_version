<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Client_giros_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add new customer giro
     * @param array $data $_POST data
     */
    public function add($data)
    {
        $this->db->insert(db_prefix().'giros', $data);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Customer Giro Created [ID:' . $insert_id . ', Name:' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
    * Get customer giros where customer belongs
    * @param  mixed $id customer id
    * @return array
    */
    public function get_customer_giros($id)
    {
        $this->db->where('customer_id', $id);

        return $this->db->get(db_prefix().'giros_groups')->result_array();
    }

    /**
     * Get all customer giros
     * @param  string $id
     * @return mixed
     */
    public function get_giros($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix().'giros')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix().'giros')->result_array();
    }

    /**
     * Edit customer giro
     * @param  array $data $_POST data
     * @return boolean
     */
    public function edit($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update(db_prefix().'giros', [
            'name' => $data['name'],
        ]);
        if ($this->db->affected_rows() > 0) {
            log_activity('Customer Giro Updated [ID:' . $data['id'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete customer giro
     * @param  mixed $id giro id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'giros');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('girosid', $id);
            $this->db->delete(db_prefix().'giros_groups');

            hooks()->do_action('customer_giro_deleted', $id);

            log_activity('Customer Giro Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
    * Update/sync customer giros where belongs
    * @param  mixed $id        customer id
    * @param  mixed $giros_in
    * @return boolean
    */
    public function sync_customer_giros($id, $giros_in)
    {
        if ($giros_in == false) {
            unset($giros_in);
        }
        $affectedRows    = 0;
        $customer_giros = $this->get_customer_giros($id);
        if (sizeof($customer_giros) > 0) {
            foreach ($customer_giros as $customer_giro) {
                if (isset($giros_in)) {
                    if (!in_array($customer_giro['girosid'], $giros_in)) {
                        $this->db->where('customer_id', $id);
                        $this->db->where('id', $customer_giro['id']);
                        $this->db->delete(db_prefix().'giros_groups');
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                } else {
                    $this->db->where('customer_id', $id);
                    $this->db->delete(db_prefix().'giros_groups');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if (isset($giros_in)) {
                foreach ($giros_in as $giro) {
                    $this->db->where('customer_id', $id);
                    $this->db->where('girosid', $giro);
                    $_exists = $this->db->get(db_prefix().'giros_groups')->row();
                    if (!$_exists) {
                        if (empty($giro)) {
                            continue;
                        }
                        $this->db->insert(db_prefix().'giros_groups', [
                            'customer_id' => $id,
                            'girosid'     => $giro,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            }
        } else {
            if (isset($giros_in)) {
                foreach ($giros_in as $giro) {
                    if (empty($giro)) {
                        continue;
                    }
                    $this->db->insert(db_prefix().'giros_groups', [
                        'customer_id' => $id,
                        'girosid'     => $giro,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
        }

        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }
}
