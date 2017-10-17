<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Abstract Class Device
 *
 * Define methods which should be implemented in any class which Extends device
 *
 * @package     IOTHub
 * @subpackage  Libraries
 * @author      Jamie Dixon
 * @link        http://speedx.plus.com
 */
abstract class Device {
    /**
     * getDeviceValues
     *
     * Gets current values for the instantiated device
     *
     *
     * @return $sysinfo[]
     *
     * @author      Jamie Dixon
     */
    abstract protected function getDeviceValues();

    /**
     * getInitialParameters
     *
     * Gets defined parameters for the particular device
     *
     *
     * @return string|string[] $parameters
     *
     * @author      Jamie Dixon
     */
    abstract protected function getInitialParameters();

    /**
     * setDeviceValues
     *
     * Sets device parameters
     *
     * @param   string|string[] $valueArray
     *
     * @return  bool    TRUE if the communication of these parameters to device was a sucess
     *
     * @author      Jamie Dixon
     */
    abstract protected function setDeviceValues($valueArray);


    abstract protected function scanDevices();

    // pushes data from device values to the physical device/thing
    abstract protected function sendCommand($params);

    // Common methods
    public function deviceValues() {
        return $this->getDeviceValues();
    }

    /**
     * Send commands to a connected device
     *
     * @category    Control
     *
     * @param   string|string[] $values
     *              Define identifier and required parameters for a device
     */
    public function controlDevice($values) {
        $this->setDeviceValues($values); // set the values
        $data['command'] = $values;
        $data['response'] = $this->sendCommand($values); // send the command to device
        $data['sysinfo'] = $this->getDeviceValues();

        if($data){
            // print_r($data);
            return $data;
        }else{
            return false;
        }
    }

    /**
     * Function updatesDevices()
     *
     * Experimental function - targeting supported TP Link Devices
     * will initiate a UDP scan for supported devices which exist
     * in the system - this can in turn be used to update the IP address
     * useful in a DHCP environment where the devices may periodically
     * obtain new network addresses.
     * 
     */
    public function updateDevices()
    {
        $this->scanDevices();
    }


    /**
     * Function installDevice()
     *
     * Experimental function - not fully implemented
     * its purpose will be to add a new device to the IoT Hub.
     * 
     */
    public function installDevice($device)
    {
        // if the device is undefined
        if(!$device){
            // return false indicating function did not complete
            return false;
        }

        // Perform some further processing here to get the device
        // parameter and add the device to the database
        $device['parameters'] = $this->getInitialParameters();

        // print_r($device);
    }
}