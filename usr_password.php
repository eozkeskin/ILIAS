<?php
/**
* change user password
* 
* @author	Peter Gabriel <pgabriel@databay.de> 
* @version	$Id$
* @package	ilias
*/
require_once "./include/inc.header.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.usr_password.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("MESSAGE", "message", "tpl.message.html");

//display buttons
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_profile.php");
$tpl->setVariable("BTN_TXT",$lng->txt("personal_profile"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_password.php");
$tpl->setVariable("BTN_TXT",$lng->txt("chg_password"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_agreement.php");
$tpl->setVariable("BTN_TXT",$lng->txt("usr_agreement"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_row");
$tpl->parseCurrentBlock();

if ($_POST["pw_old"] != "")
{
	if ($ilias->account->updatePassword($_POST["pw_old"], $_POST["pw1"], $_POST["pw2"]))
	{
		$ilias->error_obj->sendInfo("msg_changes_ok");
		$msg = "msg_changes_ok";
	}
	else
	{
		$ilias->error_obj->sendInfo("msg_failed");
		$msg = "msg_failed";
	}
	
	$tpl->setCurrentBlock("message");
	$tpl->setVariable("MSG", $lng->txt($msg));
	$tpl->parseCurrentBlock();
}


$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("chg_password"));

$tpl->setVariable("TXT_NAME", $lng->txt("username"));
$tpl->setVariable("NAME", $ilias->account->getLogin());
$tpl->setVariable("TXT_CURRENT_PW", $lng->txt("current_password"));
$tpl->setVariable("TXT_DESIRED_PW", $lng->txt("desired_password"));
$tpl->setVariable("TXT_RETYPE_PW", $lng->txt("retype_password"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

$tpl->show();
?>