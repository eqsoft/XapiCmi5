<?php 
/**
 * Copyright (c) 2018 internetlehrer-gmbh.de
 * GPLv2, see LICENSE 
 */
include_once('./Services/Repository/classes/class.ilObjectPluginGUI.php');
include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/classes/class.ilObjXapiCmi5.php');
include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/classes/class.ilXapiCmi5Type.php');


/**
 * xApi plugin: repository object GUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
 * 
 * @ilCtrl_isCalledBy ilObjXapiCmi5GUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjXapiCmi5GUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonactionDispatcherGUI, ilLearningProgressGUI, ilExportGUI
 */
class ilObjXapiCmi5GUI extends ilObjectPluginGUI
{
    const META_TIMEOUT_INFO = 1;
    const META_TIMEOUT_REFRESH = 60;

    /**
     * Valid meta data groups for displaying
     */
    var $meta_groups = array('General', 'LifeCycle', 'Technical', 'Rights');

    /**
     * Initialisation
     *
     * @access protected
     */
    protected function afterConstructor()
    {
        // anything needed after object has been constructed
    }

	public function executeCommand() {
		global $tpl;


		$next_class = $this->ctrl->getNextClass($this);
		switch ($next_class) {
			case 'ilexportgui':
				// only if plugin supports it?
				$tpl->setTitle($this->object->getTitle());
				$tpl->setTitleIcon(ilObject::_getIcon($this->object->getId()));
				$this->setLocator();
				$tpl->getStandardTemplate();
				$this->setTabs();
				include_once './Services/Export/classes/class.ilExportGUI.php';
				$this->tabs_gui->activateTab("export");
				$exp = new ilExportGUI($this);
				$exp->addFormat('xml');
				$this->ctrl->forwardCommand($exp);
				$tpl->show();
				return;
				break;
		}

		$return_value = parent::executeCommand();

		return $return_value;
	}

    /**
     * Get type.
     */
    final function getType()
    {
        return "xxcf";
    }

    function getTitle()
    {
        return $this->object->getTitle();
    }

    /**
     * After object has been created -> jump to this command
     */
    function getAfterCreationCmd()
    {
        return "edit";
    }

    /**
     * Get standard command
     */
    function getStandardCmd()
    {
        return "view";
    }


	/**
 	 * Extended check for being in creation mode
	 *
	 * Use this instead of getCreationMode() because ilRepositoryGUI sets it weakly
	 * The creation form for contents is extended and has different commands
	 * In creation mode $this->object is the parent container and can't be used
	 *
	 * @return bool		creation mode
	 */
	protected function checkCreationMode()
	{
		global $ilCtrl;
		$cmd = $ilCtrl->getCmd();
		if ($cmd == "create" or $cmd == "cancelCreate" or $cmd == "save" or $cmd == "Save")
		{
			$this->setCreationMode(true);
		}
		return $this->getCreationMode();
	}

	/**
     * Perform command
     *
     * @access public
     */
    public function performCommand($cmd)
    {
    	global $ilErr, $ilCtrl, $ilTabs;

		// if (!$this->checkCreationMode())
		// {
			// // set a return URL
			// // IMPORTANT: the last parameter prevents an encoding of & to &amp;
			// // Otherwise the OAuth signatore is calculated wrongly!
			// $this->object->setReturnURL(ILIAS_HTTP_PATH . "/". $ilCtrl->getLinkTarget($this, "view", "", true));
		// }

        switch ($cmd)
        {
        	case "edit":
        	case "update":
			case "showExport":
        		$this->checkPermission("write");
            	// $this->setSubTabs("edit");
            	
                $cmd .= "Object";
                $this->$cmd();
                break;

            case "editLPSettings":
                $this->checkPermission("edit_learning_progress");
                // $this->setSubTabs("learning_progress");

                $cmd .= "Object";
                $this->$cmd();
                break;

            case "checkToken":
               	$this->$cmd();
                break;
            
            default:
            	
				if ($this->checkCreationMode())
				{
					$this->$cmd();
				}
				else
				{
					$this->checkPermission("read");
					if ($this->object->getTypeId() == "") {
						$pl = new ilXapiCmi5Plugin();
						$ilErr->raiseError($pl->txt('type_not_set'), $ilErr->MESSAGE);
					} else 
					if ($this->object->typedef->getAvailability() == ilXapiCmi5Type::AVAILABILITY_NONE)	{
						$ilErr->raiseError($this->lng->txt('xxcf_message_type_not_available'), $ilErr->MESSAGE);
					}

					if (!$cmd)
					{
						$cmd = "viewObject";
					}
					$cmd .= "Object";
					$this->$cmd();
				}
        }
    }
	
	
    /**
     * Set tabs
     */
    function setTabs()
    {
        global $ilTabs, $ilCtrl, $lng;

		if ($this->checkCreationMode())
		{
			return;
		}

        $type = new ilXapiCmi5Type($this->object->getTypeId());

		// view tab
		// if ($this->object->typedef->getLaunchType() == ilXapiCmi5Type::LAUNCH_TYPE_EMBED)
		// {
			$ilTabs->addTab("viewEmbed", $this->lng->txt("content"), $ilCtrl->getLinkTarget($this, "viewEmbed"));
		// }

        //  info screen tab
        $ilTabs->addTab("infoScreen", $this->lng->txt("info_short"), $ilCtrl->getLinkTarget($this, "infoScreen"));

        // add "edit" tab
        if ($this->checkPermissionBool("write"))
        {
            $ilTabs->addTab("edit", $this->lng->txt("settings"), $ilCtrl->getLinkTarget($this, "edit"));
			$ilTabs->addTab("export", $this->lng->txt("export"), $ilCtrl->getLinkTargetByClass("ilexportgui", ""));
        }

        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
        if (ilObjUserTracking::_enabledLearningProgress() && ($this->checkPermissionBool("edit_learning_progress") || $this->checkPermissionBool("read_learning_progress")))
        {
            if ($this->object->getLPMode() > 0 && $this->checkPermissionBool("read_learning_progress"))
            {
				// if ($this->checkPermissionBool("read_learning_progress"))
				// {
					if (ilObjUserTracking::_enabledUserRelatedData())
					{
						$ilTabs->addTab("learning_progress", $lng->txt('learning_progress'), $ilCtrl->getLinkTargetByClass(array('ilObjXapiCmi5GUI','ilLearningProgressGUI','ilLPListOfObjectsGUI')));//, 'showObjectSummary'
					}
					else
					{
						$ilTabs->addTab("learning_progress", $lng->txt('learning_progress'), $ilCtrl->getLinkTargetByClass(array('ilObjXapiCmi5GUI','ilLearningProgressGUI', 'ilLPListOfObjectsGUI'), 'showObjectSummary'));
					}
				// }
				if ($this->checkPermissionBool("edit_learning_progress")) {
					$ilTabs->addSubTab("lp_settings", $this->txt('settings'), $ilCtrl->getLinkTargetByClass(array('ilObjXapiCmi5GUI'), 'editLPSettings'));
				}
            }
			elseif ($this->checkPermissionBool("edit_learning_progress")) {
				$ilTabs->addTab('learning_progress', $lng->txt('learning_progress'), $ilCtrl->getLinkTarget($this,'editLPSettings'));
			}
            
			// if (in_array($ilCtrl->getCmdClass(), array('illearningprogressgui', 'illplistofobjectsgui')))
			// {
				// // $ilTabs->addSubTab("lp_settings", $this->txt('settings'), $ilCtrl->getLinkTargetByClass(array('ilObjXapiCmi5GUI'), 'editLPSettings'));
			// }

        }
        // standard permission tab
        $this->addPermissionTab();
    }
    
    /**
     * Set the sub tabs
     * 
     * @param string	main tab identifier
     */
    // function setSubTabs($a_tab)
    // {
    	// global $ilUser, $ilTabs, $ilCtrl, $lng;
    	
    	// switch ($a_tab)
    	// {
            // case "learning_progress":
                // $lng->loadLanguageModule('trac');
				// if ($this->checkPermissionBool("edit_learning_progress"))
				// {
					// $ilTabs->addSubTab("lp_settings", $this->txt('settings'), $ilCtrl->getLinkTargetByClass(array('ilObjXapiCmi5GUI'), 'editLPSettings'));
				// }
                // if ($this->object->getLPMode() == ilObjXapiCmi5::LP_ACTIVE && $this->checkPermissionBool("read_learning_progress"))
                // {

                    // include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
                    // if (ilObjUserTracking::_enabledUserRelatedData())
                    // {
                        // $ilTabs->addSubTab("trac_objects", $lng->txt('trac_objects'), $ilCtrl->getLinkTargetByClass(array('ilObjXapiCmi5GUI','ilLearningProgressGUI','ilLPListOfObjectsGUI')));
                    // }
                    // $ilTabs->addSubTab("trac_summary", $lng->txt('trac_summary'), $ilCtrl->getLinkTargetByClass(array('ilObjXapiCmi5GUI','ilLearningProgressGUI', 'ilLPListOfObjectsGUI'), 'showObjectSummary'));
                // }
                // break;
        // }
    // }

    /**
     * show info screen
     *
     * @access public
     */
    public function infoScreen() 
    {
		global $ilCtrl;

        $this->tabs_gui->activateTab('infoScreen');

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        
        $info->addSection($this->txt('instructions'));
        $info->addProperty("", $this->object->getInstructions());
        
        $info->enablePrivateNotes();
        
        // add view button
        if ($this->object->typedef->getAvailability() == ilXapiCmi5Type::AVAILABILITY_NONE)
        {
            ilUtil::sendFailure($this->lng->txt('xxcf_message_type_not_available'), false);
        } elseif ($this->object->getOnline())
        {
            if ($this->object->typedef->getLaunchType() == ilXapiCmi5Type::LAUNCH_TYPE_LINK)
            {
                $info->addButton($this->lng->txt("view"), $ilCtrl->getLinkTarget($this, "view"));
            } elseif ($this->object->typedef->getLaunchType() == ilXapiCmi5Type::LAUNCH_TYPE_PAGE)
             {
                $info->addButton($this->lng->txt("view"), $ilCtrl->getLinkTarget($this, "viewPage"));
            }
        }
		$ilCtrl->forwardCommand($info);
    }

    
    /**
     * view the object (default command)
     *
     * @access public
     */
    function viewObject() 
    {
        global $ilErr;

        switch ($this->object->typedef->getLaunchType())
        {
            // case ilXapiCmi5Type::LAUNCH_TYPE_LINK:
                // $this->object->trackAccess();
                // ilUtil::redirect($this->object->getLaunchLink());
                // break;

            // case ilXapiCmi5Type::LAUNCH_TYPE_PAGE:
                // $this->ctrl->redirect($this, "viewPage");
                // break;

            case ilXapiCmi5Type::LAUNCH_TYPE_EMBED:
    			$this->ctrl->redirect($this, "viewEmbed");
                break;

            default:
                $this->ctrl->redirect($this, "infoScreen");
                break;
        }
    }

    function getRegistration() {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    /**
     * view the embedded object
     *
     * @access public
     */
    function viewEmbedObject()
    {
        global $tpl, $ilErr, $ilUser;
		$token = $this->object->fillToken();
        $this->object->trackAccess();
        $privacy_ident = "";
	    $activityId = $this->object->getActivityId();
	    $registration = $this->getRegistration();
        switch ($this->object->getPrivacyIdent()) {
			case ilXapiCmi5Type::PRIVACY_IDENT_CODE :
				$iliasDomain = substr(ILIAS_HTTP_PATH,7);
				if (substr($iliasDomain,0,1) == "\/") $iliasDomain = substr($iliasDomain,1);
				if (substr($iliasDomain,0,4) == "www.") $iliasDomain = substr($iliasDomain,4);
				$privacy_ident = ''.$ilUser->getId().'_'.str_replace('/','_',$iliasDomain).'_'.CLIENT_ID.'@iliassecretuser.de';
				break;
			case ilXapiCmi5Type::PRIVACY_IDENT_NUMERIC :
				$privacy_ident = $ilUser->getId().'@iliassecretuser.de';
				break;
			case ilXapiCmi5Type::PRIVACY_IDENT_LOGIN :
				$privacy_ident = $ilUser->getLogin();
				break;
			case ilXapiCmi5Type::PRIVACY_IDENT_EMAIL :
				$privacy_ident = $ilUser->getEmail();
				break;
			default :
				$privacy_ident = $ilUser->getEmail();;
        }
		$privacy_name = "";
		switch ($this->object->getPrivacyName()) {
			case 0 :
				$privacy_name = "";
				break;
			case 1 :
				$privacy_name = $ilUser->getFirstname();
				break;
			case 2 :
				$privacy_name = $this->lng->txt("salutation_".$ilUser->getGender()) .' '. $ilUser->getLastname();
				break;
			default :
				$privacy_name = $ilUser->getFullname();;
		}
		
		
        $this->tabs_gui->activateTab('viewEmbed');
		$my_tpl = new ilTemplate('./Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/templates/default/tpl.view_embed.html', true, true);

		if ($this->object->getUseFetch() == true) {
            //$sess = openssl_encrypt(json_encode($_COOKIE),'aes128','SALT'); // needs a salt
            $sess = rawurlencode(base64_encode(json_encode($_COOKIE))); // needs a salt
			$my_tpl->setCurrentBlock("fetch");
			$my_tpl->setVariable('REF_ID', $this->object->getRefId());
			$my_tpl->setVariable('SESSION', $sess);
			$my_tpl->setVariable('ILIAS_URL', ILIAS_HTTP_PATH);
			$my_tpl->parseCurrentBlock();
		} else {
			$my_tpl->setCurrentBlock("no_fetch");
			$my_tpl->setVariable('ILIAS_URL', ILIAS_HTTP_PATH);
			$my_tpl->setVariable('LAUNCH_KEY', CLIENT_ID);//$this->object->getLaunchKey());
			$my_tpl->setVariable('LAUNCH_SECRET', $token);//$this->object->getLaunchSecret());
			$my_tpl->parseCurrentBlock();
		}

		if ($this->object->getShowDebug() == true) {
			$my_tpl->setCurrentBlock("debug_embed");
			$my_tpl->setVariable('ILIAS_URL', ILIAS_HTTP_PATH);
			$my_tpl->setVariable('LRS_ENDPOINT', $this->object->typedef->getLrsEndpoint());
			$my_tpl->setVariable('LRS_KEY', $this->object->typedef->getLrsKey());
			$my_tpl->setVariable('LRS_SECRET', $this->object->typedef->getLrsSecret());
			$my_tpl->setVariable('LRS_USER_ID', $privacy_ident); 
			$my_tpl->setVariable('LRS_USER_NAME', $privacy_name);
			$my_tpl->parseCurrentBlock();
		}

		$my_tpl->setVariable('ILIAS_URL', ILIAS_HTTP_PATH);
        $my_tpl->setVariable('XAPI_USER_ID', $privacy_ident); 
        $my_tpl->setVariable('XAPI_USER_NAME', $privacy_name);
        $my_tpl->setVariable('XAPI_ACTIVITY_ID', $activityId);
        $my_tpl->setVariable('XAPI_REGISTRATION', $registration);
        $my_tpl->setVariable('LAUNCH_URL', $this->object->getLaunchUrl());
        $my_tpl->setVariable('LAUNCH_TARGET', 'window');
        $my_tpl->setVariable('WIN_LAUNCH_WIDTH', '1000');
        $my_tpl->setVariable('WIN_LAUNCH_HEIGHT', '700');
        $my_tpl->setVariable('FRAME_LAUNCH_WIDTH', '1000');
        $my_tpl->setVariable('FRAME_LAUNCH_HEIGHT', '700');

		$tpl->setContent($my_tpl->get());
    }

    // /**
     // * view the object as a page
     // *
     // * @access public
     // */
    // function viewPageObject()
    // {
        // global $ilErr;

        // $this->object->trackAccess();
        // echo $this->object->getPageCode();
        // exit;
    // }

    /**
     * create new object form
     *
     * @access	public
     */
    function create()
    {
		global $ilErr;
		if (ilXapiCmi5Type::getCountTypesForCreate() == 0) {
			$pl = new ilXapiCmi5Plugin();
			$ilErr->raiseError($pl->txt('no_type_available_for_create'), $ilErr->MESSAGE);
		} else {
			parent::create();
		}
    }
    
    /**
     * cancel creation of a new object
     *
     * @access	public
     */
    function cancelCreate()
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * save the data of a new object
     *
     * @access	public
     */
    // function saveX()
    // {
        // global $rbacsystem, $ilErr;
        
            // $new_type = $this->getType();
            // $_REQUEST["new_type"] = $new_type;
            // if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
            // {
                // $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
            // }
            // $this->initForm("create");

            // if ($this->form->checkInput())
            // {
                // $this->object = new ilObjXapiCmi5;
                // $this->object->setType($this->type);
                // $this->object->create();
                // $this->object->createReference();
                // $this->object->putInTree($_GET["ref_id"]);
                // $this->object->setPermissions($_GET["ref_id"]);
                // $this->saveFormValues();

                // $this->ctrl->setParameter($this, "ref_id", $this->object->getRefId());
                // $this->afterSave($this->object);
 // // die("save");       
            // } 
            // else
            // {
                // $this->form->setValuesByPost();
                // $this->tpl->setContent($this->form->getHTML());              
            // }
    // }

    /**
     * Edit object
     *
     * @access protected
     */
    public function editObject()
    {
        global $ilErr, $ilAccess;

        $this->tabs_gui->activateTab('edit');
        // $this->tabs_gui->activateSubTab('settings');

        $this->initForm('edit', $this->loadFormValues());
        // $this->loadFormValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * update object
     *
     * @access public
     */
    public function updateObject()
    {
        $this->tabs_gui->activateTab('edit');
        // $this->tabs_gui->activateSubTab('settings');
        
        $this->initForm("edit");
        if ($this->form->checkInput())
        {
            $this->saveFormValues();
            ilUtil::sendInfo($this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, "edit");
        }
        else
        {
            $this->form->setValuesByPost();
            $this->tpl->setVariable('ADM_CONTENT', $this->form->getHTML());
        }
    }

    /**
     * Init properties form
     *
     * @param        int        $a_mode        Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE)
     * @param		 array		(assoc) form values
     * @access       protected
     */
    protected function initForm($a_mode, $a_values = array())
    {
        if (is_object($this->form))
        {
            return true;
        }

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        // if ($a_mode != "create")
        // {
	        // $item = new ilCustomInputGUI($this->lng->txt('type'), '');
	        // $item->setHtml($this->object->typedef->getTitle());
	        // $item->setInfo($this->object->typedef->getDescription());
	        // $this->form->addItem($item);
        // }
        
        $item = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $item->setSize(40);
        $item->setMaxLength(128);
        $item->setRequired(true);
        $item->setInfo($this->txt('title_info'));
		$item->setValue($a_values['title']);        
        $this->form->addItem($item);

        $item = new ilTextAreaInputGUI($this->lng->txt('description'), 'description');
        $item->setInfo($this->txt('xxcf_description_info'));
        $item->setRows(2);
        $item->setCols(80);
		$item->setValue($a_values['description']);        
        $this->form->addItem($item);
       
        // if ($a_mode == "create")
        // {
            $item = new ilRadioGroupInputGUI($this->lng->txt('type'), 'type_id');
            $item->setRequired(true);
            $types = ilXapiCmi5Type::_getTypesData(false, ilXapiCmi5Type::AVAILABILITY_CREATE);
            foreach ($types as $type)
            {
                $option = new ilRadioOption($type['title'], $type['type_id'], $type['description']);
                $item->addOption($option);
            }
			$item->setValue($this->object->typedef->getTypeId());
            $this->form->addItem($item);

            // $this->form->setTitle($this->txt('xxcf_new'));
            // $this->form->addCommandButton((!$this->checkCreationMode() ? 'update' : 'save'), $this->lng->txt('save'));
            // $this->form->addCommandButton('cancelCreate', $this->lng->txt("cancel"));
        // }
        // else
        // {
            $item = new ilCheckboxInputGUI($this->lng->txt('online'), 'online');
            $item->setInfo($this->txt("xxcf_online_info"));
			$item->setValue("1");
			if ($a_values['online'])
			{
				$item->setChecked(true);
			}        
          	$this->form->addItem($item);
			
			$item = new ilFormSectionHeaderGUI();
			$item->setTitle($this->txt("launch_options"));
			$this->form->addItem($item);

			$item = new ilTextInputGUI($this->txt('launch_url'), 'launch_url');
			$item->setSize(40);
			$item->setMaxLength(128);
			$item->setRequired(true);
			$item->setInfo($this->txt('launch_url_info'));
			$item->setValue($a_values['launch_url']);        
			$this->form->addItem($item);
			
			$item = new ilTextInputGUI($this->txt('activity_id'), 'activity_id');
			$item->setSize(40);
			$item->setMaxLength(128);
			// $item->setRequired(true);
			$item->setInfo($this->txt('activity_id_info'));
			$item->setValue($a_values['activity_id']);        
			$this->form->addItem($item);
            
			// $item = new ilTextInputGUI($this->lng->txt('launch_key'), 'launch_key');
			// $item->setSize(40);
			// $item->setMaxLength(128);
			// // $item->setRequired(true);
			// $item->setInfo($this->txt('launch_key_info'));
			// $item->setValue($a_values['launch_key']);        
			// $this->form->addItem($item);
        	            
			// $item = new ilTextInputGUI($this->lng->txt('launch_secret'), 'launch_secret');
			// $item->setSize(40);
			// $item->setMaxLength(128);
			// // $item->setRequired(true);
			// $item->setInfo($this->txt('launch_secret_info'));
			// $item->setValue($a_values['launch_secret']);        
			// $this->form->addItem($item);

            $item = new ilCheckboxInputGUI($this->txt('use_fetch'), 'use_fetch');
            $item->setInfo($this->txt("use_fetch_info"));
			$item->setValue("1");
			if ($a_values['use_fetch'])
			{
				$item->setChecked(true);
			}        
          	$this->form->addItem($item);
			

			$item = new ilFormSectionHeaderGUI();
			$item->setTitle($this->txt("privacy_options"));
			$this->form->addItem($item);

			$item = new ilRadioGroupInputGUI($this->txt('content_privacy_ident'), 'privacy_ident');
			$op = new ilRadioOption($this->txt('conf_privacy_ident_0'), 0);
			$item->addOption($op);
			// $op = new ilRadioOption($this->txt('conf_privacy_ident_1'), 1);
			// $item->addOption($op);
			// $op = new ilRadioOption($this->txt('conf_privacy_ident_2'), 2);
			// $item->addOption($op);
			$op = new ilRadioOption($this->txt('conf_privacy_ident_3'), 3);
			$item->addOption($op);
			$item->setValue($a_values['privacy_ident']);
			$item->setInfo($this->txt('info_privacy_ident'));
			$item->setRequired(false);
			$this->form->addItem($item);

			$item = new ilRadioGroupInputGUI($this->txt('content_privacy_name'), 'privacy_name');
			$op = new ilRadioOption($this->txt('conf_privacy_name_0'), 0);
			$item->addOption($op);
			$op = new ilRadioOption($this->txt('conf_privacy_name_1'), 1);
			$item->addOption($op);
			$op = new ilRadioOption($this->txt('conf_privacy_name_2'), 2);
			$item->addOption($op);
			$op = new ilRadioOption($this->txt('conf_privacy_name_3'), 3);
			$item->addOption($op);
			$item->setValue($a_values['privacy_name']);
			$item->setInfo($this->txt('info_privacy_name'));
			$item->setRequired(false);
			$this->form->addItem($item);


			$item = new ilFormSectionHeaderGUI();
			$item->setTitle($this->txt("log_options"));
			$this->form->addItem($item);

			$item = new ilCheckboxInputGUI($this->txt('show_debug'), 'show_debug');
			$item->setInfo($this->txt("show_debug_info"));
			$item->setValue("1");
			if ($a_values['show_debug'])
			{
				$item->setChecked(true);
			}        
			$this->form->addItem($item);

            $this->form->setTitle($this->lng->txt('settings'));
            $this->form->addCommandButton("update", $this->lng->txt("save"));
            $this->form->addCommandButton("view", $this->lng->txt("cancel"));

        // }
    }
    

    /**
     * Fill the properties form with database values
     *
     * @access   protected
     */
    protected function loadFormValues()
    {
		$values = array();

		$values['title'] = $this->object->getTitle();
		$values['description'] = $this->object->getDescription();
		$values['type_id'] = $this->object->getTypeId();
		$values['type'] = $this->object->typedef->getTitle();
		$values['instructions'] = $this->object->getInstructions();
		if ($this->object->getAvailabilityType() == ilObjXapiCmi5::ACTIVATION_UNLIMITED)
		{
			$values['online'] = '1';
		}
		$values['launch_url'] = $this->object->getLaunchUrl();
		$values['activity_id'] = $this->object->getActivityId();
		// $values['launch_key'] = $this->object->getLaunchKey();
		// $values['launch_secret'] = $this->object->getLaunchSecret();
		$values['show_debug'] = $this->object->getShowDebug();
		$values['use_fetch'] = $this->object->getUseFetch();
		$values['privacy_ident'] = $this->object->getPrivacyIdent();
		$values['privacy_name'] = $this->object->getPrivacyName();

		return $values;
    }

    
    /**
     * Save the property form values to the object
     *
     * @access   protected
     */
    protected function saveFormValues() 
    {

        $this->object->setTitle($this->form->getInput("title"));
        $this->object->setDescription($this->form->getInput("description"));
        if ($this->form->getInput("type_id"))
        {
            $this->object->setTypeId($this->form->getInput("type_id"));
        }
        $this->object->setAvailabilityType($this->form->getInput('online') ? ilObjXapiCmi5::ACTIVATION_UNLIMITED : ilObjXapiCmi5::ACTIVATION_OFFLINE);
		$this->object->setLaunchUrl($this->form->getInput("launch_url"));
		$this->object->setActivityId($this->form->getInput("activity_id"));
		// $this->object->setLaunchKey($this->form->getInput("launch_key"));
		// $this->object->setLaunchSecret($this->form->getInput("launch_secret"));
		$this->object->setShowDebug($this->form->getInput("show_debug"));
		$this->object->setUseFetch($this->form->getInput("use_fetch"));
		$this->object->setPrivacyIdent($this->form->getInput("privacy_ident"));
		$this->object->setPrivacyName($this->form->getInput("privacy_name"));
        $this->object->update();
    }
    
    

    /**
     * Edit the learning progress settings
     */
    protected function editLPSettingsObject()
    {
        $this->tabs_gui->activateTab('learning_progress');
        $this->tabs_gui->activateSubTab('lp_settings');

        $this->initFormLPSettings();
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Init the form for Learning progress settings
     */
    protected function initFormLPSettings()
    {
        global $ilSetting, $lng, $ilCtrl;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->txt('lp_settings'));

        $rg = new ilRadioGroupInputGUI($this->txt('lp_mode'), 'lp_mode');
        $rg->setRequired(true);
        $rg->setValue($this->object->getLPMode());
        $ro = new ilRadioOption($this->txt('lp_inactive'),ilObjXapiCmi5::LP_INACTIVE, $this->txt('lp_inactive_info'));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->txt('lp_completed'),ilObjXapiCmi5::LP_Completed, $this->txt('lp_completed_info'));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->txt('lp_passed'),ilObjXapiCmi5::LP_Passed, $this->txt('lp_passed_info'));
        $rg->addOption($ro);
        // $ro = new ilRadioOption($this->txt('lp_completed_and_passed'),ilObjXapiCmi5::LP_CompletedAndPassed, $this->txt('lp_completed_and_passed_info'));
        // $rg->addOption($ro);
        $ro = new ilRadioOption($this->txt('lp_completed_or_passed'),ilObjXapiCmi5::LP_CompletedOrPassed, $this->txt('lp_completed_or_passed_info'));
        $rg->addOption($ro);
        $form->addItem($rg);

		// $item = new ilCheckboxInputGUI($this->txt('use_score'), 'use_score');
		// $item->setInfo($this->txt("use_score_info"));
		// $item->setValue("1");
		// if ($this->object->getLPUseScore()) {
			// $item->setChecked(true);
		// }
        // $ni = new ilNumberInputGUI($this->txt('lp_threshold'),'lp_threshold');
        // $ni->setMinValue(0);
        // $ni->setMaxValue(1);
        // $ni->setDecimals(2);
        // $ni->setSize(4);
        // $ni->setRequired(true);
        // $ni->setValue($this->object->getLPThreshold());
        // $ni->setInfo($this->txt('lp_threshold_info'));
        // $item->addSubItem($ni);
		// $form->addItem($item);

		
		
        $form->addCommandButton('updateLPSettings', $lng->txt('save'));
        $this->form = $form;

    }

    /**
     * Update the LP settings
     */
    protected function updateLPSettingsObject()
    {
        $this->tabs_gui->activateTab('learning_progress');
        $this->tabs_gui->activateSubTab('lp_settings');

        $this->initFormLPSettings();
        if (!$this->form->checkInput())
        {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        }

        $this->object->setLPMode($this->form->getInput('lp_mode'));
		//score
        $this->object->setLPThreshold($this->form->getInput('lp_threshold'));
        $this->object->update();
        $this->ctrl->redirect($this, 'editLPSettings');
    }

     /**
     * Refresh the meta data
     *
     * @access   public
     */
    public function refreshMetaObject()
    {
        $this->object->fetchMetaData(self::META_TIMEOUT_REFRESH);
        $this->ctrl->redirect($this, "infoScreen");
    }

    /**
     * check a token for validity
     * 
     * @return boolean	check is ok
     */
    function checkToken()
    {
        $obj = new ilObjXapiCmi5();
        $value = $obj->checkToken();
        echo $value;
    }
	
	protected function showExportObject() {
		require_once("./Services/Export/classes/class.ilExportGUI.php");
		$export = new ilExportGUI($this);
		$export->addFormat("xml");
		$ret = $this->ctrl->forwardCommand($export);
	}
	/**
	 * erase!
	 */
	private function activateTab() {
		$next_class = $this->ctrl->getCmdClass();

		switch($next_class) {
			case 'ilexportgui':
				$this->tabs->activateTab("export");
				break;
		}

		return;
	}

}

?>
