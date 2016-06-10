<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Aero Features
 *
 * CRUD API services for Aero features
 *
 * @package		Aero
 * @subpackage	Features
 * @category	REST Controller
 * @author		Mike Priest
*/
require APPPATH.'/libraries/REST_Controller.php';

class Features extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
        $this->load->model('feature_model');
    }

    /**
     *  GET feature service call
     */
    function index_get()
    {
    	$id = $this->input->get('id');
    	$select = $this->input->get('select') ? $this->input->get('select') : array();

    	if($id && is_array($id)){
    		$features = $this->feature_model->get_by_ids($id);
    	}elseif($id){
    		$features = $this->feature_model->get_by_id($id);
    	}else{
    		$features = $this->feature_model->get_all($select);
    	}

    	$this->response($features, 200);
    }


    /**
     *  Get table data for admin
     */
    function table_get()
    {
    	$rows["data"] = array();

    	//All features
    	$features = $this->feature_model->get_all();

    	//Get person
    	$this->load->library('person', array('host' => IAPP));
		$acl = $this->person->acl;
		$user = $this->person->username;

    	date_default_timezone_set($this->config->item('timezone'));

    	//Build rows
    	foreach($features as $feature)
    	{
	    	$id = $feature['id'];
	    	$title = $feature['title'];
	    	$desc = $feature['desc'];
	    	$creator = $feature['creator'];
	    	$active = $feature['active'] ? "Yes" : "No";

	    	$created = explode(" ", date('y-m-d h:i:s', $feature['created']->sec));
	    	$created = implode(' at ', $created);

    		$tools = "<div class='tools' style='width:210px' data-id='$id'>";

    		if($acl['guides']['edit'] || ($creator == $user && $acl['guides']['create'])){
    			$tools .= "<a class='small button success edit'>Edit <i class='ss-icon'>&#x270E;</i></a>";
    		}
    		if($acl['guides']['delete'] || ($creator == $user && $acl['guides']['create'])){
    			$tools .= "<a class='small button alert delete'>Delete <i class='ss-icon'>&#xE0D0;</i></a>";
    		}
    		$tools .= "</div></li>";

    		$row = array(
    				"<input type='checkbox' class='select' value='1' data-id='$id' />",
                    "<a href='features/". ($title) ."'>" . $title . "</a>",
    				$desc,
    				$active,
    				$creator . "<br/>" .$created,
    				$tools
    		);

    		array_push($rows['data'], $row);
    	}
    	$this->response($rows, 200);
    }

 	/**
     *  POST feature service call
     */
    function index_post()
    {
    	$new = $this->feature_model->create($this->request_data);

    	$response_code = $new ? 200 : 400;
    	$this->response($new, $response_code);
    }

    /**
     *  PUT feature service call
     */
    function index_put()
    {
    	$id = $this->request_data['id'];
    	unset($this->request_data['id']);

    	$feature = $this->feature_model->update_by_id($id, $this->request_data);
		$this->response($feature, 201);
    }

    /**
     *  Delete a feature
     */
    function index_delete()
    {
    	$feature = $this->feature_model->delete_by_id($this->delete('id'));
    	$this->response($feature, 200);
    }
}
