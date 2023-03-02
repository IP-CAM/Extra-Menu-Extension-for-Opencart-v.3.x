<?php
class ModelExtensionModuleExtraMenu extends Model {
	public function install() {
		// box tables
		$this->db->query("
			CREATE TABLE `" . DB_PREFIX . "extra_menu` (
				`menu_id` INT(11) NOT NULL AUTO_INCREMENT,
				`parent` INT(11) NOT NULL default 0,
				`position` ENUM('before', 'after') NOT NULL default 'after',
				`page` VARCHAR(32) NOT NULL default '',
				`params` VARCHAR(255) NOT NULL default '',
				`sort_order` INT(11) NOT NULL default 0,
				PRIMARY KEY (`menu_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");
		$this->db->query("
			CREATE TABLE `" . DB_PREFIX . "extra_menu_description` (
				`menu_id` INT(11) NOT NULL default 0,
				`language_id` INT(11) NOT NULL default 0,
				`name` varchar(64) NOT NULL,
				PRIMARY KEY (`menu_id`,`language_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");
		
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "extra_menu`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "extra_menu_description`");
	}

	
	public function saveMenus() {
		if(isset($this->request->post['menu_before'])) foreach($this->request->post['menu_before'] as $menu) {
			$this->db->query("insert into ".DB_PREFIX."extra_menu set parent = 0, position = 'before', page = '".$this->db->escape($menu['page'])."', params = '".$this->db->escape($menu['params'])."', sort_order = ".(int)$menu['sort_order']);
			$id = $this->db->getLastId();
			foreach($menu['name'] as $language_id => $value) {
				$this->db->query("insert into ".DB_PREFIX."extra_menu_description set menu_id = ".(int)$id.", language_id = ".(int)$language_id.", name = '".$this->db->escape($value)."'");
			}
			if(isset($menu['children']) && is_array($menu['children'])) foreach($menu['children'] as $child) {
				$this->db->query("insert into ".DB_PREFIX."extra_menu set parent = ".(int)$id.", position = 'before', page = '".$this->db->escape($child['page'])."', params = '".$this->db->escape($child['params'])."', sort_order = ".(int)$child['sort_order']);
				$cid = $this->db->getLastId();
				foreach($child['name'] as $language_id => $value) {
					$this->db->query("insert into ".DB_PREFIX."extra_menu_description set menu_id = ".(int)$cid.", language_id = ".(int)$language_id.", name = '".$this->db->escape($value)."'");
				}
			}
		}
		if(isset($this->request->post['menu_after'])) foreach($this->request->post['menu_after'] as $menu) {
			$this->db->query("insert into ".DB_PREFIX."extra_menu set parent = 0, position = 'after', page = '".$this->db->escape($menu['page'])."', params = '".$this->db->escape($menu['params'])."', sort_order = ".(int)$menu['sort_order']);
			$id = $this->db->getLastId();
			foreach($menu['name'] as $language_id => $value) {
				$this->db->query("insert into ".DB_PREFIX."extra_menu_description set menu_id = ".(int)$id.", language_id = ".(int)$language_id.", name = '".$this->db->escape($value)."'");
			}
			if(isset($menu['children']) && is_array($menu['children'])) foreach($menu['children'] as $child) {
				$this->db->query("insert into ".DB_PREFIX."extra_menu set parent = ".(int)$id.", position = 'after', page = '".$this->db->escape($child['page'])."', params = '".$this->db->escape($child['params'])."', sort_order = ".(int)$child['sort_order']);
				$cid = $this->db->getLastId();
				foreach($child['name'] as $language_id => $value) {
					$this->db->query("insert into ".DB_PREFIX."extra_menu_description set menu_id = ".(int)$cid.", language_id = ".(int)$language_id.", name = '".$this->db->escape($value)."'");
				}
			}
		}
	}

	public function deleteMenus() {
		$this->db->query('DELETE FROM '.DB_PREFIX.'extra_menu');
		$this->db->query('DELETE FROM '.DB_PREFIX.'extra_menu_description');
	}


	public function getMenus($type = 'before') {
	    $data = array();
		$result = $this->db->query("select * from ".DB_PREFIX."extra_menu where position = '".$this->db->escape($type)."' and parent = 0 ORDER BY sort_order ASC");
		foreach($result->rows as $row) {
			$children = array();
			$child = $this->db->query("select * from ".DB_PREFIX."extra_menu where parent = ".(int)$row['menu_id']." order by sort_order ASC");
			foreach($child->rows as $child_row) {
				$children[] = array(
					'menu_id' => $child_row['menu_id'],
					'page' => $child_row['page'],
					'params' => $child_row['params'],
					'sort_order' => $child_row['sort_order'],
					'name' => $this->getDescriptions($child_row['menu_id']),
				);
			}
			$data[] = array(
					'menu_id' => $row['menu_id'],
					'page' => $row['page'],
					'params' => $row['params'],
					'sort_order' => $row['sort_order'],
					'name' => $this->getDescriptions($row['menu_id']),
					'children' => $children,
			);
		}
		return $data;
	}
	
	public function getDescriptions($id) {
		$data = array();
		$result = $this->db->query("select * from ".DB_PREFIX."extra_menu_description where menu_id = ".(int)$id);
		foreach($result->rows as $row) {
			$data[$row['language_id']] = $row['name'];
		}
		return $data;
	}
}
