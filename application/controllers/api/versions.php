<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Aero Guides
 *
 * CRUD API services for Aero guides
 *
 * @package		Aero
 * @subpackage	Guides
 * @category	REST Controller
 * @author      Trevor Dell
 */
require APPPATH.'/libraries/REST_Controller.php';

class Versions extends REST_Controller
{
    function __construct()
    {
        // Construct our parent class
        parent::__construct();
        $this->load->model('guide_model', '', FALSE, $this->host);
        $this->load->model('version_model', '', FALSE, $this->host);
    }

    /**
     *  GET guide service call
     *  string $id Guide ID
     *  returns All versions
     */
    function index_get()
    {
        $id = $this->input->get('id');

        $guides = $this->version_model->get_all($id);

        $this->response($guides, $guides && count($guides) > 0 ? 200 : 400);
    }

    /**
     * POST - Restore from an older version
     * string $id Guide ID
     * int $version Version number
     * returns bool Success
     */
    function index_post()
    {
        $id = $this->request_data['id'];
        $version = $this->request_data['version'];

        $success = $this->version_model->restore($id, $version);
        $this->response($success, $success ? 200 : 400);
    }

    /**
     * DELETE - Permanently delete a version (or versions), or move all of them to trash
     * string id Guide ID
     * string[] version Version number(s), or leave out to move all versions to trash
     */
    function index_delete()
    {
        $success = $this->version_model->delete($this->delete('id'), $this->delete('version'));
        $this->response($success, $success ? 200 : 400);
    }


    function current_version_get()
    {
        $id = $this->input->get('id');

        $version = $this->version_model->get_current_version($id);
        $this->response(array('version' => $version), 200);

    }

    /**
     *  Get table data for admin
     */
    function table_get()
    {
        $id = $this->input->get('id');
        $rows["data"] = array();

        //Get all versions
        try
        {
            $versions = $this->version_model->get_all($id);
        }
        catch (Exception $e)
        {
            log_message('error', $e->getMessage());
            $this->response('Error retrieving versions for guide: '. $id, 400);
            return false;
        }

        //Get person
        $this->load->library('person', array('host' => $this->host));
        $acl = $this->person->acl;
        $user = $this->person->username;

        date_default_timezone_set($this->config->item('timezone'));

        //Build rows
        foreach($versions[0]['versions'] as $guide)
        {
            try {
                $title = $guide['title'];
                $desc = $guide['desc'];
                $active = $guide['active'] ? "Yes" : "No";
                $modifier = '';
                $modified = '';

                if (isset($guide['modifier']))
                    $modifier = $guide['modifier'];
                else
                    $modifier = $guide['creator'];


                if (isset($guide['modified']->sec)) {
                    $modified = explode(" ", date('y-m-d h:i:s', $guide['modified']->sec));
                    $modified = implode(' at ', $modified);
                } else {
                    $modified = explode(" ", date('y-m-d h:i:s', $guide['created']->sec));
                    $modified = implode(' at ', $modified);
                }


                $steps = sizeof($guide['step']);
                $version = $guide['version'];
            }
            catch (Exception $e)
            {
                log_message('error', $e->getMessage());
                $this->response('Error processing versions for guide: '. $id, 400);
                return false;
            }

            $tools = "<div class='tools' style='width:240px' data-id='$version'>";

            if($acl['guides']['edit'] || ($modifier == $user && $acl['guides']['create'])){
                $tools .= "<a class='small button success restore'>Restore<i class='ss-icon'>&#x1F4CC;</i></a>";
            }
            if($acl['guides']['delete'] || ($modifier == $user && $acl['guides']['create'])){
                $tools .= "<a class='small button alert delete'>Delete <i class='ss-icon'>&#xE0D0;</i></a>";
            }
            $tools .= "</div></li>";


            $row = array(
                "<input type='checkbox' class='select' value='1' data-id='$version' />",
                $version,
                $desc,
                $steps,
                $modifier . "<br/>" .$modified,
                $tools
            );

            array_push($rows['data'], $row);
        }
        $this->response($rows, count($rows) > 0 ? 200 : 400);
    }
}
