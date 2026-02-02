<?php 
require_once '../users/init.php';  //make sure this path is correct!
require_once '../users/includes/template/prep.php';
if(isset($user) && $user->isLoggedIn()){
    $uid = $user->data()->id;
    //if (!$uid || $uid == "") $uid = $_POST['UID']; LEftover, removed for hardeing. UID should ONLY come from logged in user not be sent. Other UID's should have other variable names
    $GLOBALS['testmode'] = "NotTest";
    if (strpos($url,'test') !== false) {
		$GLOBALS['testmode'] = "Test";
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

	$vID = $_POST['vID'];
    $Description = inputCleanse($_POST['DESCR']);
    $Date = $_POST['Date'];
    $FromUID = $_POST['FUID'];
    $ToUID = $_POST['TUID'];
    $insertVakt = $_POST['NEWV'];
    
    if ($ToUID ==  ""  && $insertVakt > 0) $ToUID = $FromUID;
    if ($Description ==  ""  && $insertVakt > 0) $Description = "Vaktendring";
    
    $Vaktinfo = sjekkVaktType($vID);
    $GuID = $Vaktinfo[7];
    
    $ActiveTour = finnGjeldendeTurnus(strtotime($Date), $GuID);
    
    if ($FromUID != 20 || $FromUID != "")$OrgOwner = $FromUID;

    if ($FromUID == 20 || $FromUID == ""){
        $dayplan = getDayPlanOnlyOriginalOwners(strtotime($Date), $ActiveTour[0], $GuID );
        $OrgOwner = $dayplan[$vID];
    }

   // error_log("Turnus_WriteDB 48:" . print_r( $_POST, true),0 );
    //error_log("Turnus_WriteDB 45:" . $OrgOwner );
    //error_log("Turnus_WriteDB 46: vID: " . $vID . " date: " . strtotime($Date). " GuID: " . $GuID . " dayplan: " . print_r($dayplan,true),0);
    //if ($insertVakt > 0) $vID = $insertVakt;
    
	
    $SendEmail = $_POST['MAIL'];
    
    
	if ($insertVakt > 0)$VaktInfo = sjekkVaktType($insertVakt);
	else $VaktInfo = sjekkVaktType($vID);
	
	//if onPRem is set:
	if ($VaktInfo[8] != '' || $VaktInfo[8] != NULL) $VaktInfo[6] = $VaktInfo[8];
	if ($VaktInfo[9] != '' || $VaktInfo[9] != NULL) $VaktInfo[3] = $VaktInfo[9];
	if ($VaktInfo[10] != '' || $VaktInfo[10] != NULL) $VaktInfo[4] = $VaktInfo[10];
	
	$definedUserVariables = chkUserVariables($ToUID);
	if (($definedUserVariables[8] == '0') && ($definedUserVariables[5] != '' || $definedUserVariables[5] != false))$emailMottager =  $definedUserVariables[5];
	if (($definedUserVariables[8] == '0') && ($definedUserVariables[5] == '' || $definedUserVariables[5] == false))$emailMottager = hentEpostAdresse($ToUID)[0];
	if (($definedUserVariables[8] == '1') && ($definedUserVariables[5] != '' || $definedUserVariables[5] != false)){$emailMottager = hentEpostAdresse($ToUID)[0];
	$ccMottager =  $definedUserVariables[5];}
	if ($ToUID == '' || $ToUID == 0)$emailMottager =  hentEpostAdresse($uid)[0];
	
	$RegDate = date('Y-m-d H:i:s', $TimeVar);
	
	if ($insertVakt > 0){ //insert the updated shift, then after this if move the original shift to "udekket" (user 20)
    	//Create and send statement and Querydata
    	$QueryStatement = "INSERT INTO `vaktplan`.`Endringer` (`ID`,`VaktID`, `Beskrivelse`, `Dato`, `RegDato`, `FraBrukerID`, `TilBrukerID`, `WhoDunnit`, `orgEier`)VALUES (NULL, :VaktID, :Beskrivelse, :Dato, :Regdato, :FraBrukerID, :TilBrukerID, :WhoDunnit, :OrgOwner);";
    	$QueryData = array("VaktID" => $insertVakt, "Beskrivelse" => $Description, "Dato" => $Date, "Regdato" => $RegDate, "FraBrukerID" => $FromUID, "TilBrukerID" =>$ToUID, "WhoDunnit" => $uid, "OrgOwner" => $OrgOwner);
    	WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
    	$ToUID = 20;
	}
	
	//Create and send statement and Querydata
	$QueryStatement = "INSERT INTO `vaktplan`.`Endringer` (`ID`,`VaktID`, `Beskrivelse`, `Dato`, `RegDato`, `FraBrukerID`, `TilBrukerID`, `WhoDunnit`, `orgEier`)VALUES (NULL, :VaktID, :Beskrivelse, :Dato, :Regdato, :FraBrukerID, :TilBrukerID, :WhoDunnit, :OrgOwner);";
	$QueryData = array("VaktID" => $vID, "Beskrivelse" => $Description, "Dato" => $Date, "Regdato" => $RegDate, "FraBrukerID" => $FromUID, "TilBrukerID" =>$ToUID, "WhoDunnit" => $uid, "OrgOwner" => $OrgOwner);
	WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	
	//Prep ICAL and mailnotif
	$navn = finnAnsattNavn($uid);
	
	
	//lag ical-event og send: 
	if ($SendEmail == "send" && $emailMottager != "udekt@minevakter.no"){

	//email data: 
        $to = $emailMottager;
        $subject = 'Vaktvarsling';
        $body = $navn[0] . " " . $navn[1] . ' har gitt deg en ' .$VaktInfo[1] . '-vakt den ' .$Date;
		$body = $body . ' - Vær oppmerksom på at Microsoft Outlook ikke respekterer tidsoneinstllinger på kalenderinvitasjoner';
 	
	
	//ical data: 
		$vaktstart_timestamp = strtotime($Date . " " . $VaktInfo[3]);
		$ical_dato_vaktstart = date("Ymd\THis", $vaktstart_timestamp);
		if ($vaktstart_timestamp >= strtotime($Date . " 18:30:00"))  $ical_dato_vaktstart = date("Ymd\THis", strtotime('-1 day', $vaktstart_timestamp ));
		if ($VaktInfo[6] == '') $ical_duration = "PT0H0M0S";
		else{
		    $hourMin = explode(".", $VaktInfo[6]);
		    if ($hourMin[1] ==  "00")$hourMin[1] = 00;
		    if ($hourMin[1] ==  "25")$hourMin[1] = 15;
		    if ($hourMin[1] ==  "50")$hourMin[1] = 30;
		    if ($hourMin[1] ==  "75")$hourMin[1] = 45;
		    $ical_duration = "PT" .$hourMin[0] . "H".$hourMin[1]."M0S";
		}
		$ical_dato_datestamp = date("Ymd\THis", $TimeVar);
		$ical_uid = $ical_dato_datestamp . "-" . $emailMottager[0];
		$Descriptor = '';
		if ($Description == 'Bytte') $Descriptor = 'Byttevakt ';
		if ($Description == 'Flytte') $Descriptor = 'Ekstravakt ';
		
//		error_log($ical_uid, 0);
	
		$ical_content =
"BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
PRODID:-//JBV//EN
X-WR-TIMEZONE:\"Europe/Oslo\"
BEGIN:VEVENT
DTSTAMP:" .$ical_dato_datestamp. "
DTSTART:" .$ical_dato_vaktstart."
DURATION:" .$ical_duration. "
UID:" . $ical_uid . "
CLASS:PUBLIC
CREATED:" .$ical_dato_datestamp. "
DESCRIPTION:" . $VaktInfo[5] . "
LOCATION:" . $VaktInfo[2] . "
ORGANIZER;CN=OPM.Minevakter:mailto:varsling@minevakter.no
LAST-MODIFIED:"  . $ical_dato_datestamp . "  
SUMMARY:" . $Descriptor . $VaktInfo[1] . "
END:VEVENT
END:VCALENDAR";

		//$mail_result=email($to,$ccMottager, $subject,$body,NULL);
		$mail_result=email($to,$ccMottager,$subject,$body,NULL,$ical_content );
		error_log($to, 0);
		error_log($ccMottager, 0);
		error_log($subject, 0);
		//error_log($ical_content, 0);
		
		if($mail_result) echo '<div class="alert alert-success" role="alert">Mail sent successfully</div><br/>';
        else error_log('Mail ERROR', 0);

        if ($definedUserVariables[8] == 1 &&  hentEpostAdresse($ToUID)[0] != $to){
        	$to = hentEpostAdresse($ToUID)[0];
        	$mail_result=email($to,$ccMottager,$subject,$body,$opts =[],$ical_content );
			if($mail_result){
			//    echo '<div class="alert alert-success" role="alert">Mail sent successfully</div><br/>';
			    error_log('Mail sent successfully to '. $to);
			}
		}
		
	}
//	if ($GLOBALS['testmode']== "NotTest" && $FromUID != $ToUID && $uid != 3){
//	if ($GLOBALS['testmode']== "NotTest" && $uid != 3){
	    //Oppdater turnusansvarlig med endringer:
    	$SkalVarsles = getVarsledeByGroup($VaktInfo[7]);
    	error_log(print_r($SkalVarsles,true), 0);
    	for ($n = 0; count($SkalVarsles) > $n; $n++){
            $ccMottager = $ical_content = "";
        	$FikkVakta = finnAnsattNavn($ToUID);
        	$to = $SkalVarsles[$n];
        	error_log($to, 0);
        	$subject = 'Turnusvarsel (Vaktendring):  ' . $VaktInfo[1] . ' den  ' . $Date;
        	$body = $VaktInfo[1] . ' den ' . $Date . ' er flyttet til '. $FikkVakta[0] . " " . $FikkVakta[1];
        	$mail_result=email($to,$ccMottager,$subject,$body,$opts =[],$ical_content );
        	if($mail_result) error_log('Mail sent successfully to '. $to);
        	else error_log('Mail ERROR', 0);
        }
//	}
}
?>