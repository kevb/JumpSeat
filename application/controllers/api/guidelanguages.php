<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Jumpseat Language Content
 *
 * CRUD API services for Jumpseat Guide language content
 *
 * @package		Jumpseat
 * @subpackage	Guide Languages
 * @category	REST Controller
 * @author		James Hansen
*/
require APPPATH.'/libraries/REST_Controller.php';

class GuideLanguages extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
        $this->load->model('language_model', '', FALSE, $this->host);
    }

     /**
     *  GET language content object service call
     */
    function index_get()
    {
        $id = $this->input->get('guideid');
        $language = (string)($this->input->get('lang'));

        if(isset($language) && $language != ''){
            $languageContent = $this->language_model->get_all_by_language($language);
        }elseif(isset($id) && $id != ''){
            $languageContent = $this->language_model->get_all_by_id($id);
        }else{
            $languageContent = $this->language_model->get_all();
        }

        $this->response($languageContent, 200);
    }

 	/**
     *  POST language object creation service call
     *  accepts guide id and language code
     *  requires the host
     */
    function index_post()
    {
    	$newLanguageContent = $this->language_model->create($this->request_data);
        $response_code = $newLanguageContent ? 200 : 400;
    	$this->response($newLanguageContent, $response_code);
    }

    /**
     *  PUT language object service call
     *  accepts an object that contains the locale content
     */
    function index_put()
    {
        $id = $this->request_data['id'];
        unset($this->request_data['id']);

        $updatedContent = $this->language_model->update_by_id($id, $this->request_data);
        $response_code = $updatedContent ? 201 : 400;
        $this->response($updatedContent, 201);
    }

    /**
     *  Delete language content 
     */
    function index_delete()
    {
        if ($this->delete('id') != null && $this->delete('id') != ''){
            $languageContent = $this->language_model->delete_by_id($this->delete('id'), false);
        }elseif($this->delete('guide_id') != null && $this->delete('guide_id') != ''){
            $languageContent = $this->language_model->delete_by_id($this->delete('guide_id'), true);
        }
        $this->response($languageContent, 200);
    }

}
