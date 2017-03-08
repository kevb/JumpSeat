<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

/**
 * Controller for uploading guides for import
 *
 * @package		Aero
 * @subpackage	export
 * @category	REST Controller
 * @author		Trevor Dell
*/
class Import_Guide extends REST_Controller
{
	private $model_name = '';

	function __construct()
	{
		parent::__construct();

		$this->host = str_replace(".", "_", $_POST['host']);
		$this->host = str_replace('://', '_', $this->host);

		$this->load->model('guide_model', '', FALSE, $this->host);
	}

	/**
	 * Imports the guides that were uploaded by guide
	 */
	public function index_post()
	{
		$verifyToken = md5('unique_salt' . $_POST['timestamp']);

		if (!empty($_FILES) && (strcmp($_POST['token'], $verifyToken) == 0))
		{
			$guides = file_get_contents($_FILES['Filedata']['tmp_name']);
			$guides = json_decode($guides);

			foreach ($guides as $guide)
			{
				try {
					$this->guide_model->create((array) $guide);
				}
				catch(Exception $e){
					log_message('error', 'ZZT: Error importing guide: ' . json_encode($guide) );
				}
			}
		}

		$this->response(true, 200);
	}
}
?>
