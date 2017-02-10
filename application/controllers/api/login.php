<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Aero apps
 *
 * CRUD API services for Aero apps
 *
 * @package		Aero
 * @subpackage	apps
 * @category	REST Controller
 * @author		Trevor Dell
*/
require APPPATH.'/libraries/REST_Controller.php';

class Login extends REST_Controller
{
	function __construct()
	{
		// Construct our parent class
		parent::__construct();
		$this->load->model('user_model');
	}

	/**
	 *  Login
	 */
	function index_post()
	{
		$user = $this->post('username');
		$pass = $this->post('password');

		$data['success'] = $this->user_model->login($user, $pass);
		$this->response($data, 200);
	}

	/**
	 *  Logout
	 */
	function index_get()
	{
	}
}
