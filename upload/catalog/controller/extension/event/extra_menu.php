<?php
class controllerExtensionEventExtraMenu extends Controller {
	
    public function view(&$route, &$data, &$output) {
        // check if this module is enabled
        if(!$this->active()) {
            return;
        }
		$this->load->model('extension/module/extra_menu');
		$data['extra_menu_before'] = $this->model_extension_module_extra_menu->getItems(true);
		$data['extra_menu_after'] = $this->model_extension_module_extra_menu->getItems(false);
		$data['extra_menu'] = $this->model_extension_module_extra_menu->itemCount();
		if($this->config->get('module_extra_menu_debug')) { 
			$this->log->write('Extra menu before:');
			$this->log->write($data['extra_menu_before']);
			$this->log->write('Extra menu after:');
			$this->log->write($data['extra_menu_after']);
			$this->log->write('Extra menu flag:');
			$this->log->write($data['extra_menu']);
		}
    }
    
    protected function active($page = 'ignore') {
	    if($this->config->get('module_extra_menu_status')) {
			return true;
	    }
	    return false;
    }
}
