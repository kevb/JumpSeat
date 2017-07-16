<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Export extends CI_Controller {

	public function index()
	{
		$model = $this->input->get('model');

		//Collection name
		$host = str_replace('.', '_', $this->input->get('host'));
		$host = str_replace('://', '_', $host);
		$ids = $this->input->get('ids');
		$ids = explode(',', $ids);

		//Load the model
		$this->load->model($model .'_model', '', FALSE, $host);

		//Call model export
		$data['export'] = $this->{$model.'_model'}->get_by_ids($ids);
		$data['model'] = $model;

		// Generate filename from timestamp
		$stamp = date('Y-m-d H:i:s');
		$data['filename'] = 'JumpSeat ' . ucwords($model) .'s ' . $stamp .'.'. $model;

		//Open the view
		$this->load->view('export_view', $data);
	}
}