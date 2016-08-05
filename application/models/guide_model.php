<?
/**
 *  Model that fetches all data associated with guides
 */
class Guide_Model extends CI_Model
{
    private $collection = null;
    private $pathwaymap_collection = null;
    private $rolemap_collection = null;
    private $host = null;
    private $locale = 'en';

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

        $this->collection = $host . "_" . GUIDES;
        $this->version_collection = $host . "_" . VERSIONS;
        $this->pathwaymap_collection = $host . "_" . PATHWAYGUIDE;
        $this->rolemap_collection = $host . "_" . ROLEGUIDE;

        $this->locale = substr(Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']),0,2);

		$this->load->model('app_model');
		$this->load->model('language_model', '', FALSE, $host);
        $this->load->model('version_model', '', FALSE, $host);
    }


    /**
     *  Get real url from collection name
     */
    public function decode_url()
    {

        $first = str_replace("http_", "http://", $this->host);
        $second = str_replace("https_", "https://", $first);
        $host = str_replace("_", ".", $second);

        return $host;
    }

    /**
     * Decode host from collection friendly host name, and set it in $this->host
     * (Useful for external sources using REST services)
     * @param string $host URL of the application
     * @return string
     */
    public function encode_url($host = '')
    {
        if (empty($host))
            $host = $this->host;

        $first = str_replace("http://", "http_", $host);
        $second = str_replace("https://", "https_", $first);
        $this->host = str_replace(".", "_", $second);
    }


    /**
     * Check to see if a user has access to a pathway
     * @param string $pathwayid
     * @return boolean
     */
    public function has_access($guideid)
    {
        $wheres = array();

        $this->load->library('person', array('host' => $this->host));
        if(in_array("Administrator", $this->person->roles)) return true;

        $roles = $this->mongo_db
            ->where(array('guideid' => $guideid))
            ->get($this->rolemap_collection);

        //Default to Guest
        if(sizeof($roles) == 0) return true;

        // @todo clean this up with orWhere roleid && pathwayid
        foreach($roles as $role)
        {
            foreach($this->person->roleids as $roleid)
            {
                if($role['roleid'] == $roleid) return true;
            }
        }

        return false;
    }


    /**
     * Tag guides with completed
     * @param $enduser username
     * @param $whereIds array of ids
     * @param $guides array of guides
     */
    public function tag_completed($enduser, $whereIds, &$guides)
    {
        //Get Audit
        $this->load->model('analytics_model', '', FALSE, $this->host);
        $stats = $this->analytics_model->has_completed_by_ids($enduser, $whereIds);

        //Merge guides with completed
        foreach($guides as &$guide)
        {

            foreach($stats as $stat) {
                if ($stat['guideid'] == $guide['id'] && $stat['perc'] == 100) $guide['isComplete'] = true;
            }
        }
    }


    /**
     * Tag guides a user has started
     * @param $enduser username
     * @param $whereIds array of ids
     * @param $guides array of guides
     */
    public function tag_started($enduser, $whereIds, &$guides)
    {
        //Get Audit
        $this->load->model('analytics_model', '', FALSE, $this->host);
        $stats = $this->analytics_model->has_started_by_ids($enduser, $whereIds);

        //Merge guides with completed
        foreach($guides as &$guide)
        {
            foreach($stats as $stat) {
                if ($stat['guideid'] == $guide['id'] && $stat['perc'] > 0) $guide['isStarted'] = true;
            }
        }
    }


    /**
     * Get all guides from mongo
     * @param array $order_by order of tours
     * @return array $guides
     */
    public function get_all($select = array(), $forceAdmin = false, $enduser = "")
    {
        $hasAuto = false;
        $hasRestrict = false;
        $whereIds = array();
        $locale = $this->locale;

        $order = array('title' => 'ASC');

        // @todo Admin inactive
        $where = array('active' => true);
        $this->load->library('person', array('host' => $this->host));

        if($forceAdmin || $this->person->acl['guides']['create'] || $this->person->acl['guides']['edit']){
            $where = array();
        }

        //Get guides
        $guides = $this->mongo_db
            ->select($select)
            ->where($where)
            ->orderBy($order)
            ->get($this->collection);

        $guides = $this->version_check($guides);

        //Get language pack
    	if ($locale !== 'en'){
    		$languageContent = $this->language_model->get_all_by_language($locale);
    	}

		foreach($guides as $key => $guide){
			if( !$this->has_access($guide['id']) )
            {
                unset($guides[$key]);
                $guides = array_values($guides);
            }else{
                $hasAuto = isset($guide['auto']) || $hasAuto ? true : false;
                $hasRestrict = (isset($guide['restrict']) && sizeof($guide['restrict']) > 0) ? true : false;
                array_push($whereIds, $guide['id']);

                // Add Language content to the guide
                if (isset($locale) && $locale !== 'en'){
                	foreach($languageContent as $key => $guideContent){
                		if ($guide['id'] === $guideContent['guideid']){
                			$guide = $this->language_model->add_language_pack($guide, $guideContent);
                			$guides[$key] = $guide;
                		}
                	}
	            }
            }
        }

        //Check Audit for Completed items
        if($enduser && $hasRestrict || $enduser && $hasAuto) {
            $this->tag_completed($enduser, $whereIds, $guides);
            $this->tag_started($enduser, $whereIds, $guides);
        }

        return $guides;
    }

    /**
     * Get count of guides
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
     * Get all guides from mongo
     * @param array $order_by order of tours
     * @return array $guides
     */
    public function get_by_id($id)
    {

        //Get guides
        $guide = $this->mongo_db
            ->where(array('_id' => new MongoId($id)))
            ->get($this->collection);

        $guide = $this->version_check($guide);
        $guide = $guide[0];

        if($this->locale != $this->config->item("language")) {
            $languageContent = $this->language_model->get_lang_by_id($id, $this->locale);
            $guide = $this->language_model->add_language_pack($guide, $languageContent);
        }

        return $guide;
    }

    /**
     * Get multiple guides
     * @param string[] $ids Requested IDs
     * @return string[] Guides
     */
    public function get_by_ids($ids, $select = array())
    {
        $whereIds = array();

        foreach ($ids as $id)
        {
            array_push($whereIds, array('_id' => new MongoId($id)));
        }

        //Get all guide info
        $guides = $this->mongo_db
            ->select($select)
            ->orWhere($whereIds)
            ->get($this->collection);

        $guides = $this->version_check($guides);
        return $guides;
    }

    /**
     * Get by name
     * @param array $order_by order of tours
     * @return array $guides
     */
    public function get_by_title($title)
    {
        //Get guides
        $guide = $this->mongo_db
            ->whereLike("title", $title)
            ->get($this->collection);

        $guide = $this->version_check($guide);
        return $guide;
    }

    public function get_title_by_id($id)
    {
        $guide = $this->mongo_db
            ->where(array('_id' => new MongoId($id)))
            ->get($this->collection);

        $guide = $this->version_check($guide);
        return $guide[0]['title'];
    }

    /**
     *  Create a clone of a guide
     */
    public function create_clone($id)
    {
        if(is_string($id))
        {
            //Grab the current
            $guide = $this->get_by_id($id);
            $guide['title'] = $guide['title'] . " Copy";

            //Start off with version 1 when cloning
            $guide['version'] = 1;

            // @todo this causes a bug is it even needed for cloning?
            //$this->version_model->create($guide);

            //Clone
            $g = $this->create($guide);

            return $g != false;
        }
        else
        {
            foreach($id as $i)
            {
                $this->create_clone($i);
            }
        }
        return true;
    }

    /**
     * Get all guides from mongo
     * @param array $order_by order of tours
     * @return array $guides
     */
    public function create($guide)
    {
        unset($guide['id']);
        if(!isset($guide['step'])) $guide['step'] = array();

        $app = $this->app_model->get_by_host($this->decode_url());

        $this->load->library('person');
        $guide['version'] = 1;
        $guide['created'] = New Mongodate(time());
        $guide['creator'] = $this->person->username;
        $guide['host'] = $app['id'];


        if(sizeof($app) > 0){
            try
            {
                $id = $this->mongo_db->insert($this->collection, (array) $guide );
                $this->version_model->create($id->{'$id'}, $guide);
            }
            catch (Exception $e)
            {
                return false;
            }

            $this->update_cache($app);
            return $id->{'$id'};
        }else{
            return false;
        }
    }


    /**
     * Replaces content in a guide with new data
     * @param $id Guide Id
     * @param $find Find Text
     * @param $replace Replace With
     * @param $namespace Property to Search
     * @param $isPreview Save or Not (return preview of what will change)
     * @return boolean
     * @todo add how many we found
     */
    public function replace_prop($id, $find, $replace, $namespace, $case, $isPreview)
    {
        //Go get the guide
        $guide = $this->get_by_id($id);

        $found = array();
        $preview = array();
        $count = 0;
        $prop = str_replace('step.', '', $namespace);

        //Replace Step Content
        if(stripos($namespace, 'step.') !== false) {
            //Loop each step to find a match
            foreach ($guide['step'] as &$step) {
                if (stripos($step[$prop], $find) !== false) {

                    //Found a match
                    array_push($found, $count);

                    if ($isPreview) {
                        //Make a previous object
                        $arr = array(
                            "step" => $count,
                            "body" => str_replace($find, $find . "[" . $replace . "]", $step[$prop])
                        );

                        array_push($preview, $arr);
                    } else {
                        if($case) {
                            //Do the replace and save
                            $step[$prop] = str_replace($find, $replace, $step[$prop]);
                        }else{
                            $step[$prop] = str_ireplace($find, $replace, $step[$prop]);
                        }
                    }
                }
                $count++;
            }
        }else{
            if($case) {
                //Guide level changes
                $guide[$namespace] = str_replace($find, $replace, $guide[$namespace]);
            }else{
                $guide[$namespace] = str_ireplace($find, $replace, $guide[$namespace]);
            }
        }


        //Go save changes
        if(!$isPreview) {
            $this->update_by_id($id, $guide);
            $preview = $found;
        }

        return $preview;
    }

    /**
     * Updates a guide with new data
     * @param $id
     * @param $guide
     * @return mixed
     */
    public function update_by_id($id, $guide)
    {
        unset($guide['id']);

        //Update language content
        if($this->locale != $this->config->item("language")){
            $languageContent['title'] = $guide['title'];
            $languageContent['desc'] = $guide['desc'];

            unset($guide['title']);
            unset($guide['desc']);
            unset($guide['step']);

            return $this->language_model->update_by_guideid($id, $this->locale, $languageContent);
        } else {

            // Create new version and get version number
            $this->version_model->update($id, $guide);
            $current = $this->version_model->get_current_version($id);

            // Update object in main collection
            $this->load->library('person');
            $guide['version'] = $current;
            $guide['modified'] = New Mongodate(time());
            $guide['modifier'] = $this->person->username;
            $r = $this->mongo_db
                ->where(array('_id' => new MongoId($id)))
                ->set((array)$guide)
                ->update($this->collection);


            if (!empty($r))
                $this->update_cache();
            return empty($r) ? false : $guide;
        }
    }

    /** NOT USED anymore.
     *
     * Update guide without creating a new version
     * (Used by when changing versions)
     *
     * @param string $id Guide ID
     * @param [] $guide Guide object
     * @return [] guide
     */
    public function update_without_version($id, $guide)
    {
        unset($guide['id']);

        // Update object in main collection
        $this->mongo_db
            ->where(array('_id' => new MongoId($id)))
            ->set( (array) $guide )
            ->update($this->collection);

        $this->update_cache();
        return $guide;
    }

    /**
     * Delete guide by id
     * @param string id
     * @return boolean
     */
    public function delete_by_id($id, $host = '')
    {
        // Need to handle host coming from external sources
        if (!empty($host) && !empty($this->host))
            $this->host=$this->encode_url($host);

        try
        {
            if(is_string($id)){

                $this->load->model('trash_model', '', FALSE, $this->host);

                // Move to trash
                $this->trash_model->trash_guide($id);

                //Delete language model
                $this->language_model->delete_by_id($id, true);

                try {
                    //Delete role map
                    $this->mongo_db
                        ->where(array('guideid' => $id))
                        ->delete($this->rolemap_collection);

                    //Delete pathway map
                    $this->mongo_db
                        ->where(array('guideid' => $id))
                        ->delete($this->pathwaymap_collection);

                }catch(Exception $e){
                    log_message('error', 'Error trying to cleanup relationships. Collections may not exist? :' . $e);
                }

                $this->update_cache();
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

                $this->update_cache();
                return true;
            }
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * Update cache number
     */
    public function update_cache($app = null)
    {
        if(!$app) $app = $this->app_model->get_by_host($this->decode_url());
        $app['version']++;

        $this->app_model->update_by_id($app['id'], $app);
    }

    /**
     * Get cache number
     */
    public function get_cache($host = null)
    {
        if(!isset($host)) $host = $this->decode_url();

        $app = $this->app_model->get_by_host($host);
        return $app['version'];
    }

    /** Check to see if the guide has a version number, if not start at version 1
     *  TODO: Once all guides have version numbers, this function and it's references can be removed
     * @param GuideObject(s) $guides Pass a single guide or an array of guides
     * @return GuideObject(s) With version added if necessary.
     */
    private function version_check($guides)
    {
        // Check for single guide
        $was_array = true;
        if (isset($guides['title']))
        {
            $guides = array($guides);
            $was_array = false;
        }

        foreach ($guides as &$guide)
            if (!isset($guide['version']))
            {
                $guide['version'] = 1;
                $this->update_by_id($guide['id'], $guide);
            }

        if ($was_array)
            return $guides;
        else
            return $guides[0];

    }
}
