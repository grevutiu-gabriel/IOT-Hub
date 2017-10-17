<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Widgets extends CI_Controller {
	/**
	 * Widgets Controller
	 *
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://192.168.1.51/
	 *
	 */
	public function index($widget, $id=NULL)
	{
		$this->load->helper('url');
        
		// Check if the requested file (page) exists
		if(!file_exists(APPPATH.'views/widgets/'.$widget.'.php'))
		{
			// the requested page does not exist
			show_404();
		}

        // Load the device model
        $this->load->model('device_model');

		$data = array(
			'device_info' => $this->device_model->getDeviceInfo($id),
			'last_seen' => '12345',
			'device_name' => 'Dev Name',
			'ip_address' => '192.168.1.255',
			'icon_path' => 'icons/tablelamp.png'
		);

		$this->load->view('widgets/'.$widget, $data);
	}
}
