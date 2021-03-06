<?php
/*
 * WiND - Wireless Nodes Database
 *
 * Copyright (C) 2005-2014 	by WiND Contributors (see AUTHORS.txt)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class node_editor_link {

	var $tpl;
	
	function __construct() {
		
	}
	
	function form_link() {
		global $db, $vars;
		$form_link = new form(array('FORM_NAME' => 'form_link'));
		$form_link->db_data('links.type, links.peer_node_id, links.peer_ap_id, links.protocol, links.ssid, links.channel, links.frequency, links.status, links.due_date, links.equipment, links.info');
		$form_link->db_data_values("links", "id", get('link'));
		
		$form_link->db_data_pickup('links.peer_node_id', "nodes", $db->get("links.peer_node_id AS value, CONCAT(nodes.name, ' (#', nodes.id, ')') AS output", "links, nodes", "links.peer_node_id = nodes.id AND links.id = '".get("link")."'"));
		$form_link->db_data_pickup('links.peer_ap_id', "links_ap", $db->get("l1.peer_ap_id AS value, l2.ssid AS output", "links AS l1, links AS l2", "l1.peer_ap_id = l2.id AND l1.id = '".get("link")."'"));
		$form_link->data[1]['Null'] = '';
		$form_link->data[2]['Null'] = '';
		return $form_link;
	}
	
	function output() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && method_exists($this, 'output_onpost_'.$_POST['form_name']))
			return call_user_func(array($this, 'output_onpost_'.$_POST['form_name']));
		global $construct;
		$this->tpl['link_method'] = (get('link') == 'add' ? 'add' : 'edit' );
		$this->tpl['form_link'] = $construct->form($this->form_link(), __FILE__);
		return template($this->tpl, __FILE__);
	}

	function output_onpost_form_link() {
		global $main, $db;
		$form_link = $this->form_link();
		$link = get('link');
		$ret = TRUE;
		$f = array("node_id" => intval(get('node')));
		switch ($_POST['links__type']) {
			case 'p2p':
				$t = $db->get('id', 'nodes', "id = '".intval($_POST['links__peer_node_id'])."'");
				if (!isset($t[0]['id']) || $t[0]['id'] == intval(get('node'))) {
					$db->output_error_fields_required(array('links__peer_node_id'));
					return;
				}
				$f['peer_ap_id'] = '';
				$f['peer_node_id'] = intval($_POST['links__peer_node_id']);
				break;
			case 'client':
				$t = $db->get('id, node_id', 'links', "id = '".intval($_POST['links__peer_ap_id'])."'");
				if (!isset($t[0]['id']) || $t[0]['node_id'] == intval(get('node'))) {
					$db->output_error_fields_required(array('links__peer_ap_id'));
					return;
				}
				$f['peer_ap_id'] = intval($_POST['links__peer_ap_id']);
				$f['peer_node_id'] = '';
				break;
			case 'ap':
				$f['peer_node_id'] = '';
				$f['peer_ap_id'] = '';
				break;
			case 'free':
				$f['peer_node_id'] = '';
				break;
		}
                $Day = $_POST["CONDATETIME_links__due_date_Day"];
                $Month = $_POST["CONDATETIME_links__due_date_Month"];
                $Year = $_POST["CONDATETIME_links__due_date_Year"];
                $Hour = $_POST["CONDATETIME_links__due_date_Hour"];
                $Minute = $_POST["CONDATETIME_links__due_date_Minute"];
                $Second = $_POST["CONDATETIME_links__due_date_Second"];

                unset($_POST["CONDATETIME_links__due_date_Day"]);
                unset($_POST["CONDATETIME_links__due_date_Month"]);
                unset($_POST["CONDATETIME_links__due_date_Year"]);
                unset($_POST["CONDATETIME_links__due_date_Hour"]);
                unset($_POST["CONDATETIME_links__due_date_Minute"]);
                unset($_POST["CONDATETIME_links__due_date_Second"]);
                                                                
                $f['due_date'] = "$Year-$Month-$Day $Hour:$Minute:$Second";
		$ret = $form_link->db_set($f, "links", "id", $link);
		
		if ($ret) {
			$main->message->set_fromlang('info', 'insert_success', make_ref('/node_editor', array("node" => get('node'))));
		} else {
			$main->message->set_fromlang('error', 'generic');		
		}
	}

}

?>
