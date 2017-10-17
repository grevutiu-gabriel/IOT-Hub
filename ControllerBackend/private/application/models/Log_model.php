<?php

class Log_model extends CI_Model {
    public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();

        $this->load->database();
    }

    public function InsertLog($data=array())
    {
        $this->db->insert('tLog', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
}