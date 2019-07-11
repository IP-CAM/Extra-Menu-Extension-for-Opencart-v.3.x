<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ControllerEventExtraMenu extends Controller {
	
    public function view(&$route, &$data, &$output) {
        // check if this module is enabled
        if(!$this->active()) {
            return;
        }
		$this->load->model('extension/module/extra_menu');
		$data['extra_menu_before'] = $this->model_extension_module_extra_menu->getItems(true);
		$data['extra_menu_after'] = $this->model_extension_module_extra_menu->getItems(false);
		$data['extra_menu'] = $this->model_extension_module_extra_menu->itemCount();
		$this->log->write($data['extra_menu_before']);
    }
    
    protected function active($page = 'ignore') {
	    if($this->config->get('module_extra_menu_status')) {
			return true;
	    }
	    return false;
    }
}
