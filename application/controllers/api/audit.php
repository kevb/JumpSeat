<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CRUD API services for Aero audit
 *
 * @package		Aero
 * @subpackage	audit
 * @category	REST Controller
 * @author		Trevor Dell
*/
require APPPATH.'/libraries/REST_Controller.php';

class Audit extends REST_Controller
{
	function __construct()
	{
		// Construct our parent class
		parent::__construct();
		$this->load->model('audit_model', '', FALSE, $this->host);
	}

	/**
	 *  GET audit service call
	 */
	function index_get()
	{
		$entries = null;
		$id = $this->get('id');

		if (!empty($id) && is_array($id))
			$entries = $this->audit_model->get_by_ids($id);
		else
			$entries = $this->audit_model->get_by_id($id);

		$response_code = $entries ? 200 : 400;
		$this->response($entries, $response_code);
	}

	/**
	 *  POST audit service call
	 */
	function create_get()
	{

        $entry = json_decode($this->get('data'), true);
		$id = $this->audit_model->create($entry);

		$response_code = $id ? 200 : 400;
    	$this->response($id, $response_code);
	}

	/**
	 *  POST audit service call
	 */
	function update_get()
	{
		$entry = json_decode($this->get('data'), true);
        //$entry = $this->request->body;

		$success = $this->audit_model->update_by_id($entry['id'], $entry);

		// @todo fix 400 thrown
		//$response_code = $success ? 200 : 400;
    	$this->response($success, 200);
	}

	/**
	 *  Delete a guide
	 */
	function index_delete()
	{
		$success = $this->audit_model->delete_by_id($this->delete('id'));

		$response_code = $success ? 200 : 400;
    	$this->response($success, $response_code);
	}
}
