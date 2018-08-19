<?php
/**
 * Copyright (c) 2018 internetlehrer GmbH
 * GPLv2, see LICENSE 
 */
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/classes/class.ilXapiCmi5Type.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/classes/class.ilXapiCmi5Encodings.php');

require_once 'Services/Tracking/interfaces/interface.ilLPStatusPlugin.php';

/**
 * xApi plugin: base class for repository object
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
 */
class ilObjXapiCmi5 extends ilObjectPlugin implements ilLPStatusPluginInterface
{

	const ACTIVATION_OFFLINE = 0;
	const ACTIVATION_UNLIMITED = 1;

	const LP_INACTIVE = 0;
	const LP_ACTIVE = 1;

	/**
	 * Content Type definition (object)
	 */
	var $typedef;

	/**
	 * Fields for filling template (list of field arrays)
	 */
	protected $fields;
	protected $availability_type;
	protected $type_id;
	protected $instructions;
	protected $meta_data_xml;
	protected $context = null;
	protected $lp_mode = self::LP_INACTIVE;
	protected $lp_threshold = 0.5;
	protected $show_debug = 0;
	protected $use_fetch = 0;
	protected $privacy_ident = ilXapiCmi5Type::PRIVACY_IDENT_EMAIL;
	protected $privacy_name;

	/**
	 * Return URL: This is a run-time variable set by the GUI and not stored
	 * @var string
	 */
	protected $return_url;

	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct($a_id = 0, $a_call_by_reference = true) {
		global $ilDB;

		parent::__construct($a_id, $a_call_by_reference);

		$this->db = $ilDB;
		$this->typedef = new ilXapiCmi5Type();
	}

	/**
	 * Get type.
	 * The initType() method must set the same ID as the plugin ID.
	 *
	 * @access	public
	 */
	final public function initType() {
		$this->setType('xxcf');
	}

	/**
	 * Set instructions
	 *
	 * @param string instructions
	 */
	public function setInstructions($a_instructions) {
		$this->instructions = $a_instructions;
	}

	/**
	 * Get instructions
	 */
	public function getInstructions() {
		return $this->instructions;
	}

	/**
	 * Set Type Id
	 *
	 * @param int type id
	 */
	public function setTypeId($a_type_id) {
		if ($this->type_id != $a_type_id) {
			$this->typedef = new ilXapiCmi5Type($a_type_id);
			$this->type_id = $a_type_id;
		}
	}

	/**
	 * Get Type Id
	 */
	public function getTypeId() {
		return $this->type_id;
	}

	/**
	 * Set vailability type
	 *
	 * @param int availability type
	 */
	public function setAvailabilityType($a_type) {
		$this->availability_type = $a_type;
	}

	/**
	 * get availability type
	 */
	public function getAvailabilityType() {
		return $this->availability_type;
	}

	/**
	 * get a text telling the availability
	 */
	public function getAvailabilityText() {
		global $lng;

		switch ($this->availability_type) {
			case self::ACTIVATION_OFFLINE:
				return $lng->txt('offline');

			case self::ACTIVATION_UNLIMITED:
				return $lng->txt('online');
		}
		return '';
	}

	/**
	 * Set meta data as xml structure
	 *
	 * @param int availability type
	 */
	public function setMetaDataXML($a_xml) {
		$this->meta_data_xml = $a_xml;
	}

	/**
	 * get meta data as xml structure
	 */
	public function getMetaDataXML() {
		return $this->meta_data_xml;
	}


	/**
	 * Get online status
	 */
	public function getOnline() {
		switch ($this->availability_type) {
			case self::ACTIVATION_UNLIMITED:
				return true;

			case self::ACTIVATION_OFFLINE:
				return false;

			default:
				return false;
		}
	}

	public function setLaunchUrl($a_launch_url) {
		$this->launch_url = $a_launch_url;
	}
	
	public function getLaunchUrl() {
		return $this->launch_url;
	}


	public function setActivityId($a_activity_id) {
		$this->activity_id = $a_activity_id;
	}
	
	public function getActivityId() {
		return $this->activity_id;
	}


	public function setLaunchKey($a_launch_key) {
		$this->launch_key = $a_launch_key;
	}
	
	public function getLaunchKey() {
		return $this->launch_key;
	}


	public function setLaunchSecret($a_launch_secret) {
		$this->launch_secret = $a_launch_secret;
	}
	
	public function getLaunchSecret() {
		return $this->launch_secret;
	}

	public function setShowDebug($a_show_debug) {
		$this->show_debug = $a_show_debug;
	}
	
	public function getShowDebug() {
		return $this->show_debug;
	}
	

	public function setUseFetch($a_use_fetch) {
		$this->use_fetch = $a_use_fetch;
	}

	public function getUseFetch() {
		return $this->use_fetch;
	}
	

	public function setPrivacyIdent($a_option)
	{
		$this->privacy_ident = $a_option;
	}
	
	public function getPrivacyIdent()
	{
		return $this->privacy_ident;
	}


	public function setPrivacyName($a_option)
	{
		$this->privacy_name = $a_option;
	}
	
	public function getPrivacyName()
	{
		return $this->privacy_name;
	}

	// /**
	 // * set a return url for coming back from the content
	 // * 
	 // * @param string	return url
	 // */
	// public function setReturnUrl($a_return_url) {
		// $this->return_url = $a_return_url;
	// }

	// /**
	 // * get a return url for coming back from the content
	 // * 
	 // * @return string	return url
	 // */
	// public function getReturnUrl() {
		// return $this->return_url;
	// }

	// /**
	 // * get the URL to lauch the assessment
	 // *
	 // * @access public
	 // */
	// public function getLaunchLink() {
		// return $this->fillTemplateRec($this->typedef->getTemplate());
	// }

	// /**
	 // * get the code to embed the object on a page
	 // *
	 // * @access public
	 // */
	// public function getEmbedCode() {
		// return $this->fillTemplateRec($this->typedef->getTemplate());
	// }

	// /**
	 // * get the code of a page to show
	 // *
	 // * @access public
	 // */
	// public function getPageCode() {
		// return $this->fillTemplateRec($this->typedef->getTemplate());
	// }


	// /**
	 // * Fill a template recursively with field values
	 // * Placeholders are like {FIELD_NAME}
	 // * Replacement is case insensitive
	 // *
	 // * @param	string  template
	 // * @param	int	 maximum recursion depth (default 100, stops at 0)
	 // */
	// private function fillTemplateRec($a_template, $a_maxdepth = 100) {
		// $this->initFields();
        
        // return;
        
		// foreach ($this->fields as $name => $field) {
			// $pattern = $this->typedef->getPlaceholder($name);
			// if (strpos($a_template, $pattern) !== false) {
				// $value = $this->fillField($field, $a_maxdepth);

				// // replace the placeholder in the template
				// $a_template = str_replace($pattern, $value, $a_template);
			// }
		// }
		// return $a_template;
	// }

	// /**
	 // * Fill a field and return its value
	 // * 
	 // * @param	array	field
	 // * @param	int		maximum recoursion depth
	 // * @return 	mixed	field value (depending on type)
	 // */
	// private function fillField($a_field, $a_maxdepth = 100) {
		// // check recursion or existing values   	
		// if (0 > $a_maxdepth--) {
			// return 'max depth reached!';
		// } elseif (isset($a_field['field_value'])) {
			// //echo "<br />FOUND: ".  $a_field['field_name'] . " = ";
			// //var_dump($a_field['field_value']);

			// return $a_field['field_value'];
		// }

		// // get field values that are not yet known
		// switch ($a_field['field_type']) {
			// case ilXapiCmi5Type::FIELDTYPE_ILIAS:
				// $value = $this->fillIliasField($a_field);
				// break;

			// case ilXapiCmi5Type::FIELDTYPE_CALCULATED:
				// $value = $this->fillCalculatedField($a_field, $a_maxdepth);
				// break;

			// case ilXapiCmi5Type::FIELDTYPE_TEMPLATE:
				// $value = $this->fillTemplateRec($a_field['template'], $a_maxdepth);
				// break;
		// }

		// // apply an encoding to the value
		// $value = ilXapiCmi5Encodings::_applyEncoding($a_field['encoding'], $value);


		// // save the value so that it is not re-calculated
		// $this->fields[$a_field['field_name']]['field_value'] = $value;

		// //echo "<br />FILLED: ".  $a_field['field_name'] . " = ";
		// //var_dump($value);

		// return $value;
	// }

	// /**
	 // * Apply a function with parameters to fill a field
	 // * 
	 // * @param $a_field
	 // * @param $a_maxdepth
	 // * @return unknown_type
	 // */
	// private function fillCalculatedField($a_field, $a_maxdepth) {
		// // process the function parameters
		// $parsed_params = array();
		// foreach ($a_field['params'] as $param_name => $param_value) {
			// foreach ($this->fields as $field_name => $field) {
				// if ($param_value == $this->typedef->getPlaceholder($field_name)) {
					// $param_value = $this->fillField($field, $a_maxdepth);
				// }

				// $parsed_params[$param_name] = $param_value;
			// }
		// }

		// // apply the function
		// require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/classes/class.ilXapiCmi5Functions.php");
		// $value = ilXapiCmi5Functions::applyFunction($a_field['function'], $parsed_params);

		// // save the value so that it is not re-calculated
		// $this->fields[$a_field['field_name']]['field_value'] = $value;

		// return $value;
	// }

	/**
	 * create an access token
	 * 
	 * @param $a_field
	 * @return unknown_type
	 */
	// private function fillToken($a_field) {
	public function fillToken() {
		$seconds = $this->getTimeToDelete();
		$result = $this->selectCurrentTimestamp();
		$time = new ilDateTime($result['CURRENT_TIMESTAMP'], IL_CAL_DATETIME);

		$timestamp = $time->get(IL_CAL_UNIX);
		$new_timestamp = $timestamp + $seconds;

		$value = $this->createToken($timestamp);

		$time_to_db = new ilDateTime($new_timestamp, IL_CAL_UNIX);

		//Insert new token in DB
		$this->insertToken($value, $time_to_db->get(IL_CAL_DATETIME));

		//delete old tokens
		$this->deleteToken($timestamp);

		return $value;
	}

	/**
	 * fill an ILIAS field
	 * @param $a_field
	 * @return unknown_type
	 */
	private function fillIliasField($a_field) {
		global $ilias, $ilUser, $ilSetting, $ilAccess, $ilClientIniFile;

		switch ($a_field['field_name']) {
			// object information

			case "ILIAS_REF_ID":
				$value = $this->getRefId();
				break;

			case "ILIAS_TITLE":
				$value = $this->getTitle();
				break;

			case "ILIAS_DESCRIPTION":
				$value = $this->getDescription();
				break;

			case "ILIAS_INSTRUCTIONS":
				$value = $this->getInstructions();
				break;

			// object context	

			case "ILIAS_CONTEXT_ID":
				$context = $this->getContext();
				$value = $context['id'];
				break;

			case "ILIAS_CONTEXT_TYPE":
				$context = $this->getContext();
				$value = $context['type'];
				break;

			case "ILIAS_CONTEXT_TITLE":
				$context = $this->getContext();
				$value = $context['title'];
				break;

			// call-time imformation

			// UK entfernt
			// case "ID":
				// $value = $this->selectID();
				// break;

			case "ILIAS_REMOTE_ADDR":
				$value = $_SERVER["REMOTE_ADDR"];
				break;

			case "ILIAS_TIME":
				$value = date('Y-m-d H:i:s', time());
				break;

			case "ILIAS_TIMESTAMP":
				$value = time();
				break;

			case "ILIAS_SESSION_ID":
				$value = session_id();
				break;

			case "ILIAS_TOKEN":
				$value = $this->fillToken($a_field);
				break;

			case "ILIAS_RESULT_ID":
				if ($this->getLPMode() == self::LP_ACTIVE)
				{
					$this->plugin->includeClass('class.ilXapiCmi5Result.php');
					$result = ilXapiCmi5Result::getByKeys($this->getId(), $ilUser->getId(), true);
					$value = $result->id;
				}
				else
				{
					$value= "";
				}
				break;

			// service urls
			case "ILIAS_CALLBACK_URL":
				$value = ILIAS_HTTP_PATH . "/Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/callback.php";
				break;

			case "ILIAS_EVENT_LOG_URL":
				$value = ILIAS_HTTP_PATH . "/Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/event_log.php";
				break;

			// case "ILIAS_RETURN_URL":
				// $value = $this->getReturnUrl();
				// break;

			case "ILIAS_RESULT_URL":
				if ($this->getLPMode() == self::LP_ACTIVE)
				{
					$value = ILIAS_HTTP_PATH . "/Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/result.php"
						. '?client_id='.CLIENT_ID;
				}
				else
				{
					$value = '';
				}
				break;

			// user information			

			case "ILIAS_USER_ID":
				$value = $ilUser->getId();
				break;

			case "ILIAS_USER_CODE":
				$value = sha1($ilUser->getId() . $ilUser->getCreateDate());
				break;

			case "ILIAS_USER_LOGIN":
				$value = $ilUser->getLogin();
				break;

			case "ILIAS_USER_FIRSTNAME":
				$value = $ilUser->getFirstname();
				break;

			case "ILIAS_USER_LASTNAME":
				$value = $ilUser->getLastname();
				break;

			case "ILIAS_USER_FULLNAME":
				$value = $ilUser->getFullname();
				break;

			case "ILIAS_USER_EMAIL":
				$value = $ilUser->getEmail();
				break;

			case "ILIAS_USER_IMAGE":
				$value = ILIAS_HTTP_PATH . "/" . $ilUser->getPersonalPicturePath("small");
				break;

			case "ILIAS_USER_LANG":
				$value = $this->lng->getLangKey();
				break;

			case "ILIAS_USER_WRITE_ACCESS":
				$value = $ilAccess->checkAccess('write', '', $this->getRefId()) ? "1" : "0";
				break;

			// platform information

			case "ILIAS_VERSION":
				$value = $ilias->getSetting("ilias_version");
				break;

			case "ILIAS_CONTACT_EMAIL":
				$value = $ilSetting->get("admin_email");
				break;

			case "ILIAS_CLIENT_ID":
				$value = CLIENT_ID;
				break;

			case "ILIAS_HTTP_PATH":
				$value = ILIAS_HTTP_PATH;
				break;

			case "ILIAS_LMS_URL":
				require_once ('./Services/Link/classes/class.ilLink.php');
				$value = ilLink::_getLink(ROOT_FOLDER_ID, "root");
				break;

			case "ILIAS_LMS_GUID":
				$parsed = parse_url(ILIAS_HTTP_PATH);
				$value = CLIENT_ID . "." . implode(".", array_reverse(explode("/", $parsed["path"]))) . $parsed["host"];
				break;

			case "ILIAS_LMS_NAME":
				if (!$value = $ilSetting->get("short_inst_name")) {
					$value = $ilClientIniFile->readVariable('client', 'name');
				}
				break;

			case "ILIAS_LMS_DESCRIPTION":
				require_once("Modules/SystemFolder/classes/class.ilObjSystemFolder.php");
				if (!$value = ilObjSystemFolder::_getHeaderTitle()) {
					$value = $ilClientIniFile->readVariable('client', 'description');
				}
				break;

			default:
				$value = "";
				break;
		}

		return $value;
	}

	/**
	 * initialize the fields for template processing
	 */
	// private function initFields() {
		// global $ilUser, $ilias, $ilSetting;

		// if (is_array($this->fields)) {
			// return;
		// }
		// $this->fields = array();


		// //
		// // ILIAS fields (type and encoding are commmon to all)
		// //
		// $ilias_names = array(
			// // object information
			// 'ILIAS_REF_ID',
			// 'ILIAS_TITLE',
			// 'ILIAS_DESCRIPTION',
			// 'ILIAS_INSTRUCTIONS',
			// // object context
			// 'ILIAS_CONTEXT_ID',
			// 'ILIAS_CONTEXT_TYPE',
			// 'ILIAS_CONTEXT_TITLE',
			// // call-time imformation
			// // 'ID',
			// 'ILIAS_REMOTE_ADDR',
			// 'ILIAS_TIME',
			// 'ILIAS_TIMESTAMP',
			// 'ILIAS_SESSION_ID',
			// 'ILIAS_TOKEN',
			// 'ILIAS_RESULT_ID',
			// // service urls
			// 'ILIAS_CALLBACK_URL',
			// 'ILIAS_EVENT_LOG_URL',
			// 'ILIAS_RETURN_URL',
			// 'ILIAS_RESULT_URL',
			// // user information
			// 'ILIAS_USER_ID',
			// 'ILIAS_USER_CODE',
			// 'ILIAS_USER_LOGIN',
			// 'ILIAS_USER_FIRSTNAME',
			// 'ILIAS_USER_LASTNAME',
			// 'ILIAS_USER_FULLNAME',
			// 'ILIAS_USER_EMAIL',
			// 'ILIAS_USER_IMAGE',
			// 'ILIAS_USER_LANG',
			// 'ILIAS_USER_WRITE_ACCESS',
			// // platform information
			// 'ILIAS_VERSION',
			// 'ILIAS_CONTACT_EMAIL',
			// 'ILIAS_CLIENT_ID',
			// 'ILIAS_HTTP_PATH',
			// 'ILIAS_LMS_URL',
			// 'ILIAS_LMS_GUID',
			// 'ILIAS_LMS_NAME',
			// 'ILIAS_LMS_DESCRIPTION',
		// );
		// foreach ($ilias_names as $name) {
			// $field = array();
			// $field['field_name'] = $name;
			// $field['field_type'] = ilXapiCmi5Type::FIELDTYPE_ILIAS;
			// $field['encoding'] = '';

			// $this->fields[$field['field_name']] = $field;
		// }

		// //
		// // type specific fields
		// //
        
        // return; // functions below are still not defined in ilXapiCmi5Type
        
		// $type_fields = $this->typedef->getFieldsAssoc();
		// $type_values = $this->typedef->getInputValues();
		// $input_values = $this->getInputValues();
		// foreach ($type_fields as $field) {
			// // set value to user input
			// if ($field['field_type'] != ilXapiCmi5Type::FIELDTYPE_TEMPLATE and $field['field_type'] != ilXapiCmi5Type::FIELDTYPE_CALCULATED) {
				// switch ($field['level']) {
					// case "type":
						// $field['field_value'] = $type_values[$field['field_name']];
						// break;

					// case "object":
					// default:
						// $field['field_value'] = $input_values[$field['field_name']];
						// break;
				// }
			// }

			// $this->fields[$field['field_name']] = $field;
		// }
	// }

	/**
	 * get info about the context in which the link is used
	 * 
	 * The most outer matching course or group is used
	 * If not found the most inner category or root node is used
	 * 
	 * @param	array	list of valid types
	 * @return 	array	context array ("ref_id", "title", "type")
	 */
	public function getContext($a_valid_types = array('crs', 'grp', 'cat', 'root')) {
		global $tree;

		if (!isset($this->context)) {

			$this->context = array();

			// check fromm inner to outer
			$path = array_reverse($tree->getPathFull($this->getRefId()));
			foreach ($path as $key => $row)
			{
				if (in_array($row['type'], $a_valid_types))
				{
					// take an existing inner context outside a course
					if (in_array($row['type'], array('cat', 'root')) && !empty($this->context))
					{
						break;
					}

					$this->context['id'] = $row['child'];
					$this->context['title'] = $row['title'];
					$this->context['type'] = $row['type'];

					// don't break to get the most outer course or group
				}
			}
		}

		return $this->context;
	}

	/**
	 * fetch meta data DELETE UK
	 * and save them locally for caching
	 *
	 * @return   object 	simpleXMLElement of metadata
	 */
	// public function fetchMetaData($a_timeout = 0) {
		// $meta_raw = "";

		// $url = $this->typedef->getMetaDataUrl();
		// if ($url) {
			// $url = $this->fillTemplateRec($url);
			// $default_timeout = ini_get('default_socket_timeout');
			// if ($a_timeout) {
				// ini_set('default_socket_timeout', $a_timeout);
			// }
			// $meta_raw = @file_get_contents($url);
			// ini_set('default_socket_timeout', $default_timeout);

			// $meta_raw_enc = utf8_encode($meta_raw);
		// }

		// // Verification
		// $meta_obj = simplexml_load_string($meta_raw_enc);


		// if ($meta_obj === false) {
			// // use cached calue
			// return simplexml_load_string($this->getMetaDataXML());
		// }

		// if ($meta_raw_enc != $this->getMetaDataXML()) {
			// $this->setMetaDataXML($meta_raw_enc);
			// $this->doUpdate();
		// }

		// return $meta_obj;
	// }

	/**
	 * Update function
	 *
	 * @access public
	 */
	public function doUpdate() {
		global $ilDB;
		$ilDB->replace('xxcf_data_settings', array(
			'obj_id' => array('integer', $this->getId()),
				), array(
			'type_id' => array('integer', $this->getTypeId()),
			'availability_type' => array('integer', $this->getAvailabilityType()),
			'launch_url' => array('text', $this->getLaunchUrl()),
			'activity_id' => array('text', $this->getActivityId()),
			// 'launch_key' => array('text', $this->getLaunchKey()),
			// 'launch_secret' => array('text', $this->getLaunchSecret()),
			'show_debug' => array('integer', $this->getShowDebug()),
			'use_fetch' => array('integer', $this->getUseFetch()),
			// 'instructions' => array('text', $this->getInstructions()),
			// 'meta_data_xml' => array('text', $this->getMetaDataXML()),
			'privacy_ident' => array('integer', $this->getPrivacyIdent()),
			'privacy_name' => array('integer', $this->getPrivacyName()),
			'lp_mode' => array('integer', $this->getLPMode())

			)
		);
		return true;
	}

	public function insertToken($a_token, $a_time) {
		global $ilDB, $ilUser;
		$ilDB->insert('xxcf_data_token', array(
			'token' => array('text', $a_token),
			'time' => array('timestamp', $a_time),
			'obj_id' => array('integer', $this->getId()),
			'usr_id' => array('integer', $ilUser->getId())
			)
		);
		return true;
	}
	
	public function getToken() {
		global $ilDB, $ilUser;
		$token = '';
		$obj_id=$this->_lookupObjectId($_GET['ref_id']);
		$query = "SELECT token FROM xxcf_data_token WHERE obj_id=" . $ilDB->quote($obj_id, 'integer') 
			. " AND usr_id=" . $ilDB->quote($ilUser->getId(), 'integer');
			//.time
		$result = $ilDB->query($query);
		$row = $ilDB->fetchObject($result);
		if ($row) {
			$token = $row->token;
		}
		return $token;
	}


	public function deleteToken($times) {
		global $ilDB;

		$value = date('Y-m-d H:i:s', $times);
		$query = "DELETE FROM xxcf_data_token WHERE time < " . $ilDB->quote($value, 'timestamp');
		$ilDB->manipulate($query);
		return true;
	}


	/**
	 * Delete
	 *
	 * @access public
	 */
	public function doDelete() {
		global $ilDB;
		
		$query = "DELETE FROM xxcf_data_settings " .
				"WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
		$ilDB->manipulate($query);

		$query = "DELETE FROM xxcf_results " .
				"WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
		$ilDB->manipulate($query);

		$query = "DELETE FROM xxcf_user_mapping " .
				"WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
		$ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * read settings
	 *
	 * @access public
	 */
	public function doRead() {
		global $ilDB;
		
		$query = 'SELECT * FROM xxcf_data_settings WHERE obj_id = '
				. $ilDB->quote($this->getId(), 'integer');

		$res = $ilDB->query($query);
		$row = $ilDB->fetchObject($res);
		
		if ($row) {
			$this->setAvailabilityType($row->availability_type);
			$this->setTypeId($row->type_id);
			$this->setInstructions($row->instructions);
			// $this->setMetaDataXML($row->meta_data_xml);
			$this->setLaunchUrl($row->launch_url);
			$this->setActivityId($row->activity_id);
			// $this->setLaunchKey($row->launch_key);
			// $this->setLaunchSecret($row->launch_secret);
			$this->setShowDebug($row->show_debug);
			$this->setUseFetch($row->use_fetch);
			$this->setPrivacyIdent($row->privacy_ident);
			$this->setPrivacyName($row->privacy_name);
			$this->setLPMode($row->lp_mode);
			// $this->setLPThreshold($row->lp_threshold);
		}
	}

	/**
	 * Do Cloning
	 */
	function doCloneObject($new_obj, $a_target_id, $a_copy_id = null) { //TODO
		global $ilDB;
		
		//Settings filling
		$ilDB->insert('xxcf_data_settings', array(
			'obj_id' => array('integer', $new_obj->getId()),
			'type_id' => array('integer', $this->getTypeId()),
			'availability_type' => array('integer', $this->getAvailabilityType()),
			'instructions' => array('text', $this->getInstructions()),
			// 'meta_data_xml' => array('text', $this->getMetaDataXML()),
			'lp_mode' => array('integer', $this->getLPMode()),
			'lp_threshold' => array('float', $this->getLPThreshold())
		 ));
		//Value filling
		$values = $this->getInputValues();
		
		foreach($values as $it => $value){
			$ilDB->insert('xxcf_data_values', array(
			'obj_id' => array('integer', $new_obj->getId()),
			'field_name' => array('text', $it),
			'field_value' => array('text', $value)
				)
		);
		}
	}

	function createToken($time) {
		$pre_token = rand(-100000, 100000);
		$token = $pre_token . $time;
		$token = md5($token);
		return $token;
	}

	function selectCurrentTimestamp() {
		global $ilDB;
		$query = "SELECT CURRENT_TIMESTAMP";
		$result = $ilDB->query($query);
		$row = $ilDB->fetchAssoc($result);
		return $row;
	}


	function checkToken() {
		global $ilDB;

		$token = $_GET['token'];
		$query = "SELECT token FROM xxcf_data_token WHERE token = " . $ilDB->quote($token, 'text');
		$result = $ilDB->query($query);
		$row = $ilDB->fetchAssoc($result);

		if ($row) {
			return "1";
		} else {
			return "0";
		}
	}


	function getTimeToDelete() {
		global $ilDB;
		$query = "SELECT time_to_delete FROM xxcf_data_types WHERE type_id = " . $ilDB->quote($this->getTypeId(), 'integer');
		$result = $ilDB->query($query);
		$row = $ilDB->fetchAssoc($result);
		return $row['time_to_delete'];
	}


	/**
	 * get the learning progress mode
	 */
	public function getLPMode() {
		return $this->lp_mode;
	}

	/**
	 * set the learning progress mode
	 */
	public function setLPMode($a_mode) {
		$this->lp_mode = $a_mode;
	}

	/**
	 * get the learning progress mode
	 */
	public function getLPThreshold() {
		return $this->lp_threshold;
	}

	/**
	 * set the learning progress mode
	 */
	public function setLPThreshold($a_threshold) {
		$this->lp_threshold = $a_threshold;
	}


	/**
	 * Get all user ids with LP status completed
	 *
	 * @return array
	 */
	public function getLPCompleted()
	{
		$this->plugin->includeClass('class.ilXapiCmi5LPStatus.php');
		return ilXapiCmi5LPStatus::getLPStatusDataFromDb($this->getId(), ilLPStatus::LP_STATUS_COMPLETED_NUM);
	}

	/**
	 * Get all user ids with LP status not attempted
	 *
	 * @return array
	 */
	public function getLPNotAttempted()
	{
		$this->plugin->includeClass('class.ilXapiCmi5LPStatus.php');
		return ilXapiCmi5LPStatus::getLPStatusDataFromDb($this->getId(), ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM);
	}

	/**
	 * Get all user ids with LP status failed
	 *
	 * @return array
	 */
	public function getLPFailed()
	{
		$this->plugin->includeClass('class.ilXapiCmi5LPStatus.php');
		return ilXapiCmi5LPStatus::getLPStatusDataFromDb($this->getId(), ilLPStatus::LP_STATUS_FAILED_NUM);
	}

	/**
	 * Get all user ids with LP status in progress
	 *
	 * @return array
	 */
	public function getLPInProgress()
	{
		$this->plugin->includeClass('class.ilXapiCmi5LPStatus.php');
		return ilXapiCmi5LPStatus::getLPStatusDataFromDb($this->getId(), ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);
	}

	/**
	 * Get current status for given user
	 *
	 * @param int $a_user_id
	 * @return int
	 */
	public function getLPStatusForUser($a_user_id)
	{
		$this->plugin->includeClass('class.ilXapiCmi5LPStatus.php');
		return ilXapiCmi5LPStatus::getLPDataForUserFromDb($this->getId(), $a_user_id);
	}

	/**
	 * Track access for learning progress
	 */
	public function trackAccess()
	{
		global $ilUser;

		// track access for learning progress
		if ($ilUser->getId() != ANONYMOUS_USER_ID and $this->getLPMode() == self::LP_ACTIVE)
		{
			$this->plugin->includeClass('class.ilXapiCmi5LPStatus.php');
			ilXapiCmi5LPStatus::trackAccess($ilUser->getId(),$this->getId(), $this->getRefId());
		}
	}
    
    /******* TESTING *******/
    
    public static function handleLPStatusFromProxy($client, $token, $status, $score) {
        self::_log("handleLPStatusFromProxy: ". $client . ":" . $token . ":" . $status . ":" . $score);
    }
    
    private static function _log($txt) {
        file_put_contents("xapilog.txt",$txt."\n",FILE_APPEND);
	}
}

?>
