<?php
require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

$list_def_file = 'list/backup_stats.list.php';

/******************************************
* End Form configuration
******************************************/

//* Check permissions for module
$app->auth->check_module_permissions('sites');

$app->load('listform_actions','functions');

class list_action extends listform_actions {

	public function prepareDataRow($rec)
	{
		global $app;

		$rec = parent::prepareDataRow($rec);

		$rec['active'] = "<div id=\"ir-Yes\" class=\"swap\"><span>Yes</span></div>";
		if ($rec['backup_interval'] === 'none') {
			$rec['active']        = "<div class=\"swap\" id=\"ir-No\"><span>No</span></div>";
			$rec['backup_copies'] = 0;
		}

		$recBackup = $app->db->queryOneRecord('SELECT COUNT(backup_id) AS backup_count FROM web_backup WHERE parent_domain_id = ?', $rec['domain_id']);
		$rec['backup_copies_exists'] = $recBackup['backup_count'];

		return $rec;
	}
}

$list = new list_action;
$list->SQLExtWhere = "";
$list->onLoad();