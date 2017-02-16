<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header ('Access-Control-Allow-Origin: *');

/**
 * Aero Template
 *
 * Services to finding and retrieve templates
 *
 * @package		Aero
 * @subpackage	Template
 * @category	REST Controller
 * @author		Mike Priest
 */
require APPPATH.'/libraries/REST_Controller.php';

class Template extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
    }

	/**
	 *  Template for Aero tpl files
	 */
	public function index_get()
	{
        $guest = $this->input->get('guest');
        $host = $this->input->get('host');

        if($guest != ""){
            $this->load->model('guest_model');
            $this->guest_model->login($guest, $host);
        }

        $name = urldecode($_REQUEST['name']);
		$path = "{$_SERVER['DOCUMENT_ROOT']}/assets/tpl/{$name}";
		$this->load->library('person', array('host' => $this->host));
		$is_admin = in_array($this->config->item("administrator"), $this->person->roles);

		$acl = $this->person->acl;
		$username = $this->person->username;

		if (strrpos($name, "..") === false && file_exists($path))
        {
			//Grab tpl
			ob_start();
			include_once $path;
			$tpl = ob_get_clean();

			$template = json_encode(array('tpl' => $tpl ? $tpl : ""));
            $this->response($template, 200);
		}
		else {
			$this->response("404 - file does not exist.", 404);
		}
	}
}
