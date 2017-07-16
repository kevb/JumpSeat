<?
/**
*  Model that fetches all data associated with tours
*/
class RoleUserMap_Model extends CI_Model
{
	private $collection = null;
	private $user_collection = null;
	private $role_collection = null;
	private $host = null;

	/**
	 * Constructor
	*/
	function __construct($host)
	{
		// Call the Model constructor
		parent::__construct();
		$this->load->library('mongo_db');

		//Load from config
		$this->load->config('config', TRUE);
		$this->host = $host;
		$this->collection = $host . "_" . ROLEUSER;
		$this->user_collection = USERS;
		$this->role_collection = $host . "_" . ROLES;
	}
	
	
	/**
	 * Does a user relationship already exist
	 * @param string $roleid
	 * @param string $guideid
	 * @return boolean map exists
	 */
	public function map_exists($userid, $roleid)
	{
		//Get roles
		$role_map = $this->mongo_db
			->select(array('guideid'))
			->where(array('roleid' => $roleid, 'userid' => $userid))
			->get($this->collection);
	
		return (sizeof($role_map) > 0);
	}


	/**
	 * Get list of users by role id
	 * @param array $id id
	 * @return array $role
	 */
	public function get_by_role($roleid)
	{
		$users = array();
		$whereIds = array();

		//Get roles
		$role_map = $this->mongo_db
			->select(array('userid'))
			->where(array('roleid' => $roleid))
			->get($this->collection);

		if(sizeof($role_map) > 0){
			foreach ($role_map as $role){
				array_push($whereIds, array('_id' => new MongoId($role['userid'])));
			}

			$users = $this->mongo_db
				->select(array('firstname', 'lastname', 'email'))
				->orWhere($whereIds)
				->get($this->user_collection);

		}

		return $users;
	}


	/**
	 * Get list of roles by user id
	 * @param array $id id
	 * @return array $user
	 */
	public function get_by_user($userid)
	{
		$roles = array();
		$whereIds = array();

		//Get role map
		$role_map = $this->mongo_db
			->select(array('roleid'))
			->where(array('userid' => $userid))
			->get($this->collection);

		if(sizeof($role_map) > 0){
			foreach ($role_map as $role){
				array_push($whereIds, array('_id' => new MongoId($role['roleid'])));
			}

			$roles = $this->mongo_db
				->select(array('title'))
				->orWhere($whereIds)
				->get($this->role_collection);
		}

		return $roles;
	}


	/**
	 * Create a new association (role to user)
	 * @param string $roleid
	 * @param string $userid
	 * @return string $if
	 */
	public function create($roleid, $userid, $collection = null)
	{
		$map['roleid'] = $roleid;
		$map['userid'] = $userid;

		// Override collection
		if($collection) $this->collection = $collection . "_" . ROLEUSER;

		if(!$this->map_exists($userid, $roleid))
			{
			try
			{
                log_message('debug', 'ZZT Changed Use ' . $this->collection);

                $id = $this->mongo_db->insert($this->collection, $map );
				return $id->{'$id'};
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		return false;
	}


	/**
	 * Delete role association to user
	 * @param string $roleid
	 * @param string $userid
	 * @return boolean
	 */
	public function delete($roleid, $userid)
	{
		try
		{
			if(is_string($userid)){

				//Delete one
				$this->mongo_db
					->where(array('roleid' => $roleid, 'userid' => $userid))
					->delete($this->collection);

				return true;

			}else{

				//Delete multi
				foreach ($userid as $i)
				{
					if (!$this->delete($roleid, $i))
					{
						return false;
					}
				}
				return true;
			}
		}
		catch (Exception $e)
		{
			return false;
		}
	}
}