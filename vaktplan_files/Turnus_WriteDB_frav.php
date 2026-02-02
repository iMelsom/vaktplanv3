<?php 
//error_log(print_r($_POST, true), 0);

require_once '../users/init.php';  //make sure this path is correct!
require_once '../users/includes/template/prep.php';
if(isset($user) && $user->isLoggedIn()){
    $uid = $user->data()->id;
    //DEBUG: KEEP DISABLED WHEN NOT DEBUGGING
    //Pretend to be other user
    //error_log($uid);
    //if ($uid == 3) $uid = 73;
    //error_log($uid);
    
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
	
	
	//error_log(print_r($_POST, 1));
	
	
	
	$frvID = $_POST['FRVID'];
	$frvType = $_POST['FRVTYP'];
    $fromDate = $_POST['FDATE'];
    $toDate = $_POST['TDATE'];
    $AID = $_POST['UID'];
    $GUID = $_POST['GrpID'];
    
    $AvdNavn = hentAvdelingsnavn($GUID);
    
    if ($AID ==  "" || !$AID) $AID = $uid;
    $navn = finnAnsattNavn($AID);
    
    
    if ($_POST['RegUID'])$RegUID = $_POST['RegUID'];
    if (!$_POST['RegUID'])$RegUID = $uid;
    $SendEmail = $_POST['MAIL'];
    if ($frvType == 13) $SendEmail = "NONE";
    $Valid = $_POST['VALID'];
    $ResetXV = $_POST['RESETXV'];
    $Merknad = inputCleanse($_POST['DESCR']);
	if (!$frvID || $frvID == '') $Valid = 1;

	$frvInfo = oversettFraversGrunn($frvType);

	$SkalVarsles = array();
	if ($GUID == 0){
	    $AvailGUID = HentTilganger($AID);
	   // error_log(print_r($AvailGUID, 1));
	   // error_log(count($AvailGUID));
	    for($m = 0; $m < count($AvailGUID);$m++){
	        if ($AvailGUID[$m] != 13) {
	        $TempArray = getVarsledeByGroup($AvailGUID[$m]);
	        
	        $SkalVarsles = array_keys(
	            array_flip($SkalVarsles) + array_flip($TempArray)
	            );
	        
	        //array_push($SkalVarsles, ...$TempArray);
	       // $SkalVarsles =  array_merge($SkalVarsles, $AvailGUID[$m]);
	        }
	    }
	}

	else $SkalVarsles = getVarsledeByGroup($GUID);
//	error_log(print_r($SkalVarsles, 1)); 
	
	if ($Valid == 1 && (!$frvID || $frvID == '') ){
	   $QueryStatement =		"INSERT INTO `Fravaer`";
	   $QueryStatement = $QueryStatement. "(`ID`, `BrukerID`, `Grunn`, `FraDato`, `TilDato`, `regBy`, `RegStamp`, `stillValid`, `Merknad`)";
	   $QueryStatement = $QueryStatement. "VALUES (NULL, :BrukerID, :Grunn, :FraDato, :TilDato, :regBy, CURRENT_TIMESTAMP, :stillValid, :Merknad);";
	   $QueryData = array("BrukerID" =>$AID, "Grunn" => $frvType, "FraDato" =>$fromDate, "TilDato" => $toDate, "regBy" => $uid, "stillValid" => $Valid, "Merknad" => $Merknad);
	   WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
    }


if ($frvID || $frvID != '' || $Valid == 0){
    //Her må funskjon for å reverse tildelte vakter som følge av fravær inn. Dette er hvor vakter deaktiveres igjen. Dette må inkludere varslign til dem som "mister" ekstravakter
    if ($Valid == 0){
        
        //Sett vakt inaktiv: 
        $QueryStatement = "UPDATE `Fravaer` SET `stillValid` = '0' WHERE `Fravaer`.`ID` = :ID;";
        $QueryData = array("ID" => $frvID);
        WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);

        
        //Returner omfordelte vakter:
        //if ($ResetXV == 1) returnShifts($AID, $fromDate, $toDate)

    
    }
    if (($frvID || $frvID != '')&& $Valid != 0){
        $QueryStatement = "UPDATE `Fravaer` SET `Grunn`= :Grunn, `FraDato`= :FraDato, `TilDato`= :TilDato, `regBy`= :regBy, `RegStamp`=  CURRENT_TIMESTAMP,  `stillValid`= :stillValid,  `Merknad`= :Merknad WHERE `Fravaer`.`ID` = :ID;";
        $QueryData = array("Grunn" => $frvType, "FraDato" =>$fromDate, "TilDato" => $toDate, "regBy" => $uid, "stillValid" => $Valid, "Merknad" => $Merknad, "ID" => $frvID);
        WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
    }
    
}


if ($GLOBALS['testmode']== "NotTest" ) {

	    if ($Valid == 0){
			$subjText = 'Turnusvarsel: fjernet eller avlyst vaktavvik  ';
			$body= 'Turnusavvik " ' . $frvInfo[1] . '"  i perioden ' . $fromDate . ' til ' . $toDate . ' er avlyst eller fjernet.';
			
		}

		if ($Valid == 1){
			$subjText = 'Turnusvarsel: Registrert ';
			$body= 'Du er registrert med " ' . $frvInfo[1] . '"  i perioden ' . $fromDate . ' til ' . $toDate;
		}
		
		if ($SendEmail == "User" || $SendEmail == "All" || $frvType == 4 || $frvType == 10){
		    $definedUserVariables = chkUserVariables($AID);
		    if (($definedUserVariables[8] == '0') && ($definedUserVariables[5] != '' || $definedUserVariables[5] != false))$emailMottager =  $definedUserVariables[5];
		    if (($definedUserVariables[8] == '0') && ($definedUserVariables[5] == '' || $definedUserVariables[5] == false))$emailMottager = hentEpostAdresse($AID)[0];
		    if (($definedUserVariables[8] == '1') && ($definedUserVariables[5] != '' || $definedUserVariables[5] != false)){
		        $emailMottager = hentEpostAdresse($AID)[0];
		        $ccMottager =  $definedUserVariables[5];
		    }

		    $subject = $subjText . $frvInfo[1];
		    $mail_result=email($to,$ccMottager, $subject,$body,NULL);
		    if($mail_result) error_log('Mail sent successfully to '. $emailMottager);
			else error_log('Mail ERROR', 0);
			
		}
	}

	if (($SendEmail == "All" || $frvType == 4 || $frvType == 10) && $Valid != 0 ){
		for ($n = 0; count($SkalVarsles) > $n; $n++){
			$to = $SkalVarsles[$n];
	    	$subject = 'Turnusvarsel: Ekstravakt ' . $AvdNavn . ' utkalt PGA. sykdom';
			$body = $navn[0] . " " . $navn[1] . ' har ringt inn syk i perioden ' . $fromDate . ' til ' . $toDate;
			$ccMottager = false;
			if ($to != "udekt@minevakter.no") $mail_result=email($to,$ccMottager, $subject,$body,NULL);
//			error_log($body);
			if($mail_result) error_log('Mail sent successfully to '. $to);
			else error_log('Mail ERROR', 0);
		}

	}
	if ($frvType == 7 && $Valid != 0 ){
	    for ($n = 0; count($SkalVarsles) > $n; $n++){
	        $to = $SkalVarsles[$n];
	        $subject = 'Turnusvarsel:' . $navn[0] . ' har registrert seg utilgjengelig for ekstravakt i perioden '. $fromDate . ' til ' . $toDate;
	        $body = $navn[0] . " " . $navn[1] . ' har har registrert seg utilgjengelig for ekstravakt  i perioden ' . $fromDate . ' til ' . $toDate;
	        $ccMottager = false;
	        if ($to != "udekt@minevakter.no") $mail_result=email($to,$ccMottager, $subject,$body,NULL);
	        //error_log($body);
	        if($mail_result) error_log('Mail sent successfully to '. $to);
	        else error_log('Mail ERROR', 0);
	    }
	    
	}
	if ($frvType == 7 && $Valid == 0 ){
	    for ($n = 0; count($SkalVarsles) > $n; $n++){
	        $to = $SkalVarsles[$n];
	        $subject = 'Turnusvarsel:' . $navn[0] . ' er tilgjengelig igjen for ekstravakt i perioden '. $fromDate . ' til ' . $toDate;
	        $body = $navn[0] . " " . $navn[1] . ' er tilgjengelig igjen  for ekstravakt  i perioden ' . $fromDate . ' til ' . $toDate;
	        $ccMottager = false;
	        if ($to != "udekt@minevakter.no")$mail_result=email($to,$ccMottager, $subject,$body,NULL);
	        //error_log($body);
	        
	        if($mail_result) error_log('Mail sent successfully to '. $to);
	        else error_log('Mail ERROR', 0);
	    }
	    
	}
	if (($frvType != 4 && $frvType != 7 && $frvType != 10 && $frvType != 13 && $frvType != "") && $Valid != 0 ){
	    for ($n = 0; count($SkalVarsles) > $n; $n++){
	        $to = $SkalVarsles[$n];
	        $subject = 'Turnusvarsel:' . $navn[0] . 'har registrert fravær ('.$frvInfo.') i perioden '. $fromDate . ' til ' . $toDate;
	        $body = $navn[0] . " " . $navn[1] . ' har  registrert fravær ('.$frvInfo.') i perioden ' . $fromDate . ' til ' . $toDate;
	        $ccMottager = false;
	        if ($to != "udekt@minevakter.no")$mail_result=email($to,$ccMottager, $subject,$body,NULL);
	        //			error_log($body);
	        if($mail_result) error_log('Mail sent successfully to '. $to);
	        else error_log('Mail ERROR', 0);
	    }
	    
	}
	
}

function sendSlettevarsel($userID, $data, $fravDate){
    $definedUserVariables = chkUserVariables($userID);
    if (($definedUserVariables[8] == '0') && ($definedUserVariables[5] != '' || $definedUserVariables[5] != false))$emailMottager =  $definedUserVariables[5];
    if (($definedUserVariables[8] == '0') && ($definedUserVariables[5] == '' || $definedUserVariables[5] == false))$emailMottager = hentEpostAdresse($userID)[0];
    if (($definedUserVariables[8] == '1') && ($definedUserVariables[5] != '' || $definedUserVariables[5] != false)){
        $emailMottager = hentEpostAdresse($userID)[0];
        $ccMottager =  $definedUserVariables[5];
    }
    $subjText = 'Turnusvarsel: fjernet eller avlyst vaktavvik  ';
    $body= 'Ekstravakt " ' . $data[1] . '"  den ' . $fravDate .  ' er avlyst eller fjernet, på grunn av endringer i fraværsbehov.';
    $subject = $subjText . $data[1];
    if ($to != "udekt@minevakter.no")$mail_result=email($to,$ccMottager, $subject,$body,NULL);
    if($mail_result) error_log('Mail sent successfully to '. $emailMottager);
    else error_log('Mail ERROR', 0);
    
}

function returnShifts($uid, $Startdato, $enddate){
    $Tildelinger = hentTildelinger($uid, $Startdato); //Hent alle tildelte turer
    for ($a=0; strtotime($Startdato . " +".$a." day") <= strtotime($enddate)&& $Tildelinger [0]!= "NoTour"; $a++){
        $AktivNixDato = strtotime($Startdato . " +".$a." day");
        $AktivDato = date("Y-m-d", $AktivNixDato);
        
        for($c=0; $c < count($Tildelinger) && $Tildelinger[$c] != "NoTour";$c++){
            
            if ($Tildelinger[$c][5] != NULL && strtotime($AktivDato) > strtotime($Tildelinger[$c][5]) ){ //To check for changes in rotaoffset
                $Tildelinger[$c] = hentTildelinger($uid,$AktivDato,$Tildelinger[$c][2] );
            }
            
            if ($Tildelinger[$c] != "" && $Tildelinger[$c] != "NoTour"){
                //Husk � sjekke om turnusen er ny fra dag til dag.
                $TurID = finnGjeldendeTurnus($AktivNixDato, $Tildelinger[$c][2]);
                $Turnus = hentTurnus($TurID[0]);

                //Hent tildelte vakter:
                $Vakt =  HentVakt($uid, $AktivNixDato, $Turnus, $TurID[1],$Tildelinger[$c][2]); //Hentvakt henter ogs� vaktinfo

                //Legg vakter klar til skriving:
                if(is_array($Vakt)){
                    //Erstattes med databaseaktivitet
                      $VaktEndringsData =   sjekkVaktFlytt($Vakt[0], $AktivNixDato);
                      
                      //Slett endringen:
                      $QueryStatement = "DELETE FROM `Endringer` WHERE `ID` = :ID;";
                      $QueryData = array("ID" => $VaktEndringsData[0]);
                      WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
                      
                      //Send varsel om fjerning av ekstravakt: 
                      sendSlettevarsel($VaktEndringsData[5], $Vakt, $AktivNixDato); //må sette inn tilbruker fra vaktendringsdata, vaktdata, samt dato som behandles nå
                }
            }
        }
    }
    
}
?>