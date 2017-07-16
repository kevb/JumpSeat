<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Aero roles
 *
 * CRUD API services for Aero roles
 *
 * @package		Aero
 * @subpackage	roles
 * @category	REST Controller
 * @author		Mike Priest
*/
require APPPATH.'/libraries/REST_Controller.php';

class RoleUserMap extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();

        $this->load->model('roleusermap_model', '', FALSE, $this->host);
    }

    /**
     *  GET role mapping by role
     */
    function by_role_get()
    {
    	$map = $this->roleusermap_model->get_by_role($this->input->get('roleid'));
    	$this->response($map, 200);
    }

    /**
     *  Get table data for admin
     */
    function table_get()
    {
    	$rows["data"] = array();

    	//All users
    	$users = $this->roleusermap_model->get_by_role($this->get('roleid'));

    	//Get user
    	$this->load->library('person', array('host' => $this->host));
    	$acl = $this->person->acl;

    	//Build rows
    	foreach($users as $user)
    	{
    		$id = $user['id'];
    		$firstname = $user['firstname'];
    		$lastname = $user['lastname'];
    		$email = $user['email'];

    		$tools = "<div class='tools' style='width:115px'; data-id='$id'>";
    		//Permission
    		if($acl['roles']['assignGuide']){
    			$tools .= "<a class='small button alert delete'>Delete <i class='ss-icon'>&#xE0D0;</i></a>";
    		}
    		$tools .= "</div></li>";

    		$row = array(
    				"<input type='checkbox' class='select' value='1' data-id='$id' />",
    				$firstname,
    				$lastname,
    				$email,
    				$tools
    		);

    		array_push($rows['data'], $row);
    	}
    	$this->response($rows, 200);
    }

 	/**
     *  POST role service call
     */
    function index_post()
    {
        $new = $this->roleusermap_model->create($this->request_data['roleid'], $this->request_data['userid']);
    	$this->response($new, 200);
    }

    /**
     *  DELETE association between user and role
     */
    function index_delete()
    {
    	$del = $this->roleusermap_model->delete($this->delete('roleid'), $this->delete('userid'));

    	$response_code = $del ? 200 : 400;
    	$this->response($del, $response_code);
    }
}
