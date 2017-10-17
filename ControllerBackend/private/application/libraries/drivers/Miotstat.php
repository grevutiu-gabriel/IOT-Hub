<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'libraries/Device.php'); // include the abstract class file

class Miotstat extends Device {
	/**
	 * Miotstat - a driver library for Jamie Dixon's 
	 * IoT Thermostat device
	 *
	 * Compatible with the following devices
	 * 		Arduino Yun Based Thermostat-Emulator
	 * Acts as gateway between web app and Thermostat
	 */

	protected $sysinfo;
	protected $port = 80;

	// Commands/States
	var $commands =
		[
            'off'       	=> '/arduino/set/0',
            'on'	       	=> '/arduino/set/100',
            'set'			=> '/arduino/set/',
            'info'	       	=> '/arduino/get/0',
        ];

    var $parameters = array
    (
    	'power' 			=> 0,
    	'setTemp'			=> 0,
    	'currentTemp' 		=> 0,
    	'currentHumidity' 	=> 0
    );

    public function __construct($data) {
		// Call the CI_Controller constructor
		// parent::__construct();

		// Array which holds data about a device
        $this->sysinfo = array(
        	'device_id' => $data['id'],
        	'ip_address' => $this->get_device_ip($data['id']),
        	'driver_id' => 'Miotstat',
        	'updated' => time()
        	);

        // $this->sysinfo['params'] = $this->sendCommands($this->commands['info'])['system']['get_sysinfo'];
    }

	/**
	 * protected function getInitialParameters()
	 * used for getting the devices default parameters
	 * mainly for setting up the parameters table in SQL
	 **/
    protected function getInitialParameters()
    {
    	// $params = $this->sendCommands($this->commands['info']);
    	// print_r($params);
    	return $this->parameters;
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
    	if($params['device_commands']['climate']['temperature']){
    		// print ("probably want to set temperature");
    		$command = $this->commands['set'].$params['device_commands']['climate']['temperature'];

    		return $this->sendCommands($command);
    	}

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
	    $data['parameters'] = $device_response['params'];
	    $data['error'] = 0;

	    // return the response (success if its made it this far!)
	    return $data;
	}

	private function httpRequest($command)
	{
		// print_r($command);
		// Get cURL resource
		$curl = curl_init();
		
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
			CURLOPT_CONNECTTIMEOUT => 2, 
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => 'http://'.$this->sysinfo['ip_address'].$command,
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