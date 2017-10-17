<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authentication Class
 *
 * Responsible for Authenticating users against user records in the database
 *
 * @package     IOTHub
 * @subpackage  Libraries
 * @author      Jamie Dixon
 * @link        http://speedx.plus.com
 */
class Authentication {

    var $ci;
    
    function __construct() {
        $this->ci = &get_instance();
        $this->ci->load->model('log_model');
    }


    /**
     * Authenticate
     *
     * Calls to authenticate with valid user credentials
     * will return user record from the database
     *
     * @param string|string[] $user
     *
     * @return  string[] $loginResponse
     *
     * @author      Jamie Dixon
     */
    public function authenticate($user)
    {
        // Get the username/pass parameters from the $user array
        $username = $user['user'];
        $pass = $user['pass'];

        // Load the login/user model
        $this->ci->load->model('user_model');

        // Populate $UserLogin with SQL query results
        // Will return the user for inputted username
        // regardless of whether password is correct or not
        $UserLogin = $this->ci->user_model->userLogin($username);

        // If there was no row
        if(!$UserLogin){
            die("Username not found.");
        }

        // Load the PasswordHash library
        // http://www.openwall.com/phpass/
        $this->ci->load->library('PasswordHash');

        # Try to use stronger but system-specific hashes, with a possible fallback to
        # the weaker portable hashes.
        $t_hasher = new PasswordHash(8, FALSE);

        // hash the inputted password
        $hash = $UserLogin[0]->Password;

        // Compare the inputted hashed password with the stored hash password
        $check = $t_hasher->CheckPassword($pass, $hash);
        $inetAddr = ip2long($_SERVER['REMOTE_ADDR']);


        if ($check){
            // The check succeeded
            $login_response = array(
                'error' => false,
                'success' => true,
                'status_code' => 200
            );
            
            // Prepare user array for session storage
            $userdata = array(
                'UID' => $UserLogin[0]->ID,
                'Username'  => $UserLogin[0]->Username,
                'Email'     => $UserLogin[0]->Email,
                'LastActive'  => time(),
                'LoggedIn' => TRUE
            );

            // Update the last active time for user
            $this->ci->user_model->updateUserLogin($UserLogin[0]->ID);

            // Push the userdata array to the session
            $this->ci->session->set_userdata($userdata);

            // Prepare logData array
            $logData = array(
                'UID' => $UserLogin[0]->ID,
                'Code' => 200,
                'Message' => 'Login Success',
                'Timestamp' => time(),
                'IP' => $inetAddr
            );

        }else{
            // Destroy any current session
            $this->ci->session->sess_destroy();

            // Prepare the response array (failed)
            $login_response = array (
                'error' => true,
                'success' => false,
                'status_code' => 400
            );

            // Prepare the logData array
            $logData = array(
                'Code' => 400,
                'Message' => 'Invalid Login',
                'Timestamp' => time(),
                'IP' => $inetAddr
            );
        }

        // Log the login attempt
        $this->ci->log_model->InsertLog($logData);

        // Prepare a data array
        $data = array("result" => $login_response);

        // Return the login response
        return $login_response;
    }
}