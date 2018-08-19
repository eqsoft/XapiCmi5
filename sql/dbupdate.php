<#1>
<?php
/**
 * Copyright (c) 2018 internetlehrer GmbH 
 * GPLv2, see LICENSE 
 */

/**
 * xApi plugin: database update script
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
 */ 

/**
 * Type definitions
 */
if(!$ilDB->tableExists('xxcf_data_types'))
{
	$types = array(
		'type_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'type_name' => array(
			'type' => 'text',
			'length' => 32
		),
		'title' => array(
			'type' => 'text',
			'length' => 255
		),
		'description' => array(
			'type' => 'text',
			'length' => 4000
		),
		'availability' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 1
		),
		'remarks' => array(
			'type' => 'text',
			'length' => 4000
		),
		'time_to_delete' => array(
			'type' => 'integer',
			'length' => 4
		),
		'log_level' => array(
			'type' => 'integer',
			'length' => 2,
			'notnull' => true,
			'default' => 0
		),
		'lrs_type_id' => array(
			'type' => 'integer',
			'length' => 2,
			'notnull' => true,
			'default' => 1
		),
		'lrs_endpoint' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		),
		'lrs_key' => array(
			'type' => 'text',
			'length' => 128,
			'notnull' => true
		),
		'lrs_secret' => array(
			'type' => 'text',
			'length' => 128,
			'notnull' => true
		),
		'privacy_ident' => array(
			'type' => 'integer',
			'length' => 2,
			'notnull' => true,
			'default' => 0
		),
		'privacy_name' => array(
			'type' => 'integer',
			'length' => 2,
			'notnull' => true,
			'default' => 0
		),
		'privacy_comment_default' => array(
			'type' => 'text',
			'length' => 2000,
			'notnull' => true
		),
		'external_lrs' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)		
	);
	$ilDB->createTable("xxcf_data_types", $types);
	$ilDB->addPrimaryKey("xxcf_data_types", array("type_id"));
	$ilDB->createSequence("xxcf_data_types");
}

?>
<#2>
<?php
/**
 * settings for xapi-objects
 */
if(!$ilDB->tableExists('xxcf_data_settings'))
{
	$settings = array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'type_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'instructions' => array(
			'type' => 'text',
			'length' => 4000
		),
		'availability_type' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'meta_data_xml' => array(
			'type' => 'clob'
		),
		'lp_mode' => array(
			'type' => 'integer',
			'length' => 2,
			'notnull' => true,
			'default' => 0
		),
		'lp_threshold' => array(
			'type' => 'float',
			'notnull' => true,
			'default' => 0.5
		),
		'launch_key' => array(
			'type' => 'text',
			'length' => 64,
			'notnull' => true
		),
		'launch_secret' => array(
			'type' => 'text',
			'length' => 64
		),
		'launch_url' => array(
			'type' => 'text',
			'length' => 64,
			'notnull' => true
		),
		'activity_id' => array(
			'type' => 'text',
			'length' => 64,
			'notnull' => true
		),
		'open_mode' => array (
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'width' => array (
			'type' => 'integer',
			'length' => 2,
			'notnull' => true,
			'default' => 950
		),
		'height' => array (
			'type' => 'integer',
			'length' => 2,
			'notnull' => true,
			'default' => 650
		),
		'show_debug' => array (
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'privacy_comment' => array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => true
		),
		'version' => array (
			'type' => 'integer',
			'length' => 2,
			'notnull' => true,
			'default' => 1
		)
	);

	$ilDB->createTable("xxcf_data_settings", $settings);
	$ilDB->addPrimaryKey("xxcf_data_settings", array("obj_id"));
}
?>
<#3>
<?php 
/**
 * table for detailed learning progress
 */
if(!$ilDB->tableExists('xxcf_results'))
{
	$values = array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
		),
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
		),
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
		),
		'version' => array (
			'type' => 'integer',
			'length' => 2,
			'notnull' => true,
			'default' => 1
		),
		'result' => array(
			'type' => 'float',
			'notnull' => false,
		),
		'status' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'time' => array(
			'type' => 'timestamp',
			'notnull' => true,
			'default' => ''
		)
	);
	$ilDB->createTable("xxcf_results", $values);
	$ilDB->addPrimaryKey("xxcf_results", array("id"));
	$ilDB->createSequence("xxcf_results");
	$ilDB->addIndex("xxcf_results", array("obj_id","usr_id"), 'i1', false);
}
?>
<#4>
<?php
/**
 * table for user mapping ILIAS-LRS
 */
if(!$ilDB->tableExists('xxcf_user_mapping'))
{
	$values = array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'lrs_name' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
			'default' => ''
		),
		'lrs_mail' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true
		)
	);
	$ilDB->createTable("xxcf_user_mapping", $values);
	$ilDB->addPrimaryKey("xxcf_user_mapping", array("obj_id","usr_id"));
}
?>
<#5>
<?php
/**
 * table token for auth
 */
if(!$ilDB->tableExists('xxcf_data_token'))
{
	$token = array(
		'token' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
			'default' => 0
		),
		'time' => array(
			'type' => 'timestamp',
			'notnull' => true,
			'default' => ''
		)
	);
	$ilDB->createTable("xxcf_data_token", $token);
	$ilDB->addPrimaryKey("xxcf_data_token", array("token", "time"));
}
?>
<#6>
<?php
/**
 * 
 */ 
	ilUtil::makeDirParents(ilUtil::getWebspaceDir().'/xxcf/cache');
?>
<#7>
<?php
/**
 * Check whether type exists in object data, if not, create the type
 * The type is normally created at plugin activation, see ilRepositoryObjectPlugin::beforeActivation()
 */
	$set = $ilDB->query("SELECT obj_id FROM object_data WHERE type='typ' AND title = 'xxcf'");
	if ($rec = $ilDB->fetchAssoc($set))
	{
		$typ_id = $rec["obj_id"];
	}
	else
	{
		$typ_id = $ilDB->nextId("object_data");
		$ilDB->manipulate("INSERT INTO object_data ".
			"(obj_id, type, title, description, owner, create_date, last_update) VALUES (".
			$ilDB->quote($typ_id, "integer").",".
			$ilDB->quote("typ", "text").",".
			$ilDB->quote("xxcf", "text").",".
			$ilDB->quote("Plugin xAPI", "text").",".
			$ilDB->quote(-1, "integer").",".
			$ilDB->quote(ilUtil::now(), "timestamp").",".
			$ilDB->quote(ilUtil::now(), "timestamp").
			")");
	}

?>
<#8>
<?php
/**
* Add new RBAC operations
*/
	$set = $ilDB->query("SELECT obj_id FROM object_data WHERE type='typ' AND title = 'xxcf'");
	$rec = $ilDB->fetchAssoc($set);
	$typ_id = $rec["obj_id"];

	$operations = array('edit_learning_progress','read_learning_progress');
	foreach ($operations as $operation)
	{
		$query = "SELECT ops_id FROM rbac_operations WHERE operation = ".$ilDB->quote($operation, 'text');
		$res = $ilDB->query($query);
		$row = $ilDB->fetchObject($res);
		$ops_id = $row->ops_id;
		
		$query = "DELETE FROM rbac_ta WHERE typ_id=".$ilDB->quote($typ_id, 'integer')." AND ops_id=".$ilDB->quote($ops_id, 'integer');
		$ilDB->manipulate($query);

		$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES ("
		.$ilDB->quote($typ_id, 'integer').","
		.$ilDB->quote($ops_id, 'integer').")";
		$ilDB->manipulate($query);
	}

?>
<#9>
<?php
	$ilDB->modifyTableColumn('xxcf_data_settings','launch_key', array(
			'type' => 'text',
			'length' => 64,
			'notnull' => false)
	);
	$ilDB->modifyTableColumn('xxcf_data_settings','launch_url', array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false)
	);
	$ilDB->modifyTableColumn('xxcf_data_settings','activity_id', array(
			'type' => 'text',
			'length' => 128,
			'notnull' => false)
	);
	$ilDB->modifyTableColumn('xxcf_data_settings','privacy_comment', array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => false)
	);
?>
<#10>
<?php
	if ( !$ilDB->tableColumnExists('xxcf_data_token', 'obj_id') ) {
		$ilDB->addTableColumn('xxcf_data_token', 'obj_id', array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
		));
	}
	if ( !$ilDB->tableColumnExists('xxcf_data_token', 'usr_id') ) {
		$ilDB->addTableColumn('xxcf_data_token', 'usr_id', array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true,
				'default' => 0
		));
	}

?>
<#11>
<?php
	if ( !$ilDB->tableColumnExists('xxcf_data_settings', 'use_fetch') ) {
		$ilDB->addTableColumn('xxcf_data_settings', 'use_fetch', array(
				'type' => 'integer',
				'length' => 1,
				'notnull' => true,
				'default' => 1
		));
	}
?>