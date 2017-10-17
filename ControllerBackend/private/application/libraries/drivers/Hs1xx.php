<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'libraries/Device.php'); // include the abstract class file

class Hs1xx extends Device {
	/**
	 * Hs1xx - a driver library for TP-LINK devices
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
            'off'       => '{"system":{"set_relay_state":{"state":0}}}',
            'on'      => '{"system":{"set_relay_state":{"state":1}}}',
            'ledoff'   => '{"system":{"set_led_off":{"off":1}}}',
            'ledon'    => '{"system":{"set_led_off":{"off":0}}}',
            'info'     => '{"system":{"get_sysinfo":{}}}',
            // 'cloudinfo'=> '{"cnCloud":{"get_info":{}}}',
            // 'wlanscan' => '{"netif":{"get_scaninfo":{"refresh":0}}}',
            // 'time'     => '{"time":{"get_time":{}}}',
            'schedule' => '{"schedule":{"get_rules":{}}}',
            'countdown'=> '{"count_down":{"get_rules":{}}}',
            // 'antitheft'=> '{"anti_theft":{"get_rules":{}}}',
            // 'reboot'   => '{"system":{"reboot":{"delay":1}}}',
            // 'reset'    => '{"system":{"reset":{"delay":1}}}',
            // 'setcloud' => '{"cnCloud":{"set_server_url":{"server":"devs.teamtechuk.com"}}}',
            'udpbc'    => '{"system":{"get_sysinfo":{}}}' //udp broadcast packet
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
        	'driver_id' => 'Hs1xx',
        	'updated' => time()
        	);

        $this->sysinfo['params'] = $this->sendCommands($this->commands['info'])['system']['get_sysinfo'];

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
    	// print_r($params);
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
	 * Intefaces with TP-LINK HS1xx devices using a TCP connection
	 * on port 9999.
	 * $dev_cmds should contain required actions of device, device identifier and address
	 */
	private function sendCommands($command)
	{
	    $error = false;
	    $fp = @fsockopen($this->sysinfo['ip_address'], $this->port, $errno, $errstr, 3);

	    // $rc = @fsockopen(...);
	    if (is_resource($fp))
	    {
	        // Call the encrypt method on the device driver library
	        $out = $this->encrypt($command);  // Object instances will always be lower case
	        
	        fwrite($fp, $out);

	        $buffer = "";
	        while (!feof($fp)) {
	            $buffer = fgets($fp,1024);
	        }

	        fclose($fp);

	        // decrypt the buffer
	        $buffer = $this->decrypt($buffer);

	        // trim the useless data (first 5 chars)
	        $buffer = substr($buffer, 5);

	        // append an opening curly brace to make a valid json string
	        $buffer = "{".$buffer;

	        // decode the json string into a json object/assoc array
	        $response = json_decode($buffer, TRUE);
	        $response['error'] = 0;
	        $response['status_code'] = 200;
	    }else{
	    	$error = true;
	    	$response = array("error" => true, "status_code" => 500, "message" => "Could not communicate with device");
	    }

	    // $response['error'] = 0;

	    // print();

	    if($response['system']['set_relay_state']['err_code'] == 0){
	    	$response['parameters']['power'] = json_decode($command)->system->set_relay_state->state;
	    }

	    // print_r($response);
	    return $response;
	}

	private function encrypt($string)
	{
		$key = 171;
		$result = "\0\0\0\0";

		for($i=0; $i<strlen($string); $i++){
			// XOR $key with decimal value of $string[$i]
			$a = $key ^ ord($string[$i]);
			$key = $a;
			$result = $result.chr($a);
		}

		return $result;
	}

	private function decrypt($string){
	    $key = 171;
	    $result = "";
	    for($i=0; $i<strlen($string); $i++){
	        $a = $key ^ ord($string[$i]);
	        $key = ord($string[$i]);
	        $result = $result.chr($a);
	    }
	    return $result;
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

	protected function scanDevices($bc_addr='172.24.1.255', $bc_port=9999)
	{        
        $port = 9999;
        $str = $this->encrypt($this->commands['udpbc']);

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
        socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
        socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>2, "usec"=>0));
        socket_sendto($sock, $str, strlen($str), 0, $bc_addr, $port);

        $i = 0;
        $clients = array();
        while(true) {
                $ret = @socket_recvfrom($sock, $buf,2048, 0, $ip, $port);
                if($ret === false) break;
                $clients[$i]['data'] = json_decode(decrypt($buf),TRUE);
                $clients[$i]['ip'] = $ip;
                $i++;
        }

        socket_close($sock);
        
        return $clients;
	}
}