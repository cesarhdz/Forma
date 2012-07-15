<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

    public function index()
    {

        /*
         * Include the module, can be repplaced by __autoload function
         * or by previously loading a Model
         */
        include BASEPATH . '/core/Model.php';

        /*
         * Include form library, without making an instance
         * of the Class
         * It can be loaded via __autoload php function
         */
        include APPPATH . 'libraries/Forma.php';

        /*
         * loading form from as module seams more
         * accurate than loading from config
         */
        $this->load->model('forms/sample_form');
        
        /*
         * VarDump the form
         */
        echo '<h1>Showing Forma Model Obejct</h1>';
        echo '<pre>';
        var_dump($this->sample_form);

//		$this->data['forma'] = $this->config->item('forma_sample');
//
//		$this->load->view('welcome_message', $this->data);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */