<?
/**
 *  Model that fetches all data associated with guides
 */
class Language_Model extends CI_Model
{
    private $collection = null;
    private $pathwaymap_collection = null;
    private $rolemap_collection = null;
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

        $this->collection = $host . "_" . GUIDES . '_lang';
        $this->defaultCollection = $host . "_" . GUIDES;
        $this->version_collection = $host . "_" . VERSIONS;
        $this->pathwaymap_collection = $host . "_" . PATHWAYGUIDE;
        $this->rolemap_collection = $host . "_" . ROLEGUIDE;

        $this->load->model('app_model');
        $this->load->model('version_model', '', FALSE, $host);
    }


    /**
     *  Get real url from collection name
     */
    public function get_real_url(){

        $first = str_replace("http_", "http://", $this->host);
        $second = str_replace("https_", "https://", $first);
        $host = str_replace("_", ".", $second);

        return $host;
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

    public function get_all(){
        $object = $this->mongo_db
            ->get($this->collection);

        return $object;
    }

    /**
     * Get specfic language content by either mongo id or guideid
     * @param string id
     * @return array $guide
     */
    public function get_by_id($id, $languageDb = false)
    {
        if ($languageDb){
            $dbCollection = $this->collection;
        }else{
            $dbCollection = $this->defaultCollection;
        }

        //Get guide
        $object = $this->mongo_db
            ->where(array('_id' => new MongoId($id)))
            ->get($dbCollection);

        return $object;
    }


    /**
     * Get specfic language content by either mongo id or guideid
     * @param string id
     * @return array $guide
     */
    public function get_lang_by_id($id, $language)
    {
        //Get guide
        $content = $this->mongo_db
            ->where(array('guideid' => $id, 'lang' => $language))
            ->get($this->collection);

        //No lang pack go create it
        if(sizeof($content) == 0){
            $input['id'] = $id;
            $input['lang'] = $language;
            return $this->create($input, true);
        }

        return $content[0];
    }

    /**
     * Get all language content from mongo by guideid
     * @param string id
     * @return array $languageContent
     */
    public function get_all_by_id($id)
    {
        //Get all content by guideid
        $object = $this->mongo_db
            ->where(array('guideid' => $id))
            ->get($this->collection);

        return $object;
    }

    /**
     * Get all language content from mongo by lang
     * @param string language
     * @return array $languageContent
     */
    public function get_all_by_language($language)
    {
        //Get all content by guideid
        $object = $this->mongo_db
            ->where(array('lang' => $language))
            ->get($this->collection);

        return $object;
    }

    /**
     * Create empty language content for a guide based off of the corresponding default guide id and a language flag
     * Saves this new guide in the _lang collection
     * @param json input - expects guide id and language flag
     * @return guide id
     */
    public function create($input, $object = false)
    {
        $guideid = $input['id'];
        $languageFlag = $input['lang'];
        $defaultGuide = $this->get_by_id($guideid);

        $app = $this->app_model->get_by_host($this->get_real_url());
        $newLangGuide = $this->strip_default_content($defaultGuide[0], $languageFlag);

        if(sizeof($app) > 0){
            try
            {
                $id = $this->mongo_db->insert($this->collection, (array) $newLangGuide );
            }
            catch (Exception $e)
            {
                return false;
            }

            if(!$object) {
                return $id->{'$id'};
            }else{
                $newLangGuide['id'] = $id->{'$id'};
                return $newLangGuide;
            }
        }else{
            return false;
        }
    }

    /**
     * Updates the language content with new data
     * @param $id
     * @param $languageContent
     * @return languageContent object
     */
    public function update_by_id($id, $languageContent)
    {
        unset($languageContent['id']);

        $this->mongo_db
            ->where(array('_id' => new MongoId($id)))
            ->set( (array) $languageContent )
            ->update($this->collection);

        return $languageContent;
    }

    /**
     * Updates the language content with new data
     * @param $id
     * @param $languageContent
     * @return languageContent object
     */
    public function update_by_guideid($id, $language, $languageContent)
    {
        unset($languageContent['id']);

        $this->mongo_db
            ->where(array('guideid' => $id, 'lang' => $language))
            ->set( (array) $languageContent )
            ->update($this->collection);

        return $languageContent;
    }

    /**
     * Delete language content by id
     * @param string id
     * @return boolean
     */
    public function delete_by_id($id, $guideId)
    {
        try
        {
            if ($guideId == false){
                if(is_string($id)){
                    try {
                        $this->mongo_db
                            ->where(array('_id' => new MongoId($id)))
                            ->delete($this->collection);

                    }catch(Exception $e){
                        log_message('error', 'Error trying delete :' . $e);
                    }
                    return true;
                }else{

                    //Delete multi
                    foreach ($id as $i)
                    {
                        if (!$this->delete_by_id($i, false))
                        {
                            return false;
                        }
                    }
                    return true;
                }
            }elseif ($guideId == true){
                if(is_string($id)){
                    try {
                        $this->mongo_db
                            ->where(array('guideid' => $id))
                            ->deleteAll($this->collection);

                    }catch(Exception $e){
                        log_message('error', 'Error trying delete :' . $e);
                    }
                    return true;
                }
            }
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * Strips out content from the default guide and returns an empty guide with the language flag
     * @param array guide
     * @param string language flag
     * @return array $guide
     */
    private function strip_default_content($guide, $languageFlag){
        // Loop through the guide content array
        // unset unneeded keys and add empty strings
        foreach ($guide as $key => $content) {
            if ($key !== 'step' && $key !== 'id' && $key !== 'title' && $key !== 'desc') {
                unset($guide[$key]);
            }elseif ($key === 'id') {
                $guideid = $guide[$key];
                unset($guide[$key]);
                $guide['guideid'] = $guideid;
            }elseif ($key !== 'step') {
                $guide[$key] = $guide[$key];
            }
        }

        // Loop through the step content and unset unneeded keys, set language content to an empty string.
        foreach ($guide['step'] as &$step) {
            foreach ($step as $key => $stepContent) {
                if ($key !== 'body' && $key !== 'title' && $key !== 'alert' && $key !== 'alertContent' && $key !== 'lossalert' && $key !== 'lossalertContent'){
                    unset($step[$key]);
                }else{
                    $step[$key] = $step[$key];
                }
            }
        }

        $guide['lang'] = $languageFlag;

        return $guide;
    }

    public function strip_step_content($step){

        foreach ($step as $key => $stepContent) {
            if ($key !== 'body' && $key !== 'title' && $key !== 'alert' && $key !== 'alertContent' && $key !== 'lossalert' && $key !== 'lossalertContent'){
                unset($step[$key]);
            }else{
                $step[$key] = $step[$key];
            }
        }

        return $step;
    }

    public function add_language_pack($guide, $languageContent){

        $guide['langid'] = $languageContent['id'];

        // Loop through the language content array and match it to the current guide if it isn't the step array
        foreach ($languageContent as $key => $content) {
            if ($key !== 'step' && $key !== 'guideid' && $key !== 'id'){
                $guide[$key] = $content;
            }
        }

        // Loop through the step content and match it to the content in the current guide based off of the guide id
        $languageContentSteps = $languageContent['step'];
        foreach ($guide['step'] as &$step) {
            $stepIndex = array_search($step, $guide['step']);
            $stepLanguageContent = $languageContentSteps[$stepIndex];
            foreach ($step as $key => $stepContent) {
                if (($key == "title" || $key == "body") && isset($stepLanguageContent[$key])){
                    $step[$key] = $stepLanguageContent[$key];
                }
            }
        }

        return $guide;
    }
}
