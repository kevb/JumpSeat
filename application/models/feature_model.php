<?
/**
*  Model that fetches all data associated with features
*/
class Feature_Model extends CI_Model
{
	private $collection = null;
	private $host = null;

	/**
	 * Constructor
	*/
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
		$this->load->library('mongo_db');

		//Load from config
		$this->load->config('config', TRUE);

        $this->host = IAPP;
		$this->collection = IAPP . "_" . FEATURES;

		$this->load->model('app_model');
	}

	/**
	 * Get all features from mongo
	 * @param array $order_by order of tours
	 * @return array $features
	*/
	public function get_all($select = array(), $forceAdmin = false)
	{
		$order = array('title' => 'ASC');

		// @todo Admin inactive
		$where = array('active' => true);
		$this->load->library('person', array('host' => $this->host));

		if($forceAdmin || $this->person->acl['guides']['create'] || $this->person->acl['guides']['edit']){
			$where = array();
		}

		//Get features
		$features = $this->mongo_db
			->select($select)
			->where($where)
			->orderBy($order)
			->get($this->collection);

		return $features;
	}

	/**
	 * Get all features from mongo
	 * @param array $order_by order of tours
	 * @return array $features
	 */
	public function get_by_id($id)
	{
		//Get features
		$feature = $this->mongo_db
			->where(array('_id' => new MongoId($id)))
			->get($this->collection);

		return $feature[0];
	}

	/**
	 * Get multiple features
	 * @param string[] $ids Requested IDs
	 * @return string[] Features
	 */
	public function get_by_ids($ids, $select = array())
	{
        $whereIds = array();

        foreach ($ids as $id)
        {
            array_push($whereIds, array('_id' => new MongoId($id)));
        }

        //Get all feature info
        $features = $this->mongo_db
            ->select($select)
            ->orWhere($whereIds)
            ->get($this->collection);

        return $features;
	}

	/**
	 * Get by name
	 * @param string $title of feature
	 * @return array $features
	 */
	public function get_by_title($title)
	{
		//Get features
		$feature = $this->mongo_db
			->whereLike("title", $title)
			->get($this->collection);

        return (sizeof($feature) > 0) ? $feature[0] : $feature;
	}

	/**
	 * Get all features from mongo
	 * @param array $order_by order of tours
	 * @return array $features
	 */
	public function create($feature)
	{
		unset($feature['id']);

		$app = $this->app_model->get_by_host($this->host);

		$this->load->library('person');
        $feature['created'] = New Mongodate(time());
		$feature['creator'] = $this->person->username;
        $feature['pages'] = array();

		if(sizeof($app) > 0){
			try
			{
				$id = $this->mongo_db->insert($this->collection, (array) $feature );
			}
			catch (Exception $e)
			{
				return false;
			}

			return $id->{'$id'};
		}else{
			return false;
		}
	}

	/**
	 * Updates a feature with new data
	 */
	public function update_by_id($id, $feature)
	{
        unset($feature['id']);

        // Update object in main collection
		$this->load->library('person');
        $feature['modified'] = New Mongodate(time());
		$feature['modifier'] = $this->person->username;
		$this->mongo_db
			->where(array('_id' => new MongoId($id)))
			->set( (array) $feature )
			->update($this->collection);

		return $feature;
	}

	/**
	 * Delete feature by id
	 * @param string id
	 * @return boolean
	 */
	public function delete_by_id($id)
	{
		try
		{
			if(is_string($id)){

                //Delete role map
                $this->mongo_db
                    ->where(array('_id' => new MongoId($id)))
                    ->delete($this->collection);

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
