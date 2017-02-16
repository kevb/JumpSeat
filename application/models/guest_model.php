<?
/**
*  Model that fetches all data associated with guides
*/
class Guest_Model extends CI_Model
{
	private $collection = null;

	/**
	 * Constructor
	*/
	function __construct()
	{
		// Call the Model constructor
		parent::__construct();
		$this->load->library('mongo_db');
		$this->collection = USERS;

        $this->load->model('user_model');
	}

	/**
	 *  TEST ONLY Login User
	 */
	public function login($username, $host = '')
    {
        $host = str_replace('.', '_', $host);
        $host = str_replace('://', '_', $host);

        //Guest
        if(isset($_SESSION['appuser']) || isset($_SESSION['sysadmin']) || $username == "guest@jumpseat.io") return;

        //Already logged in?
        if(isset($_SESSION['userid'])) return;

        // Guest flag
        if(!isset($_SESSION['sysadmin'])) $_SESSION['appuser']  = true;

        //RESET FOR APP USER
        session_destroy();
        session_start();

        //Does user already exist?
        $user = $this->mongo_db
            ->where(array('email' => $username))
            ->get($this->collection);

        //Create app user
        if (sizeof($user) <= 0) {
            $user = array(
                "firstname" => "App",
                "lastname" => "User",
                "email" => $username,
                "sysadmin" => false,
                "password" => "password",
                "timeslogin" => 0
            );

            $user['id'] = $this->user_model->create($user);
        } else {
            $user = $user[0];
        }

        $_SESSION['appuser']  = true;
        $_SESSION['username'] = $user['email'];
        $_SESSION['userid'] = $user['id'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
        $_SESSION['sysadmin'] = $user['sysadmin'];

        $this->load->library('person', array('host' => $host));
        $this->user_model->update_lastlogin($user['id'], $user['timeslogin']);

        return true;
    }
}
