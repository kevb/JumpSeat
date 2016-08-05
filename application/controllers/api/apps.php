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

class Apps extends REST_Controller
{
	function __construct()
	{
		// Construct our parent class
		parent::__construct();
		$this->load->model('app_model');
	}

	/**
	 *  GET app service call
	 */
	function index_get()
	{
		$apps = null;
		$id = $this->get('id');
		$host = $this->get('host');

		if (!empty($id))
			$apps = $this->app_model->get_by_id($id);
		elseif (!empty($host))
			$apps = $this->app_model->get_by_host($host);
		else
			$apps = $this->app_model->get_all();

		$this->response($apps, 200);
	}

		/**
	 *  POST app service call
	 */
	function index_post()
	{
		$app = $this->request->body;
		$id = $this->app_model->create($app);

    	$this->response($id, 200);
	}

	/**
	 *  POST app service call
	 */
	function index_put()
	{
		$app = $this->request->body;

        if (empty($app['id']))
            $this->response('id required', 400);

        $success = $this->app_model->update_by_id($app['id'], $app);
        $this->response($success, empty($success) ? 400 : 200);
	}

	/**
	 *  PUT active flag
	 */
	function active_put()
	{
		$id = $this->request_data['id'];
		unset($this->request_data['id']);

		$role = $this->role_model->get_by_id($id, true);

		//Were not updating these
		unset($role["id"]);
		unset($role["title"]);
		unset($role["description"]);

		//Merge changes
		$merged = array_replace_recursive((array) $role, (array) $this->request_data);

		//Update permissions
		$new = $this->role_model->update_by_id($id, $merged);

		$response_code = $new ? 200 : 400;
		$this->response($new, $response_code);
	}

	/**
	 *  Delete a guide
	 */
	function index_delete()
	{
		$success = $this->app_model->delete_by_id($this->delete('id'));

		$response_code = $success ? 200 : 400;
    	$this->response($success, $response_code);
	}
}
