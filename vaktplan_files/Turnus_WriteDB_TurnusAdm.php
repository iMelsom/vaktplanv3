<?php 
//error_log(print_r($_POST,true),0);

require_once '../users/init.php';  //make sure this path is correct!
require_once '../users/includes/template/prep.php';
$uid = $user->data()->id;
//$UID = $_POST['UID'];
  //  if ($UID != '') $uid = $UID; //Just som copy/paste and sloppyness hardening.
    //if ($UID == '') $UID = $uid;
    
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
    
if(isset($user) && $user->isLoggedIn()){
        //error_log("blipp: " . $ToUID, 0);
        
	//Felles: 
    $WaddYaDoin = $_POST['UPDTP'];
    //error_log("blipp: " . $WaddYaDoin, 0);
    
    
    $RegDate = date('Y-m-d H:i:s', $TimeVar);
    $Today = date('Y-m-d', $TimeVar);
	$Tomorrow = date('Y-m-d', strtotime('Tomorrow'));
 	$guid = $_POST['GID'];

	//kompabilitet: 
	//if ($UID == '') $UID = 20; //Benytt Udekt intill jeg f�r oppdatert alle skript til � sende med UID for registrering. 

	
	//tur / Ferieavvik: 
	$Description = inputCleanse($_POST['DATA']);
    $Date = $_POST['FDATE'];
    $DayBeforeDate = date('Y.m.d', strtotime($Date . "- 1 day"));

	//Ferieavvik: 
	$Ferievakt = $_POST['USER'];
	
	//vakttype
    $vKode = inputCleanse($_POST['VKTKODE']);
    $vLen = $_POST['LENGTH'];
    $vLen = str_replace( ",", "\.", $vLen);//if decimal given with "," replace with "."
    $vStart = $_POST['START'];
    $vOnPremLen = $_POST['OLENGTH'];
    $vOnPremLen = str_replace( ",", "\.", $vOnPremLen);//if decimal given with "," replace with "."
    if ($vOnPremLen == "") $vOnPremLen = NULL;
    $vOnPremStart = $_POST['OSTART'];
    if ($vOnPremStart=="") $vOnPremStart = NULL;
    $vInfo = inputCleanse($_POST['SHINFO']);
    $vLoc = $_POST['JOBLOC'];
    $vVID = $_POST['VID'];
	
	//turfordeling: 
	$Offsett = $_POST['tOFFSET'];
	$ToUID = $_POST['tUID'];
		
	if ($WaddYaDoin == "NyVaktType"){
	    $timestamp = strtotime($vStart) + 60*60*$vLen;
    	$vEnd = date('H:i:s', $timestamp);
    	if ($vLen == '24') $vEnd = '24:00:00';
    	if ($vOnPremLen != NULL){
    	   $timestamp = strtotime($vOnPremStart) + 60*60*$vOnPremLen;
    	   $vOnPremEnd = date('H:i:s', $timestamp);
    	   if ($vOnPremLen == '24') $vOnPremEnd = '24:00:00';
    	}
	}
	//error_log($guid, 0);

	if ($WaddYaDoin == "NyTur"){
		if(isset($Description)){
		    $Description = str_replace( "&#10;", ",", $Description);//replace line feed with  ,
		    $Description = str_replace( "&#9;", ",", $Description);//replace tab with ,
		    $Description = preg_replace("/,\z/", "",$Description);//replace alst comma in string(if at very end) with ,
			$NyTur = explode(",",$Description); //Then make an array out of it
			}
			
		else {error_log("NyTurnus: Turnus_WriteDB_TurnusAdm.php:Descritpion:Error:Data not Given", 0);}

	    $tLength = count($NyTur)/7;	
		//1. : Registrer turnusen
		
        //sjekk for eksisterende tur: 
        $TourNow = finnTurnusutenSluttDato($guid);
        if (is_array($TourNow)){
            $QueryStatement = "UPDATE `Turnus` SET `tEndDate` = :tEndDate WHERE `Turnus`.`ID` = :ID;";
            $QueryData = array("tEndDate" => $DayBeforeDate, "ID" => $TourNow[0]);
            WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
        }
        
        $QueryStatement = 		 "INSERT INTO `Turnus`";
        $QueryStatement = $QueryStatement. "(`ID`, `Startdato`, `permission_group`, `tLengthWeeks`)"; 
        $QueryStatement = $QueryStatement. "VALUES (NULL, :Startdato, :permission_group, :tLengthWeeks);";
        $QueryData = array("Startdato" => $Date, "permission_group" => $guid, "tLengthWeeks" => $tLength);
        WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
        
        //Get new turnusID: 
        $NewTourNow = finnTurnusutenSluttDato($guid);
        
		//2.: Legg inn Turnusoppbygging (vaktliste)
		for ($i = 0; $i < count($NyTur); $i++){
    		$VaktID = FinnVaktID($NyTur[$i]);
    		if (!$NyTur[$i]  || $NyTur[$i] =="") $VaktID = FinnVaktID("I");

    		$QueryStatement = "INSERT INTO `TurnusTur`";
    		$QueryStatement = $QueryStatement. "(`VaktID`, `TurnusID`)"; 
    		$QueryStatement = $QueryStatement. "VALUES (:VaktID, :TurnusID);"; 
    		$QueryData = array("VaktID" => $VaktID, "TurnusID" => $NewTourNow[0]);
    		WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
		}
	}

	if ($WaddYaDoin == "NyVaktType" && $vVID == "" ){
	    $QueryStatement = " INSERT INTO `VaktType` (`ID`, `Vakt`, `Lokasjon`, `StartKL`, `SluttKL`, `Beskrivelse`, `Varighet`, `UsedBy`, `OnPremLength`, `OnPremStart`, `OnPremEnd`) ";
	    $QueryStatement = $QueryStatement . " VALUES (NULL, :Vakt, :Lokasjon, :StartKL, :SluttKL, :Beskrivelse, :Varighet, :UsedBy, :OnPremLength, :OnPremStart, :OnPremEnd);";
	    $QueryData = array("Vakt" => $vKode, "Lokasjon" => $vLoc, "StartKL" => $vStart, "SluttKL" => $vEnd, "Beskrivelse" => $vInfo, "Varighet" => $vLen, "UsedBy" => $guid, "OnPremLength" => $vOnPremLen, "OnPremStart" => $vOnPremStart, "OnPremEnd" =>$vOnPremEnd);
	    WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
		
		
		//$Query = $Query . " VALUES (NULL, '$vKode', '$vLoc', TIME('$vStart'), TIME('$vEnd'), '$vInfo', '$vLen', '$guid', '$vOnPremLen', TIME('$vOnPremStart'), TIME('$vOnPremEnd'));";
		//error_log($Query, 0); //print query out in error log
		//$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	}	
	
	if ($WaddYaDoin == "NyVaktType" && $vVID > 0){
	    $QueryStatement = "UPDATE `VaktType` SET ";
	    $QueryStatement = $QueryStatement. "
`Vakt` = :Vakt, 
`Lokasjon` = :Lokasjon, 
`StartKL` = :StartKL, 
`SluttKL` = :SluttKL, 
`Beskrivelse` = :Beskrivelse, 
`Varighet` = :Varighet, 
`onPremLength` = :OnPremLength, 
`onPremStart` = :OnPremStart, 
`onPremEnd` = :OnPremEnd"; 
	    $QueryStatement = $QueryStatement. " WHERE `VaktType`.`ID` = :ID";
	    $QueryData = array(
	        "Vakt" => $vKode, 
	        "Lokasjon" => $vLoc, 
	        "StartKL" => $vStart, 
	        "SluttKL" =>$vEnd,
	        "Beskrivelse" => $vInfo, 
	        "Varighet" => $vLen, 
	        "OnPremLength" => $vOnPremLen, 
	        "OnPremStart" => $vOnPremStart, 
	        "OnPremEnd" => $vOnPremEnd,
	        "ID" => $vVID);
		WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
		
		//error_log($Query , 0);
		//$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	}
	if ($WaddYaDoin == "FerieAvvik"){
		$LogWorthy = false;
		$Logged = false;
		$LogDate = $Date;
		if(isset($Description)){
		    $Description = str_replace( "&#10;", ",", $Description);//Old logstyle compat
		    $Description = str_replace( "&#9;", ",", $Description);//Old logstyle compat
		    $Description = preg_replace("/,\z/", "",$Description);
		    $Avvik = explode(",",$Description); // Go ahead! Make my ARRAY!!
		}
		for ($i = 0; $i < count($Avvik); $i++){
//		    error_log($i . ". Vakt: " . $Avvik[$i]);
		    $VaktID = FinnVaktID($Avvik[$i]);
//			error_log("Avvik: " . print_r($Avvik,true));
		    if ($VaktID != '' && $VaktID != '33' && $VaktID != '34'&& $VaktID != 'Err'){
		        $QueryStatement = 		 "INSERT INTO `vaktplan`.`Endringer`";
		        $QueryStatement = $QueryStatement. "(`ID`, `VaktID`, `Beskrivelse`, `Dato`, `RegDato`, `FraBrukerID`, `TilBrukerID`, `WhoDunnit`)";
		        $QueryStatement = $QueryStatement. "VALUES (NULL, :VaktID, 'F.Avvikl.', :Dato, :RegDato, NULL, :TilBrukerID, :WhoDunnit);";
		        $QueryData = array("VaktID" => $VaktID, "Dato" => $Date, "RegDato" => $RegDate, "TilBrukerID" => $Ferievakt, "WhoDunnit" => $uid);
				WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);

//				$Query = $Query. "VALUES (NULL, '$VaktID[0]', 'F.Avvikl.', '$Date', '$RegDate', NULL, '$Ferievakt', '$UID');"; //$date = 'Y-m-d' $RegDate = '>
//				error_log($Query , 0);
//				$Result = WriteMySQL($Query, $GLOBALS['Database'], $UID);

				$LogWorthy = true;
		    }
			//			if(!$Avvik[$i] || $Avvik[$i] == '' || $Avvik[$i] == ' ' || $VaktID[0] != '')$Date = date('Y-m-d',strtotime($Date . "+1 days"));
		$Date = date('Y-m-d',strtotime($Date . "+1 days"));
		}
		if ($LogWorthy == true && $Logged == false){ //Description is pre-cleansed, since it handles querydata as well
		    $ToLog = 'Turnusavvik=\"' . trim($Description, " ") . '\" User=' . $Ferievakt  . ' StartDate=' . $LogDate. ' Group=' . $guid;
		    $QueryStatement = "INSERT INTO `UpdateLog` (`ID`, `query`, `Dato`, `UserID`) VALUES (NULL, :query, :Dato, :UserID);";
		    $QueryData = array("query" => $ToLog, "Dato" => $RegDate, "UserID" => $uid);
			WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
			
			//$QueryStatement = "INSERT INTO `UpdateLog` (`ID`, `query`, `Dato`, `UserID`) VALUES (NULL, '$ToLog', '$RegDate', '$uid');";
			//$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
			$Logged = true;
		}
	}	
	
	if ($WaddYaDoin == "TurFordeling"){
		//$Offset kommer inn som [turnusnummer-Offsett], må brekkes opp i to nummer: 
	    $Offsett = explode("-", $Offsett); //[0]  =  GruppeID [1] = Offset 
		//error_log( print_r($Offsett, true), 0);
	    //1. Hvis $ToUID == 0, sett sluttdato på forrige eier.
	    if (SjekkTurEier($Offsett[1],$Offsett[0],$TimeVar) && $ToUID == '' && $Offsett[1] != 'L'){
	        $tCurrentOwner =  SjekkTurEier($Offsett[1],$Offsett[0],$TimeVar);
	        $QueryStatement = "UPDATE `TurnusFordeling` SET `TilDato` =  :TilDato WHERE ID = :ID;";
	        $QueryData = array("TilDato" => $Today, "ID" => $tCurrentOwner[0]);
	        WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	        //				error_log( $Query, 0);
	        //$Query = "UPDATE `TurnusFordeling` SET `TilDato` = '$Today' WHERE ID = $tCurrentOwner[0];";
	        //$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	    }
	    
	    //2. Sett tur til person, dersom turen er ubesatt:
	    if (!SjekkTurEier($Offsett[1],$Offsett[0],$TimeVar) && $ToUID != '' && $Offsett[1] != 'L'){
	        $QueryStatement = "INSERT INTO `TurnusFordeling`" ;
	        $QueryStatement = $QueryStatement . "(`ID`, `BrukerID`, `GroupID`, `Offset`, `FraDato`, `TilDato`)";
	        $QueryStatement = $QueryStatement . " VALUES (NULL, :BrukerID, :GroupID, :Offset, :FraDato, NULL);";
	        $QueryData = array("BrukerID" => $ToUID, "GroupID" => $Offsett[0], "Offset" => $Offsett[1], "FraDato" => $Tomorrow);
	        WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	        //				error_log( $Query, 0);
	        //$Query = $Query . " VALUES (NULL, '$ToUID', '$Offsett[0]', '$Offsett[1]', '$Tomorrow', NULL);";
	        //$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	    }
	    //3. Hvis $ToUID == 0 og offset = L, sett sluttdato på forrige leder.
	    if (finnLeder($Offsett[0], date("Y.m.d", $TimeVar), 3) && $ToUID == '' && $Offsett[1] == 'L'){
	        $tCurrentOwner =  finnLeder($Offsett[0], date("Y.m.d", $TimeVar), 3);
	        $QueryStatement = "UPDATE `ledere` SET `EndDate` =  :TilDato WHERE UID = :UID AND GrID = :GrID AND `EndDate` IS NULL;";
	        $QueryData = array("TilDato" => $Today, "UID" => $tCurrentOwner, "GrID" => $Offsett[0]);
	        WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	        error_log("TwriteDB240** " . print_r($QueryData, true), 0);
	        //				error_log( $Query, 0);
	        //$Query = "UPDATE `TurnusFordeling` SET `TilDato` = '$Today' WHERE ID = $tCurrentOwner[0];";
	        //$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	    }
	    
	    //4. Hvis offsett = L Sett person som leder, dersom rollen er ubesatt:
	    if (!finnLeder($Offsett[0], date("Y.m.d", $TimeVar), 3) && $ToUID != '' && $Offsett[1] == 'L'){
	        $QueryStatement = "INSERT INTO `ledere`" ;
	        $QueryStatement = $QueryStatement . "(`UID`, `GrID`, `StartDate`, `EndDate`, `Level`)";
	        $QueryStatement = $QueryStatement . " VALUES (:BrukerID, :GroupID, :FraDato, NULL, :Level);";
	        $QueryData = array("BrukerID" => $ToUID, "GroupID" => $Offsett[0], "FraDato" => $Tomorrow, "Level" => 3);
	        WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	       // error_log("TwriteDB253** " .  print_r($QueryData, true), 0);
	        //				error_log( $Query, 0);
	        //$Query = $Query . " VALUES (NULL, '$ToUID', '$Offsett[0]', '$Offsett[1]', '$Tomorrow', NULL);";
	        //$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	    }
	}
	if ($WaddYaDoin == "RollBack"){
	    $rollUID = $_POST['rollUID'];
	    $rollID = $_POST['rollID'];
	    
	    $Avvik = hentTurnusAvvik($rollID);

        $ThingsToRemove = array("Turnusavvik=", "\"", "User=","Group=", "StartDate=", "&#10;");

        $tempCleaned = str_replace( "; &", ";&", $Avvik[1]);//Old logstyle compat
	    $tempCleaned = str_replace($ThingsToRemove, "", $tempCleaned);
	    $tempAvvik = explode(" " , $tempCleaned);
	    
	    $tempData = str_replace( ";&", ";UE&", $tempAvvik[0]);
	    $tempData = preg_replace("/^&#9;/", "UE&#9;", $tempData);
	    $tempData = str_replace("&#9;", ",", $tempData);
	    $tempData = preg_replace("/,\z/", "",$tempData);
	    
	    $User = str_replace($ThingsToRemove, "", $tempAvvik[1]);
	    $GRP = str_replace($ThingsToRemove, "", $tempAvvik[2]);
	    $startDate = str_replace($ThingsToRemove, "", $tempAvvik[2]);
	    $startDateNIX = strtotime($startDate);
	    $VaktArray = explode("," ,$tempData);
	    
	    for ($k=0; $k < count($VaktArray); $k++){
	        if ($VaktArray[$k] != "UE"){
    	        $Date = date ("Y-m-d", strtotime($startDate . "+".$k." days"));
    	        
    	        //MÅ SKRIVES OM TIL Å BENYTTE PREPPA STATEMENT!
    	        $Statement = "SELECT `ID` FROM `Endringer` WHERE `TilBrukerID` = :TilBrukerID AND `Beskrivelse` = 'F.Avvikl.' AND `Dato` = :Dato";
    	        $QueryData=  array("TilBrukerID" =>$User, "Dato" => $Date);
    	        
    	        $Result = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
    	        //$Query = "SELECT `ID` FROM `Endringer` WHERE `TilBrukerID` = " .$User. " AND `Beskrivelse` = 'Frav�rsavvikling' AND `Dato` = '" .$Date. "'";
    	        // $Result = QueryMySQL($Query, $GLOBALS['Database'], $uid);
    	        for ($n = 0; $n < count($Result); $n++){
    	            $myrow[$n] = $Result[$n][0];
    	            //$n++;
    	        }
    	        //$myrow = mysqli_fetch_row($Result);
 
    	        
    	        $QueryStatement = "DELETE FROM `Endringer` WHERE `ID` = :ID;";
    	        $QueryData = array("ID" => $myrow[0]);
    	        WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
    	        
    	        //$Query = "DELETE FROM `Endringer` WHERE `ID` = ". $myrow[0];
    	        //$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
    	        //error_log($Query);
	        }
	    }
	    $QueryStatement = "DELETE FROM `UpdateLog` WHERE `ID` = :ID;";
	    $QueryData = array("ID" => $rollID);
	    WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	    
	    //$Query = "DELETE FROM `UpdateLog` WHERE `ID` = ". $rollID;
	    //$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	    //error_log($Query);
	    
	}
	//error_log("blipp: " . $WaddYaDoin, 0);
	if ($WaddYaDoin == "setEndTur"){
	    $Date = $_POST['FDATE'];
	    //if (!$Date || $Date == "") $Date = "NULL";
	    $DataID = $_POST['DATAID'];
	    $QueryStatement = "UPDATE `Turnus` SET `tEndDate` = :tEndDate WHERE `Turnus`.`ID` = :ID;";
	    $QueryData = array("tEndDate" => $Date, "ID" => $DataID);
	    if (!$Date || $Date == ""){
	        $QueryStatement = "UPDATE `Turnus` SET `tEndDate` = NULL WHERE `Turnus`.`ID` = :ID;";
	        $QueryData = array("ID" => $DataID);
	    }
	   // error_log("blipp: " . $_POST['DATAID'], 0); 
	   // error_log("blipp: " . $DataID, 0);
	   // error_log("blipp: " . $QueryStatement, 0);
	   // error_log(print_r($QueryData,true), 0);
	    
	    WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	    //$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	    //error_log("blipp: " . $WaddYaDoin, 0);
	   // error_log("blipp: " . $_POST['ENDTILD'], 0);
	    
	    if ($_POST['ENDTILD'] == 1){
	        $GRPID = $_POST['GID'];
	        $QueryStatement = "UPDATE `TurnusFordeling` SET `TilDato` = :TilDato WHERE GroupID = :ID AND `TilDato` IS NULL;";
	        $QueryData = array("TilDato" =>$Date, "ID" => $GRPID);
	         error_log("blipp: " . $QueryStatement, 0);
	         error_log(print_r($QueryData,true), 0);
	        WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	    }
	    
	}
	if ($WaddYaDoin == "endAnsattTur"){
	    $Date = $_POST['FDATE'];
	    if (!$Date || $Date == "") $Date = NULL;
	    $DataID = $_POST['DATAID'];
	    $QueryStatement = "UPDATE `TurnusFordeling` SET `TilDato` = :TilDato WHERE ID = :ID;";
	    $QueryData = array("TilDato" =>$Date, "ID" => $DataID);
	    WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	    //	    error_log( $Query);
	    //$QueryStatement = "UPDATE `TurnusFordeling` SET `TilDato` = '$Date' WHERE ID = $DataID;";
	    //$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	}
	if ($WaddYaDoin == "endLederTur"){
	    $Date = $_POST['FDATE'];
	    if (!$Date || $Date == "") $Date = NULL;
	    $DataID = $_POST['DATAID'];
	    $QueryStatement = "UPDATE `ledere` SET `EndDate` =  :TilDato WHERE ID = :ID;";
	    $QueryData = array("TilDato" =>$Date, "ID" => $DataID);
	    WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	    //error_log( $DataID);
	    //$QueryStatement = "UPDATE `TurnusFordeling` SET `TilDato` = '$Date' WHERE ID = $DataID;";
	    //$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	}
	if ($WaddYaDoin == "giveAnsattTur"){
	    $Date = $_POST['FDATE'];
	    $DataID = $_POST['DATAID'];
	    $QueryStatement = "INSERT INTO `TurnusFordeling`" ;
	    $QueryStatement = $QueryStatement . "(`ID`, `BrukerID`, `GroupID`, `Offset`, `FraDato`, `TilDato`)";
	    $QueryStatement = $QueryStatement . " VALUES (NULL, :BrukerID, :GroupID, :Offset, :FraDato, NULL);";
	    $QueryData = array("BrukerID" =>$ToUID, "GroupID" => $guid, "Offset" =>$Offsett, "FraDato" => $Date);
	    WriteMySQLPrepped($QueryData, $QueryStatement, $GLOBALS['Database'], $uid);
	    //	    error_log( $Query);
	    //$Query = $Query . " VALUES (NULL, '$ToUID', '$guid', '$Offsett', '$Date', NULL);";
	    //$Result = WriteMySQL($Query, $GLOBALS['Database'], $uid);
	}
}
?>