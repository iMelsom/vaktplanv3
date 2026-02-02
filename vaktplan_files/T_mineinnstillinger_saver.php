<?php
require_once '../users/init.php';  //make sure this path is correct!
require_once '../users/includes/template/prep.php';
if(isset($user) && $user->isLoggedIn()){
    
//live or test? 
if (strpos($url,'test') !== false) {
	$GLOBALS['testmode'] = "Test";
}
else{
	$GLOBALS['testmode'] = "NotTest";
}
//Set main database
if ($GLOBALS['testmode'] ==  "Test"){
	$GLOBALS['Database'] = 'minevakter_test';
	$GLOBALS['UserDB'] = 'minevakter_USpice';
}
if ($GLOBALS['testmode'] ==  "NotTest"){
	$GLOBALS['Database'] = 'vaktplan';
	$GLOBALS['UserDB'] = 'vaktplan_users';
}

require_once "includes/turnus_DBinfo.php";
require_once "includes/turnus_functions.php";
require_once 'init.php'; //, vaktplaninit, Trenger UID. Må hentes etter UID er definert.


//Get Variables
$Startpage = '';
if(isset($_GET['Startpage']))$Startpage = $_GET['Startpage'];
$NumMonths = '';
if(isset($_GET['NumMonths']))$NumMonths = $_GET['NumMonths'];
$Offday = '';
if(isset($_GET['Offday']))$Offday = $_GET['Offday'];
$altEmail = '';
if(isset($_GET['altEmail']))$altEmail = $_GET['altEmail'];
$PreDays = '';
if(isset($_GET['PreDays']))$PreDays = $_GET['PreDays'];
$SendBoth = '';
if(isset($_GET['SendBoth']))$SendBoth = $_GET['SendBoth'];
$dumpUpdate = '';
if(isset($_GET['DumpUpdate']))$dumpUpdate = $_GET['DumpUpdate'];
//error_log('test : ' . $dumpUpdate, 0);



$uid = $user->data()->id;


if ($_POST['ResetColor'])$ColorReset = $_POST['ResetColor'];


for ($i=1; oversettFraversGrunn($i) != ''; $i+=1){
    $fraverBeskrivelse = oversettFraversGrunn($i);
    $altColorsArray[$fraverBeskrivelse[2]] =  $_POST[$fraverBeskrivelse[2]];
}

if ($ColorReset == 1){
    for ($i=1; oversettFraversGrunn($i) != ''; $i+=1){
        $fraverBeskrivelse = oversettFraversGrunn($i);
        $altColorsArray[$fraverBeskrivelse[2]] =  $fraverBeskrivelse[3];
    }
}
if (is_array($altColorsArray)){$altColors =  serialize($altColorsArray);}
else $altColors = '';
//error_log("blipp " . print_r($altColorsArray,true));
//error_log("blipp " . print_r($_POST,true));
//error_log($altColors, 0);

if ($altEmail == 'empty' || $altEmail == 'NoChange' || $SendBoth == ''){
    $SendBoth = 0;
}

$definedUserVariables = chkUserVariables($uid);

//Add new: 
if ($definedUserVariables == false || $definedUserVariables == ''){ //Make sure a the user has possible settings
    $QueryStatement =		 "INSERT INTO `UserOptions`";
    $QueryStatement = $QueryStatement. "(`UID`, `DefaultView`, `NumMonths`, `ShowOffdayCodes`, `altEmail`, `altColours`, `PreDays`, `sendToBoth`)";
    $QueryStatement = $QueryStatement. "VALUES (:UID, NULL, NULL, NULL, NULL, NULL, NULL, 0);"; 

    $QueryData = array("UID" => $uid);
	WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	
	//WriteMySQL($Query, $GLOBALS['Database'], $UID);
	$definedUserVariables = chkUserVariables($uid); //Get them again, since we just created them
//	error_log($Query, 0);
}
//ID	UID	DefaultView	NumMonths	ShowOffdayCodes	altEmail	altColours	PreDays	sendToBoth

//Edit Existing: 
if ( is_array ($definedUserVariables)) {
    $QueryData = array();
    
    $QueryStatement = "UPDATE `UserOptions` SET ";
	
	//Always setting UID, since query needs a start
    $QueryStatement = $QueryStatement. "`UID` = :UID ";
    $QueryData["UID"] = $uid;
    
	//setting default view?
	if ($definedUserVariables[2] != $Startpage && $Startpage != ''){
	    if ($Startpage == 'empty') $Startpage = '';
	    $QueryStatement = $QueryStatement. ",  ";
	    $QueryStatement = $QueryStatement. "`DefaultView` = :DefaultView ";
	    $QueryData["DefaultView"] = $Startpage;
	    
	}
	
	//Setting Numer of months to view?
	if ($definedUserVariables[3] != $NumMonths && $NumMonths != ''){
	    if ($Startpage == 'empty') $Startpage = '';
	    $QueryStatement = $QueryStatement. ",  ";
	    $QueryStatement = $QueryStatement. "`NumMonths` = :NumMonths ";
	    $QueryData["NumMonths"] = $NumMonths;
	    
	}
	
	//Showing offdaycodes?
	if ($definedUserVariables[4] != $Offday && $Offday != ''){
	    if ($Startpage == 'empty') $Offday = '';
	    $QueryStatement = $QueryStatement. ",  ";
	    $QueryStatement = $QueryStatement. "`ShowOffdayCodes` = :ShowOffdayCodes ";
	    $QueryData["ShowOffdayCodes"] = $Offday;
	    
	}
	
	//setting secondary email?
	if ($definedUserVariables[5] != $altEmail && $altEmail != ''){
	    if ($altEmail == 'empty')$altEmail ='';
	    $QueryStatement = $QueryStatement. ",  ";
	    $QueryStatement = $QueryStatement. "`altEmail` = :altEmail ";
	    $QueryData["altEmail"] = $altEmail;
	    
	}
	
    //Setting or resetting colors?	
	if ( $ColorReset == 1 || (($definedUserVariables[6] != $altColors  && $altColors != '' && $altColors !='a:10:{s:1:"f";N;s:1:"a";N;s:1:"p";N;s:1:"s";N;s:1:"k";N;s:1:"g";N;s:1:"u";N;s:2:"a2";N;s:1:"h";N;s:2:"sx";N;}'))){
	    $QueryStatement = $QueryStatement. ",  ";
	    $QueryStatement = $QueryStatement. "`altColours` = :altColours ";
	    $QueryData["altColours"] = $altColors;
	    
	}
	
	//Setting number of days before today in rota? 
	if ($definedUserVariables[7] != $PreDays && $PreDays != ''){
	    $QueryStatement = $QueryStatement. ",  ";
	    $QueryStatement = $QueryStatement. "`PreDays` = :PreDays ";
	    $QueryData["PreDays"] = $PreDays;
	    
	}
	
	//sending to both emails? 
	if ($definedUserVariables[8] != $SendBoth && $SendBoth != ''){
	    $QueryStatement = $QueryStatement. ",  ";
	    $QueryStatement = $QueryStatement. "`sendToBoth` = :sendToBoth ";
	    $QueryData["sendToBoth"] =  $SendBoth;
	    
	}

	//Roll out tour as it is prepared or not?
	if ($definedUserVariables[9] != $dumpUpdate && $dumpUpdate != ''){
	    $QueryStatement = $QueryStatement. ",  ";
	    $QueryStatement = $QueryStatement. "`dumpToScreen` = :dumpToScreen ";
	    $QueryData["dumpToScreen"] =  $dumpUpdate;
	    
	}
	
	$QueryStatement = $QueryStatement. "WHERE `UserOptions`.`ID` = :ID";
	$QueryStatement = $QueryStatement. ";";
	$QueryData["ID"] = $definedUserVariables[0];
	
	
	WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	//error_log($Query, 0);
	}
}

?>