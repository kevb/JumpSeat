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

class RoleMap extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();

        $this->load->model('rolemap_model', '', FALSE, $this->host);
    }

    /**
     *  GET role mapping by role
     */
    function by_role_get()
    {
    	$map = $this->rolemap_model->get_by_role($this->input->get('roleid'));
    	$this->response($map, 200);
    }

    /**
     *  GET role mapping by guide
     */
    function by_guide_get()
    {
    	$map = $this->rolemap_model->get_by_guide($this->input->get('guideid'));
    	$this->response($map, 200);
    }

    /**
     *  Get table data for admin
     */
    function tableguide_get()
    {
    	$rows["data"] = array();

    	//All guides
    	$guides = $this->rolemap_model->get_by_role($this->get('roleid'));

    	//Get user
    	$this->load->library('person', array('host' => $this->host));
    	$acl = $this->person->acl;

    	//Build rows
    	foreach($guides as $guide)
    	{
    		$id = $guide['id'];
    		$title = $guide['title'];
    		$desc = $guide['desc'];
    		$steps = sizeof($guide['step']);

    		$tools = "<div class='tools' style='width:115px'; data-id='$id'>";
    		//Permission
    		if($acl['roles']['assignGuide']){
    			$tools .= "<a class='small button alert delete'>Delete <i class='ss-icon'>&#xE0D0;</i></a>";
    		}
    		$tools .= "</div></li>";

    		$row = array(
    				"<input type='checkbox' class='select' value='1' data-id='$id' />",
    				$title,
    				$desc,
    				$steps,
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
    	$new = $this->rolemap_model->create($this->request_data['roleid'], $this->request_data['guideid']);
    	$this->response($new, 200);
    }


    /**
     *  DELETE association between guide and role
     */
    function index_delete()
    {
    	$del = $this->rolemap_model->delete($this->delete('roleid'), $this->delete('guideid'));

    	$response_code = $del ? 200 : 400;
    	$this->response($del, $response_code);
    }
}
