<?
/**
 * Aero Steps
 *
 * Aero step model
 *
 * @package		Aero
 * @subpackage	Step_Model
 * @category	CI_Model
 * @author		Mike Priest
*/
class Step_Model extends CI_Model
{
	private $id = null;
	private $collection = null;
	private $host = null;
    private $locale = 'en';

	/**
	 * Constructor
	*/
	function __construct($host, $guideid)
	{
		// Call the Model constructor
		parent::__construct();
		$this->load->library('mongo_db');

		//Load from config
		$this->id = $guideid;
		$this->host = $host;
		$this->load->config('config', TRUE);

		$this->collection = $this->host . "_" . GUIDES;
        $this->locale = substr(Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']),0,2);

		$this->load->model('guide_model', '', FALSE, $this->host);
        $this->load->model('version_model', '', FALSE, $this->host);
        $this->load->model('language_model', '', FALSE, $this->host);
	}


	/**
	 * Get all guides from mongo
	 * @param array $order_by order of tours
	 * @return array $guides
	 */
	public function insertAt($index, $steps)
	{
		$guide = $this->guide_model->get_by_id($this->id);

		// Get all stored language objects
		$languageObjects = $this->language_model->get_all_by_id($this->id);

		unset($steps['id']);

		if(sizeof($guide) > 0){

			array_splice( $guide["step"], $index, 0, array( $steps));

			//Update the DB
			$this->save($guide);

			// If language objects then add the new step after stripping the content
			if (sizeof($languageObjects) > 0){
				$thisStep = $this->language_model->strip_step_content($steps);
				foreach ($languageObjects as &$lObject) {
					array_splice( $lObject["step"], $index, 0, array($thisStep));
					$this->language_model->update_by_id($lObject['id'], $lObject);
				}

			}

			return $guide;
		}else{
			return false;
		}
	}

    /**
     * Set Guide Metadata for Contextual Tips
     * @param $guide
     * @param $index
     */
    public function set_contextual(&$guide, $index, $contextual){

        //Set
        if(!isset($guide['contextual'])) $guide['contextual'] = array();

        //Remove current
        if(($key = array_search($index, $guide['contextual'])) !== false) unset($guide['contextual'][$key]);

        //Add new
        if($contextual) array_push($guide['contextual'], $index);

        return $guide;
    }

	/**
	 * Update a step with new data
	 * @param integer $index step index
	 * @param array $data data
	 * @return array $guide
	 */
	public function update($index, $data)
	{
		unset($data['id']);
		unset($data['host']);
        unset($data['locale']);

		$guide = $this->guide_model->get_by_id($this->id);

        // @todo allow multiple restrict
        $guide['restrict'] = array();
        if(isset($data['isRestrict']) && $data['isRestrict']) $guide['restrict']['s' . $index] = $data['restrictColor'];

        $guide = $this->set_contextual($guide, $index, isset($data['contextual']));

		if($guide){
			$guide["step"][$index] = $data;

			//Update the DB
			$this->save($guide);

			return $guide;
		}else{
			return false;
		}
	}


	/**
	 * Moves a step to a new index position
	 * @param integer $from
	 * @param integer $to
	 * @return array $guide
	 */
	public function moveIndex($from, $to)
	{
		$guide = $this->guide_model->get_by_id($this->id);

		// Get all stored language objects
		$languageObjects = $this->language_model->get_all_by_id($this->id);

		if($guide){

			$out = array_splice($guide["step"], $from, 1);
			array_splice($guide["step"], $to, 0, $out);

			//Update the DB
			$this->save($guide);

			//If language objects then move the step to the new position
			if (sizeof($languageObjects) > 0){
				foreach ($languageObjects as &$lObject) {
					$outPosition = array_splice($lObject["step"], $from, 1);
					array_splice($lObject["step"], $to, 0, $outPosition);
					$this->language_model->update_by_id($lObject['id'], $lObject);
				}

			}

			return $guide;

		}else{
			return false;
		}
	}

	/**
	 * Delete a step by index
	 * @param array $order_by order of tours
	 * @return array $guides
	 */
	public function deleteAt($index)
	{
		$guide = $this->guide_model->get_by_id($this->id);

		// Get all stored language objects
		$languageObjects = $this->language_model->get_all_by_id($this->id);

		if($guide){

			unset($guide["step"][$index]);

			$guide["step"] = array_values($guide["step"]);

			//Update the DB
			$this->save($guide);

			// If language objects then delete the step from each object
			if (sizeof($languageObjects) > 0){
				foreach ($languageObjects as &$lObject) {
					unset($lObject["step"][$index]);
					$lObject["step"] = array_values($lObject["step"]);
					$this->language_model->update_by_id($lObject['id'], $lObject);
				}

			}

			return $guide;

		}else{
			return false;
		}
	}

	private function save($guide){

		$this->guide_model->update_cache();
        $guide['version'] = $this->version_model->get_current_version($this->id) + 1;

        //Update language content
        if($this->locale != $this->config->item("language")){
            $languageContent['step'] = array();

            foreach($guide['step'] as &$step){
                $stepLang['title'] = $step['title'];
                $stepLang['body'] = $step['body'];
                array_push($languageContent['step'], $stepLang);

                unset($step['title']);
                unset($step['body']);
            }

            $languageContent['title'] = $guide['title'];
            $languageContent['desc'] = $guide['desc'];

            $this->language_model->update_by_guideid($this->id, $this->locale, $languageContent);
        }else {

            //Update the DB
            $success = $this->mongo_db
                ->where(array('_id' => new MongoId($this->id)))
                ->set((array)$guide)
                ->update($this->collection);

            $this->version_model->update($this->id, $guide);
        }

		return true;

	}
}
