<?php

class Device_model extends CI_Model {
    public function __construct()
    {
            // Call the CI_Model constructor
            parent::__construct();

            $this->load->database();
    }

    public function InstallNewDevice($data){
        $this->db->insert();
    }

    public function GetAvailableDevices($data=array())
    {
        $this->db->select('tDevice.*, tIcon.Path, tDeviceParameter.Value AS relay_state, tCategory.Name AS TypeName');
        $this->db->from('tDevice');
        $this->db->join('tIcon', 'tIcon.ID = tDevice.icon');
        $this->db->join('tModel', 'tModel.ID = tDevice.Model');
        $this->db->join('tCategory', 'tCategory.ID = tModel.Category');
        $this->db->join('tDeviceParameter', 'tDeviceParameter.DeviceID = tDevice.ID');
        $this->db->where('tDeviceParameter.ID', 1);
        $this->db->where('tDevice.Status', 1);
        $this->db->order_by('Name ASC');

        $query = $this->db->get();
        $result_set = $query->result_array();

        foreach ($result_set as $key => $value) {
            $this->db->select('tDeviceParameter.Key, tDeviceParameter.Value');
            $this->db->from('tDeviceParameter');
            $this->db->where('DeviceID', $value['ID']);
            
            $query = $this->db->get();
            $result_set[$key]['parameters'] = $query->result_array();
        }

        return $result_set;
    }

    public function GetAvailableRooms($room_id=NULL)
    {
        $this->db->select('tRoom.*, COUNT(tDevice.id) as DeviceCount, tIcon.Path')
            ->from('tRoom')
            ->order_by('tRoom.ID', 'desc');

        $this->db->join('tDevice', 'tRoom.ID = tDevice.Room')
            ->group_by('tRoom.ID');

        $this->db->join('tIcon', 'tIcon.ID = tRoom.icon');
            
        $this->db->where('tDevice.Status', 1);

        // If a room id is set then limit results to that room
        if($room_id){
            $this->db->where('tRoom.ID', $room_id);
            // echo $room_id;
        }

        $query = $this->db->get();
        $result_set = $query->result_array();

        return $result_set;
    }

    public function GetAvailableDevicesByRoom($room_id)
    {
        $this->db->select('tDevice.*, tIcon.Path, tDeviceParameter.Value AS relay_state, tCategory.Name AS TypeName');
        $this->db->from('tDevice');
        $this->db->join('tIcon', 'tIcon.ID = tDevice.icon');
        $this->db->join('tModel', 'tModel.ID = tDevice.Model');
        $this->db->join('tCategory', 'tCategory.ID = tModel.Category');
        $this->db->join('tDeviceParameter', 'tDeviceParameter.DeviceID = tDevice.ID');
        $this->db->where('tDeviceParameter.ID', 1);
        $this->db->where('tDevice.Status', 1);
        $this->db->where('tDevice.Room', $room_id);
        $this->db->order_by('Name ASC');

        $query = $this->db->get();
        $result_set = $query->result_array();

        foreach ($result_set as $key => $value) {
            $this->db->select('tDeviceParameter.Key, tDeviceParameter.Value');
            $this->db->from('tDeviceParameter');
            $this->db->where('DeviceID', $value['ID']);
            
            $query = $this->db->get();
            $result_set[$key]['parameters'] = $query->result_array();
        }

        return $result_set;
    }

    public function GetDeviceInfo($deviceID)
    {
        // print_r($deviceID);
        $this->db->select('tDevice.*, tIcon.path, tManufacturer.Name AS manf_name, tCategory.Name AS TypeName');
        $this->db->from('tDevice');
        $this->db->join('tIcon', 'tIcon.ID = tDevice.icon');
        $this->db->join('tModel', 'tModel.ID = tDevice.Model');
        $this->db->join('tCategory', 'tCategory.ID = tModel.Category');
        $this->db->join('tManufacturer', 'tManufacturer.ID = tModel.Manufacturer');
        $this->db->where('tDevice.ID', $deviceID);

        $query = $this->db->get();
        $result_set = $query->result_array();

        foreach ($result_set as $key => $value) {
            $this->db->select('tDeviceParameter.Key, tDeviceParameter.Value');
            $this->db->from('tDeviceParameter');
            $this->db->where('DeviceID', $deviceID);
            
            $query = $this->db->get();
            $result_set[$key]['parameters'] = $query->result_array();
        }

        return $result_set;
    }

    public function UpdateDevice($data=array())
    {
        // print_r($data);
        // Set the value attribute in the tDeviceParameter table
        // print_r($data);
        // print($data['command']['device_commands']['power']['state']);
        if(isset($data['command']['device_commands']['power']['state'])){
            // print("power state change");
            $this->db->set('Value', $data['command']['device_commands']['power']['state']);

            // Where clause
            $this->db->where('DeviceID', $data['command']['id']);
            $this->db->where('ID', 1); // power parameter identifier (for now)

            // Update the table
            $this->db->update('tDeviceParameter');
        }

        // Loop over the response parameters and store them in the database tDeviceParameter table
        foreach ($data['response']['parameters'] as $key => $value) {
            // print_r($key);
            // print_r($value);
            $this->db->set('Value', $value);
            $this->db->where('DeviceID', $data['command']['id']);
            $this->db->where('Key', $key);
            $this->db->update('tDeviceParameter');
        }

        // Get the amount of affected rows
        $param_update_result = $this->db->affected_rows();

        // print_r($result);

        // Set the last_seen attribute to the time now
        $this->db->set('LastSeen', time());

        // Where clause
        $this->db->where('ID', $data['command']['id']);

        // Update the table
        $this->db->update('tDevice');

        // Get the amount of affected rows
        $last_seen_update_result = $this->db->affected_rows();
    }

    public function GetDeviceModelsByManufacturer($data=array())
    {

    }


    public function GetDriverCode($id)
    {
            // Select the data from DB
            $this->db->select('tModel.DriverCode');
            $this->db->from('tModel');
            $this->db->join('tDevice', 'tModel.ID = tDevice.Model');
            $this->db->where('tDevice.ID', $id);
            $this->db->limit(1);

            $query = $this->db->get();
            $result = $query->result_array();

            return $result[0]['DriverCode'];
    }
}