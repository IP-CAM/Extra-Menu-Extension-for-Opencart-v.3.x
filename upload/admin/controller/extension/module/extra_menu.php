<?php
class ControllerExtensionModuleExtraMenu extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/extra_menu');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_extra_menu', $this->request->post);
			// now save menus
			$this->load->model('extension/module/extra_menu');
			// first get all id's to check for deleted
			$this->model_extension_module_extra_menu->deleteMenus();
			// save
			$this->model_extension_module_extra_menu->saveMenus();
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/extra_menu', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/extra_menu', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		if (isset($this->request->post['module_extra_menu_debug'])) {
			$data['module_extra_menu_debug'] = $this->request->post['module_extra_menu_debug'];
		} else {
			$data['module_extra_menu_debug'] = $this->config->get('module_extra_menu_debug');
		}

		if (isset($this->request->post['module_extra_menu_status'])) {
			$data['module_extra_menu_status'] = $this->request->post['module_extra_menu_status'];
		} else {
			$data['module_extra_menu_status'] = $this->config->get('module_extra_menu_status');
		}

		if(isset($this->request->post['menu_before'])) {
			$data['menu_before'] = $this->request->post['menu_before'];
		} else {
			$this->load->model('extension/module/extra_menu');
			$data['menu_before'] = $this->model_extension_module_extra_menu->getMenus('before');
		}

		if(isset($this->request->post['menu_after'])) {
			$data['menu_after'] = $this->request->post['menu_after'];
		} else {
			$this->load->model('extension/module/extra_menu');
			$data['menu_after'] = $this->model_extension_module_extra_menu->getMenus('after');
		}

		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/extra_menu', $data));
	}
	
	public function install() {
		$this->load->model('extension/module/extra_menu');
		$this->model_extension_module_extra_menu->install();
		// add event triggers
		$this->load->model('setting/event');
		$this->model_setting_event->addEvent('extra_menu','catalog/view/common/menu/before','event/extra_menu/view');
	}
	
	public function uninstall() {
		$this->load->model('extension/module/extra_menu');
		$this->model_extension_module_extra_menu->uninstall();
		// remove event triggers
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('extra_menu');
	}

	protected function validate() {
		//$this->log->write($this->request->post);
		if (!$this->user->hasPermission('modify', 'extension/module/extra_menu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		foreach ($this->request->post['menu_before'] as $menu) {
			foreach($menu['name'] as $language_id => $value) {
				if ((utf8_strlen($value) < 1) || (utf8_strlen($value) > 64)) 
					$this->error['name'] = $this->language->get('error_name');
			}
		}
		foreach ($this->request->post['menu_after'] as $menu) {
			foreach($menu['name'] as $language_id => $value) {
				if ((utf8_strlen($value) < 1) || (utf8_strlen($value) > 64)) 
					$this->error['name'] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}
}