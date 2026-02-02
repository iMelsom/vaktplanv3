<?php
error_reporting(E_ERROR);
setlocale(LC_TIME, 'nb_NO.utf8');
//date_default_timezone_set ("Europe/Oslo" );
//Testmode?
$uid = $user->data()->id;

$page = isset($definedUserVariables[2])? $definedUserVariables[2] : "Turnus";
$page = isset($_REQUEST['page'])? $_REQUEST['page'] : $page;

$pages = array(
    "Turnus" => 'vaktplan_files/fulltur_RW.php',
    "Oversikt" => 'vaktplan_files/overview_table.php',
    "mineInst" => 'vaktplan_files/personlige_innstillinger.php',
    "mittfrav" => 'vaktplan_files/turnus_mitt_fravaer.php',
    "talogg" => 'vaktplan_files/TA_logg_adm.php',
    "turadm" => 'vaktplan_files/turnus_adm.php',
    "vaktadm" => 'vaktplan_files/vakt_adm.php',
    "tpadjust" => 'vaktplan_files/Turnus_PersonellToTour.php',
    "tldradjust" => 'vaktplan_files/Turnus_PersonellToLeader.php',
    "minkal" => 'vaktplan_files/Turnus_viewown_month.php'
);

if (!$page && $definedUserVariables[2] != '') $page = $definedUserVariables[2];
if (!$page){
    $page = "Assistentturnus";
    require_once 'vaktplan_files/fulltur_RW.php';
    require_once 'vaktplan_files/turnus_dialogs.php';
}

$url = $_SERVER['SERVER_NAME'] ;// . $_SERVER['REQUEST_URI'];
if (strpos($url,'test') !== false) {
    $GLOBALS['testmode'] = "Test";
}
else{
    $GLOBALS['testmode'] = "NotTest";
}

//Set main database

if ($GLOBALS['testmode'] ==  "NotTest"){
    $GLOBALS['Database'] = 'vaktplan';
    $GLOBALS['UserDB'] = 'vaktplanv3_users';
}

//Add some local dates
//require_once "Date/Holidays.php";
//require_once "Date.php";

//vaktplan_files\includes
require_once "includes/turnus_functions.php";
require_once "includes/turnus_DBinfo.php";

//Get user permissions:
$GLOBALS['UserPermissions'] = HentTilganger($uid);
//error_log (print_r($GLOBALS['testmode'], true));
$ElevatedAccess = 0;
if (sjekkSuperbruker($uid)) $ElevatedAccess = 1;


//Set Base URL:
$GLOBALS['MineVakterURL'] = $url;

//get permission names: 
$avd = hentAvdelinger();

//Set local time: 
$TimeVar = "";
//if ($uid == 3) $TimeVar = strtotime("2019-05-07 14:12"); //For testing purposes, to set a spesific time, if user is me
if ($TimeVar == "") $TimeVar = time(); //default time, if time is not given for test. 
?>


