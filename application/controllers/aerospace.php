<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AeroSpace extends CI_Controller {

	public $require = null;
    public $fire = null;
	public $language = array();

	/**
	 * Constructor acl
	 */
	function __construct()
	{
		parent::__construct();
		$this->lang->load('aero_front', $this->config->item('language'));
		$this->language = json_encode($this->lang->language);
	}

	/**
	 * JumpSeat injection
	 */
	public function index()
	{
		$data['lang'] = $this->language;
		$data['cache'] = 0;

        //REFER is not reliable
        if($this->input->get('ref')) {
            //Get the host name
            $referer = parse_url(urldecode($this->input->get('ref')));
        }else{
            //Get the host name
            $referer = parse_url($_SERVER['HTTP_REFERER']);
        }

		//Enable ports
		$port = (isset($referer['port']) && $referer['port'] != "80") ? ':' . $referer['port'] : '';
        $www = $referer["scheme"] . "://" . $referer['host'] . $port;
        $path = $referer['path'];

		//Load app model and get app
		$this->load->model("app_model");
		$app = $this->app_model->test_url($www, $path);

		if($app && $app['active']){

			$host = str_replace('.', '_', $app['host']);
			$host = str_replace('://', '_', $host);

			//Load Person and ACL
			$this->load->library('person', array('host' => $host));
			$data['admin'] = $this->person->acl['guides']['create'];
			$data['username'] = $this->person->username;

			//Check injection is enabled
			$isInject = $this->config->item("injection_enabled");

			//Set app data
			if($app){
				$data['cache'] = $app['version'];
				$data['app'] = $app['host'];
				$data['pagedata'] = $this->get_page_data($host);
				$data['require'] = $this->require;
                $data['fire'] = $this->fire;
                $data['rootLocale'] = $this->config->item("language");
			}

			//Inject JumpSeat
			$min = (MIN == ".min") ? "_min" : "";
			if($isInject && sizeof($app) != 0) $this->load->view('aerospace_view' . $min, $data);
		}else{
			$this->load->view('aerospace_empty_view', $data);
		}
	}


	/**
	 *  Get page data for host
	 */
	public function get_page_data($host)
	{
		$js = "";

		$this->load->model('pagedata_model', '', FALSE, $host);
		$pagedata = $this->pagedata_model->get_all();
		$urls = array();

		foreach ($pagedata as $data){

			$type = trim($data['type']);
			$prop = trim($data['prop']);
			$val = trim($data['value']);
			//var_dump($prop . " : " . $val);

			if($val != ""){

				if($type == "url"){
					array_push($urls, array(
						'regex' => $prop,
						'value' => $val
					));
				}elseif($prop == "require") {
                    $this->require = trim($data['value']);
                }elseif($prop == "fire"){
                    $this->fire = trim($data['value']);
				}else{
					$js .= trim($data['prop']) . " : (function(){ return " . trim($data['value']) . "}),";
				}
			}
		}

		$js .= 'urls : ' . json_encode($urls);

		return $js;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
