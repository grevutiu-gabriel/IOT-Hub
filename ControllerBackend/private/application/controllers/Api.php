<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'/libraries/REST_Controller.php');

/**
 * Api Class
 *
 * Responsible for providing API functionality to IOT devices.
 * Extends REST_Controller.php https://github.com/chriskacerguis/codeigniter-restserver
 *
 * @package     IOTHub
 * @subpackage  Controllers
 * @author      Jamie Dixon
 * @link        http://speedx.plus.com
 */
class Api extends REST_Controller
{
    public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();

    }

    /**
     * Login post
     *
     * Handles POST requests to /login
     * $_POST array should contain parameters user and pass
     *
     *
     * @return  void
     *
     * @author      Jamie Dixon
     */
    function login_post()
    {
        if(!$this->post('user') || !$this->post('pass')){
            $this->response(NULL, 400);
        }

        $this->load->library('authentication');
        $array = array(
            'user' => $this->post('user'),
            'pass' => $this->post('pass')
        );
        $auth_result = $this->authentication->authenticate($array);
         
        if($auth_result['success'] == 1){
            $this->response(array('status' => 'success'));
        }else{
            $this->response(array('status' => 'failed'));
        }
    }

    /**
     * Logout get
     *
     * Handles GET requests to /logout
     *
     * @return  void
     *
     * @author      Jamie Dixon
     */
    function logout_get()
    {
        // // Authenticate user (by session)
        // // Check if a user is currently logged in
        if(!$this->session->userdata('LoggedIn')){
            die("Login Required");
        }

        $this->session->sess_destroy();
        $this->response(array('status' => 'success'));
    }

    /**
     * Device get
     *
     * Handles GET requests to /device
     * takes a device ID through the $_GET array
     * will return information about the device
     * including status and parameters.
     *
     * @return  void
     *
     * @author      Jamie Dixon
     */
    function device_get()
    {
        // // Authenticate user (by session)
        // // Check if a user is currently logged in
        if(!$this->session->userdata('LoggedIn')){
            // die("Login Required");
        }

        if(!$this->get('id')){
            $this->response(NULL, 400);
        }

        // Load the device model
        $this->load->model('device_model');
        
        // Get the required device parameters from the POST data
        $postDevice = $this->input->get('device');
        
        // Get the full device/system information
        $device = $this->device_model->getDeviceInfo( $this->get('id') );
         
        if($device){
            $this->response($device, 200); // 200 being the HTTP response code
        }else{
            $this->response(NULL, 404);
        }
    }

    /**
     * Device POST
     *
     * Handles POST requests to /device
     * takes a device ID through the $_GET array
     * and device parameters to set through the $_POST['device']
     * array - will return the an array with the command sent
     * and response received from the device.
     *
     * @return  void
     *
     * @author      Jamie Dixon
     */
    function device_post()
    {
        // // Authenticate user (by session)
        // // Check if a user is currently logged in
        if(!$this->session->userdata('LoggedIn')){
            die("Login Required");
        }

        if(!$this->get('id')){
            $this->response(NULL, 400);
        }

        // Load the device model
        $this->load->model('device_model');
        
        // Get the required device parameters from the POST data
        $postDevice = $this->post('device');
        $device_driver = $this->device_model->getDriverCode($this->get('id'));

        // Load the device driver library
        $deviceParams = array('id' => $this->get('id'), 'model' => 'HS110');
        $this->load->library('drivers/'.$device_driver, $deviceParams);

        // Send the command the the device
        $commandResult = $this->$device_driver->controlDevice($postDevice);

        // build up some response data
        // $data['jsonArray'] = array("device" => $set_devinfo);
        $data = $commandResult;

        // print_r($data);

        // Check the response is not empty
        if(!empty($commandResult['response']) && ($commandResult['response']['error'] === 0)){
            // The command was sent and received by the device
            // Update the database
            $this->device_model->updateDevice($data);
        }else{
            // print("error");
            // print_r($commandResult['response']);
        }

        // If the data array is not empty then display the response
        if($data)
        {
            $this->response($data, 200); // 200 being the HTTP response code
        }
 
        else
        {
            $this->response(NULL, 404);
        }
    }

    /**
     * Devices get
     *
     * Handles GET requests to /devices
     * optionally takes a Room identifier through the $_GET array
     *
     * Return information about all devices
     * including status and parameters.
     * If optional roomid is specified will return only information
     * about devices belonging to that room
     *
     * @return  void
     *
     * @author      Jamie Dixon
     */
    function devices_get()
    {
        // // Authenticate user (by session)
        // // Check if a user is currently logged in
        if(!$this->session->userdata('LoggedIn')){
            // die("Login Required");
        }

        // Load the device model
        $this->load->model('device_model');

        // If the optional roomid parameter is supplied
        if($this->get('roomid')){
            // Get the devices from particular room
            $deviceList = $this->device_model->getAvailableDevicesByRoom( $this->get('roomid') );
        }else{
            // Get the from all rooms
            $deviceList = $this->device_model->getAvailableDevices();
        }

        // If the deviceList is not FALSE or NULL
        if($deviceList){
            $this->response($deviceList, 200); // 200 being the HTTP response code
        }else{
            $this->response(NULL, 404);
        }
    }

    /**
     * Device update get
     *
     * Handles GET requests to /device_update
     * takes a device ID through the $_GET array
     * 
     * Experimental function - will be used to perform IP address
     * updates for devices which device scanning is supported
     *
     * @return  void
     *
     * @author      Jamie Dixon
     */
    function device_update_get()
    {
        // Only the TP link smart plugs support broadcast scanning so far
        $deviceParams = array('id' => $this->get('id'), 'model' => 'HS110');
        $this->load->library('drivers/hs1xx', $deviceParams);

        print_r($this->hs1xx->updateDevices());
    }

    /**
     * Rooms get
     *
     * Handles GET requests to /rooms
     * optionally takes $_GET field id
     * will return information about rooms in database
     * if optional parameter supplied will return information
     * about that room only
     *
     * @return  void
     *
     * @author      Jamie Dixon
     */
    function rooms_get()
    {
        // // Authenticate user (by session)
        // // Check if a user is currently logged in
        if(!$this->session->userdata('LoggedIn')){
            die("Login Required");
        }
        
        // Load the device model
        $this->load->model('device_model');

        if($this->get('id')){
            $roomList = $this->device_model->getAvailableRooms($this->get('id'));
        }else{
            $roomList = $this->device_model->getAvailableRooms();
        }

        // If the deviceList is not FALSE or NULL
        if($roomList){
            $this->response($roomList, 200); // 200 being the HTTP response code
        }else{
            $this->response(NULL, 404);
        }
    }
}
?>