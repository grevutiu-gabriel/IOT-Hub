<?php

class User_model extends CI_Model {
    public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();

        $this->load->database();
    }

    public function UserLogin($username)
    {
        $this->db->select('*');
        $this->db->from('tUser');
        $this->db->where('Username', $username);

        $query = $this->db->get();
        $user_result = $query->result();

        if($query->num_rows() <= 0){
            return false;
        }else{
            return $user_result;
        }
    }

    function UpdateUserLogin($uid)
    {
        $this->db->set('LastLogin', time());
        $this->db->where('ID', $uid);
        $this->db->update('tUser');
    }
}