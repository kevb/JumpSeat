<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Aero Guides
 *
 * CRUD API services for Aero guides
 *
 * @package		Aero
 * @subpackage	Guides
 * @category	REST Controller
 * @author		Mike Priest
*/
require APPPATH.'/libraries/REST_Controller.php';

class Guides extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
        $this->load->model('guide_model', '', FALSE, $this->host);
    }

    /**
     *  GET guide service call
     */
    function index_get()
    {
    	$id = $this->input->get('id');
        $enduser = $this->input->get('enduser');

    	$select = $this->input->get('select') ? $this->input->get('select') : array();

    	if($id && is_array($id)){
    		$guides = $this->guide_model->get_by_ids($id);
    	}elseif($id){
    		$guides = $this->guide_model->get_by_id($id);
        }else{
    		$guides = $this->guide_model->get_all($select, false, $enduser);
    	}

    	//Current cache
    	$guides[0]['cache'] = $this->guide_model->get_cache();

    	$this->response($guides, 200);
    }


    /**
     *  Get table data for admin
     */
    function table_get()
    {
    	$rows["data"] = array();

    	//All guides
    	$guides = $this->guide_model->get_all();

    	//Get person
    	$this->load->library('person', array('host' => $this->host));
		$acl = $this->person->acl;
		$user = $this->person->username;

    	date_default_timezone_set($this->config->item('timezone'));

    	//Build rows
    	foreach($guides as $guide)
    	{
	    	$id = $guide['id'];
	    	$title = $guide['title'];
	    	$desc = $guide['desc'];
	    	$creator = $guide['creator'];
	    	$active = $guide['active'] ? "Yes" : "No";

	    	$created = explode(" ", date('y-m-d h:i:s', $guide['created']->sec));
	    	$created = implode(' at ', $created);
	    	$steps = sizeof($guide['step']);
            $version = $guide['version'];

			$startUrl = isset($guide['step']) && isset($guide['step'][0]) && isset($guide['step'][0]['url']) ? $guide['step'][0]['url'] : '';

            $first = str_replace("http_", "http://", $this->host);
			$second = str_replace("https_", "https://", $first);
			$host = str_replace("_", ".", $second);

			$hostUrl = ($host == IAPP) ? "/iapp/welcome/home" : $host;

    		$tools = "<div class='tools' style='width:316px' data-id='$id'>";
			$tools .= "<a target='_blank' href='$hostUrl$startUrl?guideid=$id' class='small button success'>Steps <i class='ss-icon'>&#xE396;</i></a> ";

    		if($acl['guides']['edit'] || ($creator == $user && $acl['guides']['create'])){
    			$tools .= "<a class='small button success edit'>Edit <i class='ss-icon'>&#x270E;</i></a>";
    		}
    		if($acl['guides']['delete'] || ($creator == $user && $acl['guides']['create'])){
    			$tools .= "<a class='small button alert delete'>Delete <i class='ss-icon'>&#xE0D0;</i></a>";
    		}
    		$tools .= "</div></li>";


    		$row = array(
    				"<input type='checkbox' class='select' value='1' data-id='$id' />",
    				$title,
    				$desc,
    				$active,
    				$steps,
                    '<a href="versions/'. $id .'">'. $version .'</a>',
    				$creator . "<br/>" .$created,
    				$tools
    		);

    		array_push($rows['data'], $row);
    	}
    	$this->response($rows, 200);
    }


    /**
     *  GET guide cache
     */
    function cache_get()
    {
    	$this->response($this->guide_model->get_cache(), 200);
    }

 	/**
     *  POST guide service call
     */
    function index_post()
    {
    	$new = $this->guide_model->create($this->request_data);

    	$response_code = $new ? 200 : 400;
    	$this->response($new, $response_code);
    }

    /**
     *  POST clone guides
     */
    function clone_post()
    {
    	$new = $this->guide_model->create_clone($this->request_data['id']);

    	$response_code = $new ? 200 : 400;
    	$this->response($new, $response_code);
    }

	/**
	 *  POST find replace
	 */
	function replace_put()
	{
		$id = $this->request_data['id'];
        $find = $this->request_data['find'];
        $replace = $this->request_data['replace'];
        $ns = $this->request_data['namespace'];
        $case = $this->request_data['case'];

        $prev = isset($this->request_data['preview']) && $this->request_data['preview'] == true;
        $response = "";

        if(stripos($id, ',') == false) {
            $response = $this->guide_model->replace_prop($id, $find, $replace, $ns, $case, $prev);
        }else{
            $ids = explode(',', $id);

            foreach($ids as $id){
                $response = $this->guide_model->replace_prop($id, $find, $replace, $ns, $case, $prev);
            }
        }
		$this->response($response, 201);
	}

    /**
     *  POST guide service call
     */
    function index_put()
    {
    	$id = $this->request_data['id'];
    	unset($this->request_data['id']);

    	$guide = $this->guide_model->update_by_id($id, $this->request_data);
		$this->response($guide, empty($guide) ? 400 : 200);
    }

    /**
     *  Delete a guide
     */
    function index_delete()
    {
    	$guide = $this->guide_model->delete_by_id($this->delete('id'));
    	$this->response($guide, 200);
    }
}
