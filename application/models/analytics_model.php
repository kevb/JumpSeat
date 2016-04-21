<?
/**
*  Model that stores and fetches audit data
 *
 * CRUD API services for Aero analytics
 *
 * @package		Aero
 * @subpackage	audit
 * @category	Analytics
 * @author		Trevor Dell
*/
class Analytics_Model extends CI_Model
{
	private $collection = null;
	private $host = null;

	/**
	 * Constructor
	*/
	function __construct($host)
	{
		// Call the Model constructor
		parent::__construct();
		$this->load->library('aero_lib');
		$this->load->library('mongo_db');
		$this->load->model('guide_model', '', FALSE, $host);

		//Load from config
		$this->load->config('config', TRUE);
		$this->host = $host;
		$this->collection = $host . "_" . AUDIT;
	}

	/**
	 * Get all guides and the total times taken
	 * @return array of stats for each guide
	 */
	function completed_started()
	{
		$report = array();
		$guides = $this->guide_model->get_all(array('title'));

		//Grab count of started
		$started = $this->_guides_started();

		//Collect report on all guides
		foreach($guides as $guide)
		{

			if(isset($started[$guide['id']])){
				array_push($report, array(
					'title' => $guide['title'],
					'stats' => array('started' => $started[$guide['id']], 'completed' => $this->_guides_completed($guide['id']))
				));
			}else{
				array_push($report, array(
					'title' => $guide['title'],
					'stats' => array('started' => 0, 'completed' => 0)
				));
			}
		}

		return $report;
	}


	/**
	 * Get all guides and the total times taken
	 * @return array of stats for each guide
	 */
	function user_stats($username)
	{
		$report = array();
		$all_guides = $this->guide_model->get_all(array('title'));

		//Collect report on all guides
		foreach($all_guides as $a_guide)
		{
            // @todo move into aggregate & mapReduce to join

            // Get only taken guides for user
            $where = array(
                'user' => $username,
                'guideid' => $a_guide['id']
            );

            //Get the Guide
            $guide_report = $this->mongo_db
                ->select(array('guideid', 'perc'))
                ->where($where)
                ->orderBy(array('perc' => -1 ))
                ->limit(1)
                ->get($this->collection);

            //Get Guide Information
            $guide_data = $this->guide_model->get_by_id($a_guide['id']);

            //Results?
            if(sizeof($guide_report) > 0) {
                array_push($report, array(
                    'y' => $guide_data['title'],
                    'x' => $guide_report[0]['perc']
                ));
            }else{
                array_push($report, array(
                    'y' => $guide_data['title'],
                    'x' => 0
                ));
            }
		}

		return $report;
	}


	/**
	 * Returns number of unique users for this app
	 */
	function get_user_count()
	{
		return count($this->_unique_users());
	}

	/**
	 *  Count guides completed
	 *  @param string $guideid
	 *  @return number Count of completed guides
	 */
	private function _guides_completed($guideid)
	{
		// Get only completed guides
		$where = array(
			'guideid' => $guideid,
			'perc' => 100
		);

		$guideids = $this->mongo_db
			->select(array('guideid'))
			->where($where)
			->get($this->collection);

		return count($guideids);
	}


    /**
     *  Check if a user has completed a guide
     *  @param string $guideid
     *  @param string $user
     *  @return boolean
     */
    public function has_completed($user, $guideid)
    {
        // Get only completed guides
        $where = array(
            'guideid' => $guideid,
            'user' => $user,
            'perc' => 100
        );

        $guideids = $this->mongo_db
            ->select(array('progress'))
            ->where($where)
            ->get($this->collection);

        return count($guideids) > 0;
    }


    /**
     *  Check if a user has completed a guide
     *  @param string $ids guide ids
     *  @param string $user
     *  @return boolean
     */
    public function has_completed_by_ids($user, $ids)
    {
        $wheres = array();

        //Completed by user
        $where = array(
            'user' => $user,
            'perc' => 100
        );

        //Guide ids
        foreach ($ids as $id) array_push($wheres, array('guideid' => $id));

        //Get all guide info
        $guides = $this->mongo_db
            ->select(array('perc', 'guideid', 'user'))
            ->where($where)
            ->orWhere($wheres)
            ->get($this->collection);

        return $guides;
    }


	/**
	 *  Check if a user has completed a guide
	 *  @param string $ids guide ids
	 *  @param string $user
	 *  @return boolean
	 */
	public function has_started_by_ids($user, $ids)
	{
		$wheres = array();

		//Guide ids
		foreach ($ids as $id) array_push($wheres, array('guideid' => $id));

		//Get all guide info
		$guides = $this->mongo_db
			->select(array('perc', 'guideid', 'user'))
			->where(array('user' => $user ))
            ->whereGte('perc', 1)
			->orWhere($wheres)
			->get($this->collection);

		return $guides;
	}


	/**
	 * Count of how many times a guide has been taken
	 * @return array $totals map of guide vs taken
	 */
	private function _guides_started()
	{
		//Get totals
		$totals = array();
		$c = $this->mongo_db->_dbhandle->{$this->collection};
		$ops = array(
				array(
						'$project' => array(
								"guideid" => 1
						)
				),
				array(
						'$group' => array(
								"_id" => array("guideid" => '$guideid'),
								"count" => array('$sum' => 1)
						)
				),
				array(
						'$group' => array(
								"_id" => '$_id.guideid',
								"taken" => array('$sum' => '$count')
						)
				)
		);
		$results = $c->aggregate($ops)['result'];

		//Remap
		foreach($results as $result){ $totals[$result['_id']] = $result['taken']; }

		return $totals;
	}

	/**
	 * Get unique users from audit log
	 * @return string[] User names
	 */
	private function _unique_users($guideid = false) {

		if ($guideid)
			$guideid = array("guideid" => $guideid);
		else
			$guideid = array();

		$users = $this->mongo_db
			->distinct($this->collection, "user", $guideid);

		return $users;
	}
}
