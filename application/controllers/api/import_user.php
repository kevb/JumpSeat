<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

/**
 * Controller for uploading users for import
 *
 * @package		Aero
 * @subpackage	export
 * @category	REST Controller
 * @author		Trevor Dell
*/
class Import_User extends REST_Controller
{
	private $model_name = '';
	private $collection = '';

	function __construct()
	{
		parent::__construct();

		$this->host = str_replace(".", "_", $_POST['host']);
		$this->host = str_replace('://', '_', $this->host);

		$this->collection = $this->host .'_' . GUIDES;
		$this->load->model('user_model', '', FALSE, $this->host);
        $this->load->model('role_model', '', FALSE, $this->host);
        $this->load->model('roleusermap_model', '', FALSE, $this->host);
	}

	/**
	 * Imports the users that were uploaded by user
	 */
	public function index_post()
	{
		$verifyToken = md5('unique_salt' . $_POST['timestamp']);

		if (!empty($_FILES) && (strcmp($_POST['token'], $verifyToken) == 0))
		{
			$users = file_get_contents($_FILES['Filedata']['tmp_name']);
			$users = json_decode($users);

			foreach ($users as $user)
			{
				try {
					$uid = $this->user_model->create((array) $user);

                    $host = str_replace(".", "_", $user->{'host'});
                    $host = str_replace('://', '_', $host);

                    // Has role been assigned?
                    if(array_key_exists('role', $user) && $user->{'role'} != ""){
                        $roles = explode(",", $user->{'role'});

                        foreach($roles as &$role) {

                            $role = trim($role);
                            // Go get the role
                            $foundRole = $this->role_model->get_by_title($role, false, $host);

                            // Add to existing or create new group
                            if($foundRole) {
                                $roleId = $foundRole['id'];
                            } else {
                                // Create roler
                                $newRole = array("title" => $role, "description" => "", 'host' => $host);
                                $roleId = $this->role_model->create($newRole, $host);
                            }

                            $this->roleusermap_model->create($roleId, $uid, $host);
                        }
                    }
				}
				catch(Exception $e){
					log_message('error', 'ZZT: Error importing user: ' . json_encode($user) );
				}
			}
		}

		$this->response(true, 200);
	}
}