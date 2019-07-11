<?php
class ModelExtensionModuleExtraMenu extends Model {
	public function getItems($before = false) {
		$data = array();
		$sql = "SELECT * FROM `" . DB_PREFIX . "extra_menu` m join `".DB_PREFIX."extra_menu_description` d on m.menu_id = d.menu_id where m.parent = 0 and d.language_id = ".(int)$this->config->get('config_language_id');
		if($before) {
			$sql .= " AND position = 'before'";
		} else {
			$sql .= " AND position = 'after'";
		}
		$sql .= " ORDER BY sort_order ASC";
		
		$query = $this->db->query($sql);
		foreach($query->rows as $row) {
			// Level 2
			$sql = "SELECT * FROM `".DB_PREFIX."extra_menu` m join `".DB_PREFIX."extra_menu_description` d on m.menu_id = d.menu_id where parent = ".(int)$row['menu_id']." ORDER BY sort_order ASC";
			$children = $this->db->query($sql);
			$children_data = array();

			foreach ($children->rows as $child) {

				$children_data[] = array(
					'name'  => $child['name'],
					'href'  => ($child['page']?$this->url->link($child['page'], $child['params']):'')
				);
			}

			// Level 1
			$data[] = array(
				'name'     => $row['name'],
				'children' => $children_data,
				'href'     => ($row['page']?$this->url->link($row['page'], $row['params']):'')
			);
		}
		return $data;
	}
	
	public function itemCount() {
		$result = $this->db->query("SELECT count(*) as `count` FROM `" . DB_PREFIX . "extra_menu` m join `".DB_PREFIX."extra_menu_description` d on m.menu_id = d.menu_id where m.parent = 0 and d.language_id = ".(int)$this->config->get('config_language_id'));
		return $result->row['count'];
	}
	
}
