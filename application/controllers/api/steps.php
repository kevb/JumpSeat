<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Aero Steps
 *
 * CRUD API services for Aero steps
 *
 * @package		Aero
 * @subpackage	Steps
 * @category	REST Controller
 * @author		Mike Priest
*/
require APPPATH.'/libraries/REST_Controller.php';

class Steps extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();

        //Set hostname
        switch ($this->request->method) {
        	case "delete":
        		$guideid = $this->delete('id');
        		break;
        	default:
        		$guideid = $this->request_data['id'];
        		break;
        }

        $this->load->model('step_model', '', FALSE, $this->host, $guideid);
    }

 	/**
     *  POST guide service call
     */
    function index_post()
    {

		$index = floor( $this->request_data['insertAt']) + 1;

		unset($this->request_data['insertAt']);

		$success = $this->step_model->insertAt($index, $this->request_data);

    	$response_code = $success ? 200 : 400;
    	$this->response($success, $response_code);
    }

    /**
     *  POST guide service call
     */
    function index_put()
    {
    	$index = $this->request_data['index'];
    	unset($this->request_data['index']);

    	$success = $this->step_model->update($index, $this->request_data);

    	$response_code = $success ? 200 : 400;
    	$this->response($success, $response_code);
    }

    /**
     *  POST guide service call
     */
    function move_put()
    {
    	$from = $this->put("from");
    	$to = $this->put("to");

    	$success = $this->step_model->moveIndex($from, $to);

		$response_code = $success ? 200 : 400;
    	$this->response($success, $response_code);
    }


    /**
     *  POST guide service call
     */
    function index_delete()
    {
    	$index = $this->delete('index');
    	$success = $this->step_model->deleteAt($index);

    	$response_code = $success ? 200 : 400;
    	$this->response($success, $response_code);
    }
}