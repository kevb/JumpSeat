<?
/**
*  Model that fetches all data associated with tours
*/
class Role_Model extends CI_Model
{
	private $collection = null;
	private $pathrole_collection = null;
	private $host = null;

	/**
	 * Type of access
	 * @var unknown
	 */
	protected $types = array(
		"read" => false,
		"create" => false,
		"edit" => false,
		"delete" => false,
		"assignRole" => false,
		"assignGuide" => false
	);

	/**
	 * Default ACL
	 * @var $defaults
	 */
	protected $controls = array(
		'guides' => array(),
		'steps' => array(),
		'pathways' => array(),
		'roles' => array(),
		'config' => array(),
		'analytics' => array()
	);

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
		$this->collection = $host . "_" . ROLES;
		$this->pathrole_collection = $host . "_" . PATHWAYROLE;
	}

	/**
	 * Get all roles from mongo
	 * @param array $order_by order of tours
	 * @return array $roles
	 */
	public function get_defaults()
	{
		$defaults = $this->controls;

		foreach($defaults as $def => $val){

			$types = $this->types;

			if($def != "pathways" && $def != "roles"){
				unset($types['assignGuide']);
				unset($types['assignRole']);
			}

			$defaults[$def] = $types;
		}

		return $defaults;
	}

	/**
	 * Get all roles from mongo
	 * @param array $order_by order of tours
	 * @return array $roles
	*/
	public function get_all($select = array(), $count = false)
	{
		$order = array('title' => 'ASC');
		$where = array();

		//Get roles
		$roles = $this->mongo_db
			->where($where)
			->orderBy($order)
			->get($this->collection);

		//Get guide count
		if($count)
		{
			foreach($roles as &$role)
			{
				$this->load->model('rolemap_model', '', FALSE, $this->host);
				$role['guides'] = $this->rolemap_model->count_guides($role['id']);
			}
		}

		return $roles;
	}

	/**
	 * Get count of roles
	 * @return integer $count
	 */
	public function get_count()
	{
		//Get roles
		$count = $this->mongo_db
			->count($this->collection);

		return $count;
	}

	/**
	 * Get role by id
	 * @param array $id id
	 * @return array $role
	 */
	public function get_by_id($id, $permission = false)
	{
		$select = array('title', 'description');
		if($permission) $select = array('title', 'guides', 'steps', 'pathways', 'roles', 'config', 'analytics');

		//Get roles
		$role = $this->mongo_db
			->where(array('_id' => new MongoId($id)))
			->select($select)
			->get($this->collection);

		if(sizeof($role) > 0 ){
			return $role[0];
		}else{
			return false;
		}
	}


	/**
	 * Get role by title
	 * @param array $id id
	 * @return array $role
	 */
	public function get_by_title($title, $permission = false, $collection = null)
	{
		$select = array('title', 'description');
		if($permission) $select = array('title', 'description', 'guides', 'steps', 'pathways', 'roles', 'config', 'analytics');

        // Override
		if($collection) $this->collection = $collection . "_" . ROLES;

		//Get roles
		$role = $this->mongo_db
			->where(array('title' => $title))
			->select($select)
			->get($this->collection);

		if(sizeof($role) > 0 ){
			return $role[0];
		}else{
			return false;
		}
	}


	/**
	 * Get Access Control List
	 * @param string[] $roles role names
	 * @return array $acl permissions
	 */
	public function get_acl($userroles)
	{
		$acl = $this->get_defaults();

		foreach($userroles as $userrole)
		{
			//var_dump($userrole);
			$types = array('read', 'create', 'edit', 'delete', 'assignGuide', 'assignRole');

			//Get roles
			$roles = $this->mongo_db
				->where(array('title' => $userrole))
				->get($this->collection);

			foreach($roles as $role){
				foreach($acl as $akey => $avalue){
					foreach($types as $type)
					{
						// @todo cleanup assignGuide and Role
						if(!isset($acl[$akey][$type]) || $acl[$akey][$type] != true){
							$acl[$akey][$type] = isset($role[$akey][$type]) && $role[$akey][$type] ? true : false;
						}
					}
				}
			}
		}

		return $acl;
	}

	/**
	 * Create a new role
	 * @param array $role data
	 * @return array $role
	 */
	public function create($role, $collection = null)
	{
		unset($role['id']);

        // Override
        if($collection) $this->collection = $collection . "_" . ROLES;

		$new_role = array_replace_recursive($this->get_defaults(), (array) $role);

		$this->load->library('person');
		$new_role['created'] = New Mongodate(time());
		$new_role['creator'] = $this->person->username;

		try
		{
			$id = $this->mongo_db->insert($this->collection, $new_role);
			return $id->{'$id'};
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Updates a role with new data
	 * @param string $id id
	 * @param array $role data
	 */
	public function update_by_id($id, $role)
	{
		unset($role['id']);

		$this->load->library('person');
		$role['modified'] = New Mongodate(time());
		$role['modifier'] = $this->person->username;

		$this->mongo_db
			->where(array('_id' => new MongoId($id)))
			->set( (array) $role )
			->update($this->collection);

		return $role;
	}

	/**
	 * Delete role by id
	 * @param string id
	 * @return boolean
	 */
	public function delete_by_id($id)
	{
		try
		{
			if(is_string($id)){

				//Delete one
				$this->mongo_db
					->where(array('_id' => new MongoId($id)))
					->delete($this->collection);

				//Delete path > role map
				$this->mongo_db
					->where(array('roleid' => $id))
					->delete($this->pathrole_collection);

				return true;
			}else{

				//Delete multi
				foreach ($id as $i)
				{
					if (!$this->delete_by_id($i))
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
