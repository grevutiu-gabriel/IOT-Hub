<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'libraries/Device.php'); // include the abstract class file

class Wemo extends Device {
	/**
	 * Wemo - a driver library for Belkin devices
	 * extends Device class
	 *
	 * Compatible with the following devices
	 * 		Belkin WeMo Switch
	 * 		Belking WeMo Insight Switch
	 * Acts as gateway between web app and Belkin Smart Plugs
	 */

	protected $sysinfo;
	protected $port = 49153;

	// Commands/States
	var $commands =
		[
            'off'	=> '<BinaryState>0</BinaryState>',
            'on'	=> '<BinaryState>1</BinaryState>'
        ];

    var $parameters = array
    (
    	'power' 		=> 0,
    	'active_mode'	=> 'schedule',
    	'timer_set' 	=> 0,
    	'timer_remain' 	=> 0
    );

    public function __construct($data) {
		// Call the CI_Controller constructor
		// parent::__construct();

		// Array which holds data about a device
        $this->sysinfo = array(
        	'device_id' => $data['id'],
        	'ip_address' => $this->get_device_ip($data['id']),
        	'driver_id' => 'wemo',
        	'updated' => time()
        	);

        // $this->sysinfo['params'] = $this->sendCommands($this->commands['info'])['system']['get_sysinfo'];

		// print_r($this->sysinfo);
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
        	default:
        		$command = $this->commands['info'];
        		break;
        }

    	return $this->sendCommands($command);
    }

	/**
	 * Method send_command($dev_cmds)
	 *
	 * Intefaces with Belkin WeMo devices using a TCP connection
	 * on port 9999.
	 * $command should contain index to commands array of object
	 */
	private function sendCommands($command)
	{
	    $error = false;

	    $response = $this->soapRequest($command);

	    // print_r($response);

	    // if nothing in the response then return an error
	    if(!$response){
	    	$response = array("error" => true, "status_code" => 500, "message" => "Could not communicate with device");
	    	return $response;
	    }

	    // Clean the SOAP formatting from the xml response
	    $clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:', 's:', 'u:'], '', $response);

	    // Parse the devices XML response using PHP's simple xml
	    $xml = simplexml_load_string($clean_xml);

	    // print_r($response);
	    // print_r($xml->Body->SetBinaryStateResponse->BinaryState);

	    // Check the outcome of the Binary State Response
	    if(!$xml->Body->SetBinaryStateResponse->BinaryState){
	    	$response = array("error" => true, "status_code" => 501, "message" => "Error setting device state");
	    	return $response;
	    }

	    $action = simplexml_load_string($command);

	    // print_r();
	    $response = array();

	    // if($response['system']['set_relay_state']['err_code'] == 0){
	    	$response['parameters']['power'] = (string)$action[0];
	    // }

	    $response['error'] = 0;

	    // return the response (success if its made it this far!)
	    return $response;
	}

	private function soapRequest($binary_state)
	{
		// URL to the WeMo device - appended by the port number and rest of control string
        $deviceUrl = $this->sysinfo['ip_address'].':'.$this->port.'/upnp/control/basicevent1';

        $xmlPostData = '<?xml version="1.0" encoding="utf-8"?>
                            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                              <s:Body>
                                <u:SetBinaryState xmlns:u="urn:Belkin:service:basicevent:1">
                                  '. $binary_state .'
                                  <Duration></Duration>
                                  <EndAction></EndAction>
                                  <UDN></UDN>
                                </u:SetBinaryState>
                              </s:Body>
                            </s:Envelope>';

		$headers = array(
			"User-Agent: CyberGarage-HTTP/1.0",
			"Accept: text/xml",
			"Content-type: text/xml;charset=\"utf-8\"",
			"SOAPACTION: \"urn:Belkin:service:basicevent:1#SetBinaryState\"", 
			"Content-length: ".strlen($xmlPostData),
			"Cache-Control: no-cache",
			"Pragma: no-cache",
		);

		$url = $deviceUrl;

		// Initialize PHP cURL connection
        $ch = curl_init();
        
        // If connecting using SSL then ignore certificate warnings
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // Insert the URL to be requested
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set timeout of 5 seconds (things should happen FAST on LAN)
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        // Tell cURL library this is a POST request
        curl_setopt($ch, CURLOPT_POST, true);

        // Insert the POST data in XML format
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlPostData);

        // Set the request headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Get the response to the request
        $response = curl_exec($ch); 

        // Close the cURL connection
        curl_close($ch);

        return $response;
	}

	private function get_device_ip($deviceID)
	{
    	$CI =& get_instance();

		// Load the device model
		$CI->load->model('device_model');

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