<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class App extends CI_Controller {

	public $data = array();

	/**
	 * Constructor
	*/
	function __construct()
	{
		parent::__construct();
		$this->lang->load('aero', $this->config->item('language'));
		$this->data['lang'] = (object) $this->lang->language;

		if (!isset($_SESSION['username'])){
			header("Location: /login");
			exit;
		}
	}

	/**
	 *  Index for apps
	 */
	public function index()
    {
        $this->load->library('person', array('host' => ''));

        $this->data['username'] = $this->person->username;
        $this->data['is_admin'] = in_array($this->config->item("administrator"), $this->person->roles);
        $this->data['baseUrl'] = base_url();
        $this->load->view('apps_view', $this->data);
    }

	/**
	 *  User management
	 */
	public function users()
	{
		$this->load->library('person', array('host' => ''));
		$this->data['acl'] = $this->person->acl;
		$this->data['username'] = $this->person->username;
		$this->data['baseUrl'] = base_url();

		if(in_array($this->config->item("administrator"), $this->person->roles)){
			$this->load->view('users_view', $this->data);
		}else{
			//No Access!
			$this->load->view('noaccess_view', $this->data);
		}
	}

    /**
     * Section views
     * @param string $host
     * @param string $view
     */
    public function section($host, $view)
    {
        $this->load->library('person', array('host' => $host));
        $acl = $this->person->acl;
        $aclSection = $view;
        if($aclSection == "blacklist" || $aclSection == "pagedata") $aclSection = 'config';

        $this->data['username'] = $this->person->username;
        $this->data['acl'] = $acl;
        $this->data['baseUrl'] = base_url();
        $this->data['host'] = $host;
        $this->data['view'] = $view;

        if ($view == 'trash' || $view == "features")
            $aclSection = 'guides';

        if(isset($acl[$aclSection]['read']) && $acl[$aclSection]['read']){
            $this->load->view( $view. '_view', $this->data);
        }else{
            //No Access!
            $this->load->view('noaccess_view', $this->data);
        }
    }

    /**
     * Sub section views
     * @param string $host
     * @param string $sub
     * @param string $view
     */
    public function subsection($host, $sub, $view)
    {
        $host = urldecode($host);
        $this->load->library('person', array('host' => $host));
        $acl = $this->person->acl;
        $aclSection = $view;

        //Permissions
        $this->data['username'] = $this->person->username;
        $this->data['acl'] = $acl;
        $this->data['baseUrl'] = base_url();
        $this->data['host'] = $host;
        $this->data['view'] = $view;
        $this->data['id'] = urldecode($sub);

        if($view == "rolemap")
        {
            $host = str_replace('.', '_', $host);
            $host = str_replace('://', '_', $host);

            $aclSection = "roles";
            $this->load->model('role_model', '', FALSE, $host);
            $role = $this->role_model->get_by_title(urldecode($sub), true);

            //ID and description
            $this->data['permissions'] = $role;
            $this->data['objid'] = $role['id'];
            $this->data['objdesc'] = $role['description'];
        }
        else if($view == "pathwaymap")
        {
            $aclSection = "pathways";
        }
        else if($view == "versions")
        {
            $aclSection = "guides";
        }
        else if($view == "language")
        {
            $aclSection = "guides";
        }
        else if($view == "pages")
        {
            $aclSection = "guides";

            $this->load->model('feature_model', '', FALSE, $host);
            $feature = $this->feature_model->get_by_title(urldecode($sub));
            $this->data['featureid'] = $feature['id'];
        }

        if(isset($acl[$aclSection]['read']) && $acl[$aclSection]['read']){
            $this->load->view( $view. '_view', $this->data);
        }else{
            //No Access!
            $this->load->view('noaccess_view', $this->data);
        }
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
