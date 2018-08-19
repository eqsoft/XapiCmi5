<?php
/**
 * Copyright (c) 2018 internetlehrer-gmbh.de
 * GPLv2, see LICENSE 
 */

/**
 * xApi plugin: token generation script
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
 */ 
chdir("../../../../../../../");

// Avoid redirection to start screen
// (see ilInitialisation::InitILIAS for details)
$_GET["baseClass"] = "ilStartUpGUI";

require_once "./include/inc.header.php";
require_once "./Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/classes/class.ilObjXapiCmi5.php";

$track_obj = new ilObjXapiCmi5();
echo CLIENT_ID . ':' . $track_obj->getToken();

exit;
?>
