<?php  
//error_log(print_r($_POST, true), 0);
require_once '../users/init.php';  //make sure this path is correct!
require_once '../users/includes/template/prep.php';

if(isset($user) && $user->isLoggedIn()){
    $uid = $user->data()->id;
$UID = $_POST['UID'];
if ($UID != '') $uid = $UID; //Just som copy/paste and sloppyness hardening.
if ($UID == '') $UID = $uid;

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

//Get values: 
    $tUID = $_POST['tUID'];
    $GUID = $_POST['GID'];
    if ($_POST['CHECKED'] == "true") $Checked = 1;
    if ($_POST['CHECKED'] == "false") $Checked = 0;
    $Task = 0;
    if (isset( $_POST['LDR'])) $Task = $_POST['LDR'];
    
 //check if a value exists in DB
    $dbID = SjekkTurnusAktiv($tUID, $GUID);
    $dbIDLDR = SjekkAktivLeder($tUID, $GUID);
    
 
 /*
  * $dbID gir hele raden fra DB. Husk å plukke rett verdi
  */
 
    if ($Task == 0){
        //Add value if not exist
        if ($dbID[0] == ""){
            $QueryStatement = "INSERT INTO `User_inTour` (`ID`, `UID`, `GUID`, `inTour_bool`) VALUES (NULL, :UID, :GUID, :inTour_bool)";
            //$QueryStatement = "INSERT INTO `User_inTour` (`ID`, `UID`, `GUID`, `inTour_bool`) VALUES (NULL, '" . $tUID . "', '".$GUID."', '".$Checked."')";
            $QueryData = array("UID" =>$tUID, "GUID" => $GUID, "inTour_bool" =>$Checked);
        }
        
        //Update value if exist
        if ($dbID[0] >= "1"){
            $QueryStatement = "UPDATE `User_inTour` SET `inTour_bool` = :inTour_bool WHERE `User_inTour`.`ID` = :ID";
            //$QueryStatement = "UPDATE `User_inTour` SET `inTour_bool` = '".$Checked."' WHERE `User_inTour`.`ID` = ". $dbID[0];
            $QueryData = array("inTour_bool" =>$Checked, "ID" => $dbID[0]);
        }
    }
    if ($Task == 1){
            //Add value if not exist
            if ($dbIDLDR[0] == ""){
                $QueryStatement = "INSERT INTO `ledere` (`ID`, `UID`, `GrID`, `StartDate`) VALUES (NULL, :UID, :GUID, :StartDate)";
                //$QueryStatement = "INSERT INTO `User_inTour` (`ID`, `UID`, `GUID`, `inTour_bool`) VALUES (NULL, '" . $tUID . "', '".$GUID."', '".$Checked."')";
                $QueryData = array("UID" =>$tUID, "GUID" => $GUID, "StartDate" => date("Y-m-d", time()));
            }
            
            //Update value if exist
            if ($dbIDLDR[0] >= "1"){
                $QueryStatement = "UPDATE `ledere` SET `EndDate` = :EndDate WHERE `ledere`.`ID` = :ID";
                //$QueryStatement = "UPDATE `User_inTour` SET `inTour_bool` = '".$Checked."' WHERE `User_inTour`.`ID` = ". $dbID[0];
                $QueryData = array("EndDate" =>date("Y-m-d", time()), "ID" => $dbIDLDR[0]);
            }
     }
    
//    error_log("Query: " . $Query);
   WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
    //WriteMySQL($Query, $GLOBALS['Database'], $UID);
}
?>