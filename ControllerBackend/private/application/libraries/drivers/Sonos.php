<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'libraries/Device.php'); // include the abstract class file

class Sonos extends Device {
	/**
	 * Sonos - a driver library for Sonos devices
	 * extends Device class
	 *
	 * Compatible with the following devices
	 * 		TP-LINK HS100 Smart Plug
	 * 		TP-LINK HS110 Smart Plug
	 * Acts as gateway between web app and TP-LINK Smart Plugs
	 */

	protected $sysinfo;
	protected $port = 9999;

	// Commands/States
	var $commands =
		[
            'off'       	=> '{ "get": "zones", "post": "NULL"}',
            'on'      		=> '{ "get": "zones", "post": "NULL"}',
            'volumeup'   	=> '{ "get": "zones", "post": "NULL"}',
            'volumedown'    => '{ "get": "zones", "post": "NULL"}',
        ];

    var $parameters = array
    (
    	'power' 		=> 0,
    	'active_mode'	=> 'schedule',
    	'timer_set' 	=> 0,
    	'timer_remain' 	=> 0
    );

    public function __construct($data) {
		// Array holding data about a device
        $this->sysinfo = array(
        	'device_id' => $data['id'],
        	'ip_address' => $this->get_device_ip($data['id']),
        	'driver_id' => 'Sonos',
        	'updated' => time()
        );
    }

	/**
	 * protected function getInitialParameters()
	 * used for getting the devices default parameters
	 * mainly for setting up the parameters table in SQL
	 **/
    protected function getInitialParameters()
    {
    	return $this->parameters;
    }

	// returns the array of model features
	protected function deviceAvailableFeatures($dev_model_name){

		return $this->model_features[$dev_model_name];
	}

	// returns true if setting the device params was successful
    protected function setDeviceValues($values) {
        return true;
    }

    // returns array object holding all relevant device parameters
    protected function getDeviceValues() {
        return $this->sysinfo;
    }

    // sends the device values to the device
    protected function sendCommand($params) {
        switch($params['device_commands']['power']['state']){
        	case 0:
        		$command = $this->commands['off'];
        		break;
        	case 1:
        		$command = $this->commands['on'];
        		break;
        }

    	return $this->sendCommands($command);
    }


	/**
	 * Method send_command($dev_cmds)
	 *
	 * Intefaces with TP-LINK HS1xx devices using a TCP connection
	 * on port 9999.
	 * $dev_cmds should contain required actions of device, device identifier and address
	 */
	private function sendCommands($command)
	{
		// print_r($command);
		// Set error the false for checking later
	    $error = false;

	    // Get the devices response to $command
	    $device_response = json_decode($this->httpRequest($command), TRUE);

	    // print_r($device_response);

	    // if nothing in the response then return an error
	    if(!$device_response){
	    	$response = array("error" => true, "status_code" => 500, "message" => "Could not communicate with device");
	    	return $response;
	    }

	    // $response = array();
	    $data['parameters'] = $device_response;
	    $data['error'] = 0;

	    // return the response (success if its made it this far!)
	    return $data;
	}

	private function httpRequest($command)
	{
		$command = json_decode($command);

		// Get cURL resource
		$curl = curl_init();
		
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => 'http://localhost:5005/'.$command->get,
		    CURLOPT_USERAGENT => 'IoT Hub v0.01'
		));
		
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		
		// Close request to clear up some resources
		curl_close($curl);

		return $resp;
		// print_r($resp);
	}

	private function get_device_ip($deviceID)
	{
    	$CI =& get_instance();

		// Load the device model
		$CI->load->model('device_model');

		// Build a JSON array for sending to the device_model
		$data['jsonArray'] = array("device" => $deviceID);

		// Call the function to get device data
		$data = $CI->device_model->GetDeviceInfo($deviceID);

		// Get the IP address
		return($data[0]['IP']);
	}

	protected function scanDevices()
	{
		return false;
	}
}