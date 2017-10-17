<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pages Class
 *
 * Responsible for providing jQuery UI interface
 *
 * @package     IOTHub
 * @subpackage  Controllers
 * @author      Jamie Dixon
 * @link        http://speedx.plus.com
 */
class Pages extends CI_Controller {
    /**
     * View
     *
     * Provides access to jQuery UI interface based on $page param
     * checks a user is authenticated and if not shows the login
     * page.
     *
	 * @param	string	$page	The name of the page accessed
     *
     * @return  void
     *
     * @author      Jamie Dixon
     */
	public function view($page = 'home')
	{
		$this->load->helper('url');

		if(!$this->session->LoggedIn){ // LoggedIn is TRUE
			// die("Login Required");
			if($page != 'login'){
				redirect('login');
			}
		}

        
		// Check if the requested file (page) exists
		if(!file_exists(APPPATH.'views/pages/'.$page.'.php'))
		{
			// the requested page does not exist
			show_404();
		}

		$data['title'] = $page; // uppercase first letter

		$this->load->view('templates/header', $data);
		$this->load->view('pages/'.$page, $data);
		$this->load->view('templates/footer', $data);
	}
}
