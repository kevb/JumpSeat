<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Generic session functions
 *
 * @package 	Aero
 * @subpackage	session
 * @category	Library
 * @author		Mike Priest
 */
class Person
{
	public $CI = null;
	public $host = null;
	public $acl = null;
	public $roles = array('Guest');
	public $roleids = array();
	public $id = "";
	public $username = "";
	public $firstname = "";
	public $lastname = "";

	/**
	 *  Contructor
	 * @param array $params
	 */
	public function __construct($params = array('host' => ''))
	{
	    $this->CI =& get_instance();

		//Load the new ACL
		$this->host = isset($params['host']) ? $params['host'] : "";

        if(isset($_SESSION['sysadmin'])) unset($_SESSION['appuser']);

		//Start permissions
		$this->set_person();
		$this->set_acl();
	}

	/**
	 *  Set person data
	 */
	public function set_person()
	{
		if(isset($_SESSION['username']) && $_SESSION['username'] != ""){
			//User model
			$this->CI->load->model('user_model');
			$user = $this->CI->user_model->get_by_email($_SESSION['username']);

			if($user){
				//Set object
				if($user['sysadmin']) $this->roles = array('Administrator', 'Guest');

				$this->id = $user['id'];
				$this->firstname = $user['firstname'];
				$this->lastname = $user['lastname'];
				$this->username = $user['email'];
			}else{
				return false;
			}
		}
	}

	/**
	 *  Set Permissions
	 */
	public function set_groups()
	{
		$roles = array();

		//Reset on host change
		if(isset($_SESSION['host']) && ($_SESSION['host'] != $this->host) ){
			unset($_SESSION['roles']);
		}

		//Use cache
		if(isset($_SESSION['roles'])){
			$this->roles = $_SESSION['roles'];
			$this->roleids = $_SESSION['roleids'];
			return;
		}

		if(isset($_SESSION['userid'])){
			$this->CI->load->model('roleusermap_model', '', FALSE, $this->host);
			$roles = $this->CI->roleusermap_model->get_by_user($_SESSION['userid']);
		}

		//Add the Guest Role for Default
		$guest_id = $this->CI->role_model->get_by_title("Guest")['id'];
		array_push($roles, array("title" => "Guest", "id" => $guest_id));

		//Add roles to default
		foreach($roles as $role){
			array_push($this->roles, $role['title']);
			array_push($this->roleids, $role['id']);
		}

		$_SESSION['roles'] = $this->roles;
		$_SESSION['roleids'] = $this->roleids;
	}

	/**
	 *  Set Permissions
	 */
	public function set_acl()
	{
		$this->CI->load->model('role_model', '', FALSE, $this->host);

		//Set user groups
		$this->set_groups();

		//Reset on host change
		if(isset($_SESSION['host']) && ($_SESSION['host'] != $this->host) ){
			unset($_SESSION['acl']);
		}

		//Use cache
		if(isset($_SESSION['acl'])){
			$this->acl = $_SESSION['acl'];
			return;
		}

		//Go get ACL for host
		$this->acl = $this->CI->role_model->get_acl($this->roles, true);

		$_SESSION['acl'] = $this->acl;
		$_SESSION['host'] = $this->host;
	}
}
