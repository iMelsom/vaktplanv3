<?php
function testForHoliday($Day){
    $dstr = date('m-d',$Day);
    $year = date('Y',$Day);
    $eastersundayCounter = easter_days($year);
    $eastersundayNix = strtotime("21 march " . $year .  " + " . $eastersundayCounter . " days" );
    $eastersundayDate = date('Y-m-d', $eastersundayNix);
    
    $Skjertorsdag = date('m-d', strtotime( $eastersundayDate. " -3 days"));
    $Langfredag = date('m-d', strtotime($eastersundayDate . " - 2 days"));
    $paskeaften = date('m-d', strtotime($eastersundayDate . " - 1 days"));
    $fpaskedag = date('m-d', strtotime($eastersundayDate));
    $apaskedag = date('m-d', strtotime($eastersundayDate . " + 1 days"));
    $krhimmelfart = date('m-d', strtotime($eastersundayDate . " + 39 days"));
    $fpinsedag = date('m-d', strtotime($eastersundayDate . " + 49 days"));
    $apinsedag = date('m-d', strtotime($eastersundayDate . " + 50 days"));
    
    //error_log($eastersundayDate);
    
    if($dstr == "01-01") return true;
    elseif($dstr == "12-24")return true;
    elseif($dstr == "12-25") return true;
    elseif($dstr == "12-26")return true;
    elseif($dstr == "05-01") return true;
    elseif($dstr == "05-17")return true;
    elseif($dstr == "12-24")return true;
    elseif($dstr == $Skjertorsdag)return true;
    elseif($dstr == $Langfredag)return true;
    elseif($dstr == $paskeaften) return true;
    elseif($dstr == $fpaskedag)return true;
    elseif($dstr == $apaskedag) return true;
    elseif($dstr == $krhimmelfart)return true;
    elseif($dstr == $fpinsedag)return true;
    elseif($dstr ==  $apinsedag)return true;
    else return false;

    
    
    /*
     * switch($beer)
{
    case 'tuborg';
    case 'carlsberg';
    case 'stella';
    case 'heineken';
        echo 'Good choice';
        break;
    default;
        echo 'Please make a new selection...';
        break;
}
     * 
     * 
     * //error_log(easter_days(2025));
     * første nyttårsdag: 1.1
     * skjærtorsdag: 1 påskedag - 3
     * Langfredgag: 1 påskedag - 2
     * Påskeaften: 1 påskedag -1 
     * 1 påskedag:  $eastersunday
     * 2 påskeedag: 1 påskedag +1
     * kr.himmelfart: 1 påskedag +39
     * 1 pinsedag: 1 påskedag + 49
     * 2 pinsedag: 1 påskedag + 50
     * julaften: 24.12
     * første juledag: 25.12
     * andre juledag: 26.12
     * 1 mai
     * 17 mai
     * 
     * 
     */
    
    
/*
    $dstr = date('Y-m-d',$Day);
	$workday = new Date($dstr);
	$year = date('Y', $Day);

	//set up filter
	$norway = Date_Holidays::factory('Norway', $year, 'en_EN');
if (Date_Holidays::isError($norway)) {
    die('Factory was unable to produce driver-object');
}
if ($norway->isHoliday($workday)) {
   return true;
}
else return false;
*/
return false;
}

//Hent ut aktiv turnus for en gitt bruker i en gitt avdeling for en gitt dato
function turnusInfo2($UID, $nixtime, $GUID){  
    	$Dato = date('Y-m-d', $nixtime);
		$Statement  = "SELECT * FROM `TurnusFordeling` WHERE `BrukerID` = :BrukerID AND `GroupID` = :GroupID AND `FraDato` <= :Dato AND (`TilDato` >= :Dato OR `TilDato` IS NULL) ORDER BY `ID` DESC LIMIT 1";
		$QueryData = array("BrukerID" => $UID, "GroupID" => $GUID, "Dato" => $Dato);
		$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		for ($n = 0; $n < count($data); $n++){
		    $result[$n] = $data[$n];
		}
		return $result[0];
}

function turnusStartet($UID, $nixtime, $GUID){
    $Statement  = "SELECT * FROM `TurnusFordeling` WHERE `BrukerID` = :BrukerID AND `GroupID` = :GroupID AND `FraDato` <= :Dato AND (`TilDato` >= :Dato OR `TilDato` IS NULL) ORDER BY `ID` DESC LIMIT 1";
    $QueryData = array("BrukerID" => $UID, "GroupID" => $GUID, "Dato" => $Dato);
    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
    if(is_array($data)) return true;
    else return false;
}

//Hent en gruppes gjeldende turnus for oppgit tidspunkt
function finnGjeldendeTurnus($unixtime, $guid){
    $Dato = date('Y-m-d', $unixtime);
    
    $Statement = "SELECT * FROM `Turnus` WHERE `permission_group` LIKE :permission_group AND `Startdato` <= :Dato ORDER BY `StartDato` DESC LIMIT 1";
    $QueryData = array("permission_group" => $guid, "Dato" => $Dato);
    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
/*    for ($n = 0; $n < count($data); $n++){
        $result[$n] = $data[$n];
    }*/
    return $data[0];
}
function SjekkAlias($guid){
    $Statement = "SELECT `Alias` FROM `grpAlias` WHERE `GRID` LIKE :permission_group";
    $QueryData = array("permission_group" => $guid);
    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
    for ($n = 0; $n < count($data); $n++){
        $result[$n] = $data[$n];
    }
    return $result[0];
}

//Spesialversjon av funksjonen over som henter turnuser uten sluttdato for en gitt gruppe
function finnTurnusutenSluttDato($guid){
    $Statement = "SELECT * FROM `Turnus` WHERE `permission_group` LIKE :permission_group AND `tEndDate` IS NULL ORDER BY `StartDato` DESC LIMIT 1";
    $QueryData = array("permission_group" => $guid);
    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
    for ($n = 0; $n < count($data); $n++){
        $result[$n] = $data[$n];
    }
    return $result[0];
}

//Funksjon som brukes i adminpanel for turnus for å finne de siste tre lagede turer, som hjelper å bestemme forrige, nåværende og neste tur. 
function finnToppTreTurnus($guid){
    $Statement = "SELECT * FROM `Turnus` WHERE `permission_group` LIKE :permission_group ORDER BY `StartDato` DESC LIMIT 3";
    $QueryData = array("permission_group" => $guid);
    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
    for ($n = 0; $n < count($data); $n++){
        $result[$n] = $data[$n];
    }
    return $result;
}

//Hent reigstret grunndata for en turnus (gruppe, oppstart, uker)
function hentTurnusData($turnusID){ //Henter registerdata for Turnus (ID, startdato, avdeling og permission_group)
        $Statement = "SELECT * FROM `Turnus` WHERE `ID` = :ID";
		$QueryData = array("ID" => $turnusID);
		$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		for ($n = 0; $n < count($data); $n++){
		    $result[$n] = $data[$n];
		}
		return $result[0];
}

//Hent beskrivelse av en oppgitt vakt
function sjekkVaktType($vaktID){
        $Statement = "SELECT * FROM `VaktType`WHERE `ID` LIKE  :ID;";
        $QueryData = array("ID" => $vaktID);
		$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		for ($n = 0; $n < count($data); $n++){
		    $result[$n] = $data[$n];
		}
		return $result[0];
}

//Hent en liste over alle vakttyper en gitt avdleing bruker, tar ikk emed "I" (Ikke på vakt)
function hentAlleVaktTyper($guid){
        $Statement = "SELECT * FROM `VaktType` WHERE `UsedBy` LIKE :UsedBy OR `UsedBy` = 0" ;
		$QueryData = array("UsedBy" => $guid);
		$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		for ($n = 0; $n < count($data); $n++){
		    $result[$n] = $data[$n];
		}
		return $result;	
		//error_log("Old: " .print_r($result, true),0);
		
}

//hent vaktene en turnustur er bygd opp av
function hentTurnus($turnusID){
    $Statement = "SELECT * FROM `TurnusTur` WHERE `TurnusID` LIKE :TurnusID ORDER BY `ID` ASC";
    $QueryData = array("TurnusID" => $turnusID);
    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
    for ($n = 0; $n < count($data); $n++){
        $result[$n] = $data[$n][1];
    }
    //error_log("New: " .print_r($result, true),0);
    return $result;
}

//Beregn faktisk posisjon i tur: 
function Turnus_Pos($Tur_Lengde, $offset_current){
		while ($offset_current >= $Tur_Lengde){
			$offset_current = $offset_current - $Tur_Lengde;
		}
		if ($offset_current < 0) $offset_current = 0;
		return $offset_current ;
	}
	
function HentVaktUdekket($Offset, $unixtime, $turnus, $Tur_Startdato){
    $Vakt = ''; //make sure variable is empty
    $TurLengde = count($turnus); //get the total lenght of rota in days (number of shift codes)
  
    //Calculate postition in rota (offset):
    $offset = ($Offset*7) - 7; //(turnummer (ukenummer i grunnturnus) * 7 (for å få antall dager) - 7 (for starten av uka)
    if ($offset < 0) $offset = 0; //if the above returns a negative number, we where suppused to be at zero
    
    //Get shift code at offset postition:
    $Tur_Alder_iDager = DagerSiden($Tur_Startdato, $unixtime);
    $offset_current = $offset + $Tur_Alder_iDager;
    $Tur_Pos_oper = Turnus_Pos($TurLengde,$offset_current) ;//Unadjustet position in turnus
    $Curr_Vakt = $turnus[$Tur_Pos_oper];
    $Vakt = sjekkVaktType($Curr_Vakt);
    return $Vakt;
}


//Sjekk om oppgit person har registrert fravær på et gitt tidspunkt
		function sjekkFraver($uid, $ts, $incHjemmekont = True){
			$dato = date('Y-m-d', $ts);
			$Hjemmekontstatement = "";
			if (!$incHjemmekont)$Hjemmekontstatement = " AND `Grunn` != 9 AND `Grunn` != 13"; 
			$Statement= "SELECT * FROM `Fravaer` WHERE `BrukerID` = :BrukerID AND (`FraDato` <= :Dato AND `TilDato` >= :Dato)" . $Hjemmekontstatement . " AND `StillValid` LIKE 1 ORDER BY `ID` DESC";
			$QueryData = array("BrukerID" => $uid, "Dato" => $dato);
			$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
			for ($n = 0; $n < count($data); $n++){
			    $result[$n] = $data[$n];
			}
			return $result[0];

		}
		
		function sjekkFraver2($fravArray, $ts){
		    //$dato = date('Y-m-d', $ts);
		    $result = false;
		    
		    foreach($fravArray as $key => $value){
		    //while (list($key, $value) = each($fravArray)) {
		       //error_log($key);
		       //error_log(print_r($value, true),0);
		       if (strtotime($key) <= $ts && strtotime($value[1]) >= $ts){
		            $result = $value;
		            //error_log(print_r($result, true),0);
		       }
		    }
		    return $result;
		}
		
		function hentFraversliste($fDato, $tDato){
		    
		    //SELECT `FraDato`,`TilDato`,`BrukerID`,`Grunn`,`Merknad` FROM `Fravaer` WHERE (`TilDato` >= "2024-01-23" AND `TilDato` <= "2024-02-29") OR (`FraDato` >= "2024-01-23" AND `FraDato` <= "2024-02-29") AND `stillValid` = 1 ORDER BY `FraDato` ASC,`BrukerID` ASC 
		    $Statement="SELECT `FraDato`,`TilDato`,`BrukerID`,`Grunn`,`Merknad`,`ID` FROM `Fravaer` WHERE ((`TilDato` >= :fDato AND `TilDato` <= :tDato) OR (`FraDato` >= :fDato AND `FraDato` <= :tDato))  AND `stillValid` = 1 ORDER BY `FraDato` ASC,`BrukerID` ASC";
		    $QueryData = array( "fDato" => $fDato,"tDato" => $tDato);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    //error_log(print_r($data,true),0);
		    $result = Array();
		    for ($n = 0; $n < count($data); $n++){
		        $result[ $data[$n][2]][$data[$n][0]] = $data[$n];
		    }
		    return $result;
		    
		}

//sjekk hva fraværskoden betyr: 		
		function  oversettFraversGrunn($ID){
		    $Statement= "SELECT * FROM `Fravaer_grunner` WHERE `ID` = :ID";
		    $QueryData = array("ID" => $ID);
			$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
			for ($n = 0; $n < count($data); $n++){
			    $result[$n] = $data[$n];
			}
			return $result[0];
		}

//Sjekk om en gitt bruker er tildelt en ekstravakt på et gitt tidspunkt
		function sjekkEkstravakt($uid, $ts){
			$dato = date('Y-m-d', $ts);
			$Statement= "SELECT  * FROM `Endringer` WHERE  `Dato` = :Dato AND `TilBrukerID` = :TilBrukerID ORDER BY `RegDato` ASC";
			$QueryData = array("Dato" => $dato, "TilBrukerID" => $uid);
			$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
			for ($n = 0; $n < count($data); $n++){
			    $result[$n] = $data[$n];
			}
			return $result;
		}
		
		function hentVaktendringer($fDato, $tDato){
		    $Statement= "SELECT `Dato`,`VaktID`,`TilBrukerID`,`Beskrivelse`,`ID` FROM `Endringer` WHERE `Dato` >= :fDato && `Dato` <= :tDato ORDER BY `RegDato` ASC";
		    $QueryData = array("fDato" => $fDato, "tDato" => $tDato);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    $result = Array();
		    for ($n = 0; $n < count($data); $n++){
		        $result[ $data[$n][0]][$data[$n][1]] = $data[$n];
		    }
		    return $result;
		}

//sjekk om det er ihverksatt alternative vakttyper		
		function SjekkAltVakt($ts, $Gruppe, $Rolle){
//		    SELECT `Endringer`.`TilBrukerID` FROM `Endringer`,`VaktType` WHERE `Endringer`.`Dato` = "2022-11-10" AND `Endringer`.`VaktID` LIKE `VaktType`.`ID` AND `VaktType`.`StartKL` <= "12:00:00" 
//            AND `Endringer`.`TilBrukerID` != 20 AND `VaktType`.`Lokasjon` LIKE "OPM" AND `VaktType`.`UsedBy` = "4" AND `VaktType`.`Beskrivelse` LIKE "%NO1%" 
		    $dato = date('Y-m-d', $ts);
		    $tid = date('H:i:s', $ts);
		    $Statement = "SELECT `Endringer`.`TilBrukerID`,`Endringer`.`VaktID`, `Endringer`.`Dato`  FROM `Endringer`,`VaktType` WHERE `Endringer`.`Dato` = :Dato"; 
            $Statement = $Statement . " AND `Endringer`.`VaktID` LIKE `VaktType`.`ID` AND (`VaktType`.`StartKL` <= :StartKL OR (`VaktType`.`SluttKL` >= :StartKL AND `VaktType`.`SluttKL` < '09:00:00'))"; 
            $Statement = $Statement . " AND `Endringer`.`TilBrukerID` != 2 AND `VaktType`.`Lokasjon` LIKE :Lokasjon";
            $Statement = $Statement . " AND `VaktType`.`UsedBy` = :UsedBy AND `VaktType`.`Beskrivelse` LIKE :Beskrivelse";
            $QueryData = array("Dato" => $dato, "StartKL" => $tid, "Lokasjon" => "OPM", "UsedBy" => $Gruppe,  "Beskrivelse" => "%" . $Rolle . "%");
            $Statement = $Statement . " ORDER BY `Endringer`.`RegDato` DESC LIMIT 1";
           // error_log("turfun 189: " .$Statement);
           // error_log("turfun 190: " .print_r($QueryData, true),0);
            $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
            $result = $data[0];
            return $result;
		}
		
//Sjekk om en ekstravakt eller endring fortsatt er gyldig (om den er viderefordelt)		
		function fortsattGyldig($endrID, $vid, $uid, $ts){
			$dato = date('Y-m-d', $ts);
			$Statement= "SELECT * FROM `Endringer` WHERE  `VaktID` = :VaktID AND `Dato` = :Dato ORDER BY `RegDato` DESC LIMIT 1";
			$QueryData = array("VaktID" => $vid, "Dato" => $dato);
			$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
			for ($n = 0; $n < count($data); $n++){
			    $result[$n] = $data[$n];
			}
			if ($result[0][0] != $endrID) return false;
			else return true;
//			error_log("New: " .print_r($result[0][0], true),0);
			
		}

//Sjekk om en gitt vakt er flyttet til annen operatør 		
		function sjekkVaktFlytt($vid, $ts){
		//her m� det også sjekkes om evt vaktflytt gjelder forrige ansatte i vakta. Har det blitt nyansatt s� m� bytta vakter f�res tilbake
			$dato = date('Y-m-d', $ts);
			$Statement= "SELECT * FROM `Endringer` WHERE  `Dato` = :Dato AND `VaktID` = :VaktID ORDER BY `RegDato` DESC LIMIT 1";
			$QueryData = array("Dato" => $dato, "VaktID" => $vid);
			$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
			$result = false;
			if (count($data) >0){
    			for ($n = 0; $n < count($data); $n++){
    			    $result = $data[$n];
    			}
			}
			//error_log("New: " .print_r($result, true),0);
			//if ($vid == 43) error_log("t-func. 204: " ."Vaktid: " .$vid . " dato: " . $dato);
			return $result;
		}

//hvordan er turnusen fordelt mellom de forskjellige operatørene. (bruker offsett med antall uker). Brukes i Turnusadmin
		function TurnusFordeling($TurnusID, $TimeVar){
		    $dato = date('Y-m-d', $TimeVar);
		    $Statement = "SELECT * FROM `TurnusFordeling` WHERE  `GroupID` = :GroupID AND `FraDato` <= :Dato AND (`TilDato` IS NULL OR `TilDato` >= :Dato) ORDER BY `Offset` ASC";
		    $QueryData = array("GroupID" => $TurnusID, "Dato" => $dato);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n];
		    }
//		    error_log("New: " .print_r($result, true),0);
		    return $result;
		}
//Hent ansattliste for gitt avdeling ut i fra rettigheter
		function hentAnsatte2($AvdID){
		    $Statement= "SELECT `users`.`id`,`users`.`fname`,`users`.`lname`  FROM `users`,`user_permission_matches` WHERE  `users`.`id` = `user_permission_matches`.`user_id` AND `user_permission_matches`.`permission_id` = :AvdID ORDER BY `fname` ASC";
		    $QueryData = array("AvdID" => $AvdID);
		    //error_log("New: " .$Statement);
		    //error_log("New: " .print_r($QueryData, true),0);
		    
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
		    for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n];
		    }
		    return $result;
		}

//hent ansattliste fra turfordeling og fra "intour"	gitt tidspunkt	Brukes i Fulltour
		function hentAnsatte3($AvdID , $startDate){ //Hent ansatte som er satt i turnus, b�de fra "inTour" og tilegnet i turnus i l�pet av perioden
		    
		    //Get users inTour: 
		    $Statement  = "SELECT * FROM `User_inTour` WHERE `GUID` = :AvdID AND `inTour_bool` = 1";
		    $QueryData = array("AvdID" => $AvdID);
		    $data = QueryMySQLPrepped($QueryData, $Statement,$GLOBALS['Database']);
		    for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n][1];
		    }
		    //error_log($Statement);
		    //error_log(print_r($QueryData, true),0);
		    
		    // get users that has had a tour in the given period: 
		    $Statement = "SELECT * FROM `TurnusFordeling` WHERE `GroupID` = :AvdID AND (`TilDato` >= :TilDato OR `TilDato` IS NULL)";
		    $QueryData = array("AvdID" => $AvdID, "TilDato" => $startDate);
		    $data = QueryMySQLPrepped($QueryData, $Statement,$GLOBALS['Database']);
		    for ($m = 0; $m < count($data); $m++){
		        if (!in_array($data[$m][1], $result)){//Hvis brukeren allerede eksiterer i lista, hopp over (mer korrekt: legg bare inn om brukeren ikke eksisterer i lista)
		            $result[$n] = $data[$m][1];//Oppdater med brukerinfo fra turnusfordeling
		            $n++;
		        }
		    }
		    //error_log($Statement);
		    //error_log(print_r($QueryData, true),0);
		    
		   // error_log(print_r($result, true),0);
		    
		    //get users name, put both users names and userID in array: 
		    $p =0;
//		    $ledere = getVarsledeIDByGroup($AvdID);
		    $ledere = finnLedere($AvdID, $startDate);
		    
		    //error_log(print_r($ledere, true),0);
		    for ($o =0; $o < count($result); $o++){
		        
		        //error_log($result[$o]);
		        //error_log($AvdID);
		        //error_log($startDate);
		        //error_log(print_r($ledere, true),0);
		        if (is_array( harTur($result[$o],$AvdID, $startDate)) || is_array( harSnartTur($result[$o],$AvdID, $startDate)) || (is_array($ledere) && in_array($result[$o], $ledere))){
    		        $tempArray = finnAnsattNavn($result[$o]);
    		        $returndata[$p] = array($result[$o],$tempArray[0],$tempArray[1] );
    		        $p++;
		        }
		    }
		    if (is_array($ledere)){
		        for ($o =0; $o < count($ledere); $o++){
    		        if (!in_array_r($ledere[$o], $returndata) && $ledere[$o] != ''){
    		            $tempArray = finnAnsattNavn($ledere[$o][0]);
    		            $returndata[$p] = array($ledere[$o][0],$tempArray[0],$tempArray[1] );
        		        $p++;
        		    }
		        }
		    }
		   //error_log(print_r($returndata, true),0);
		    
		    return $returndata;
		}
		function in_array_r($needle, $haystack, $strict = false) {
		    foreach ($haystack as $item) {
		        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
		            return true;
		        }
		    }
		    
		    return false;
		}
		function finnAnsattNavn($UID){
		    $Statement= "SELECT `fname`,`lname` FROM `users` WHERE  `id` = :id";
		    $QueryData = array("id" => $UID);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
		    return $data[0];
		}
		function finnLeder($GrID, $Date, $Level = 3){
		    $Statement= "SELECT `UID` FROM `ledere` WHERE `GrID` = :grid AND `Level` = :level AND`StartDate` <= :date AND (`EndDate`>= :date OR `EndDate` IS NULL)";
		    $QueryData = array("grid" => $GrID, "level" => $Level, "date" => $Date );
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    //error_log(print_r($data, true),0);
		    return $data[0][0];
		}
		function finnLederturID($GrID, $Date, $Level = 3){
		    $Statement= "SELECT `ID` FROM `ledere` WHERE `GrID` = :grid AND `Level` = :level AND`StartDate` <= :date AND (`EndDate`>= :date OR `EndDate` IS NULL)";
		    $QueryData = array("grid" => $GrID, "level" => $Level, "date" => $Date );
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    //error_log(print_r($data, true),0);
		    return $data[0][0];
		}
		function finnLedere($GrID, $Date){
		    $Statement= "SELECT `UID` FROM `ledere` WHERE `GrID` = :grid AND `StartDate` <= :date AND (`EndDate`>= :date OR `EndDate` IS NULL)";
		    $QueryData = array("grid" => $GrID,  "date" => $Date );
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    //error_log($Statement);
		    //error_log(print_r($QueryData, true),0);
		    //error_log(print_r($data, true),0);
		    // return $data[0];
		    for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n];
		    }
		    return $result;
		}
		function Sjekk2FA($UID){
		    $Statement= "SELECT `twoEnabled` FROM `users` WHERE  `id` = :id";
		    $QueryData = array("id" => $UID);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
		    return $data[0];
		}
		function hvilkenDagErDetIdag($dato, $mod, $ViewType){
			if ($ViewType == "Fixed") $ts = strtotime(date('Y', $dato) . 'W' . date('W', $dato) . $mod);
			if ($ViewType == "Fluid") $ts = strtotime(date ('Y-m-d', strtotime('-2 day', $dato)) . ' + ' .  $mod . ' days');
			if ($ViewType == "Month") $ts = strtotime(date('Y-m-01',$dato));
			$JaDetErDet[0] = strftime("%A", $ts);
			$JaDetErDet[1] = $ts;
			return $JaDetErDet;
		}
		
		function hentEpostAdresse($UID){
		    $Statement= "SELECT `email` FROM `users` WHERE  `ID` = :ID";
			$QueryData = array("ID" => $UID);
			$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
			return $data[0];
		}
		
		function hentFrav($uid, $Year, $sorted = false) {
    		$FraDato = $Year . "-01-01";
    		$Statement = "SELECT * FROM `Fravaer` WHERE `BrukerID` = :BrukerID AND `FraDato` >= :FraDato AND `stillValid` = 1 ORDER BY ";
    		if ($sorted === true) $Statement = $Statement . "`Fravaer`.`Grunn` ,";
    		$Statement = $Statement ." `Fravaer`.`FraDato` ASC ";
    		$QueryData = array("BrukerID" => $uid,"FraDato" => $FraDato);
    		$data = QueryMySQLPrepped($QueryData, $Statement,$GLOBALS['Database']);
    		for ($n = 0; $n < count($data); $n++){
    		    $result[$n] = $data[$n];
    		}
    		return $result;
		}
		
		function DagerSiden($fra, $til){
		    $fra = date_create($fra);
		    $til = date_create(date('Y-m-d',$til));
		    $output = date_diff($fra, $til);
		    $output = $output->format("%a");
		    return $output;
		}
		
		function beregnDager($dag1, $dag2){
			$start = strtotime($dag1);
			$end = strtotime($dag2);
			$datediff = $end  - $start;
			$numtemp =  floor($datediff / (60 * 60 * 24));
			$numtemp +=1;
			if ($numtemp >= 7){
			for($n = 1; $numtemp >= $n; $n++){
				$numtemp -= 7;
			}
			
			$numDays = $numtemp + (5 * ($n-1));
			}
			else $numDays = $numtemp;
			return $numDays;
		}
		
		function hentFravTyper(){
		    $Statement = "SELECT * FROM `Fravaer_grunner`";
		    $QueryData = array();
		    $data = QueryMySQLPrepped($QueryData, $Statement,$GLOBALS['Database']);
		    return $data;
		}
		
		function sjekkSuperbruker($uid){
		    $Statement= "SELECT `user_id` FROM `user_permission_matches` WHERE  `user_id` = :user_id AND  (`permission_id` = (SELECT `id` FROM `permissions` WHERE  `name` = 'SuperUser'))LIMIT 1";
			$QueryData = array("user_id" => $uid);
			$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
			if(count($data) > 0) return true;
			else return false;
		}
		
		//Sjekk hvilken ID en vakt har ut ifra vaktnavnet
		function FinnVaktID($Vaktnavn){
		   $Vaktnavn = strtoupper($Vaktnavn);
		   $Vaktnavn = str_replace(' ', '', $Vaktnavn);
	   	   $Vaktnavn = trim($Vaktnavn);

	   	   $Statement = "SELECT `ID` FROM `VaktType` WHERE `Vakt` = :Vakt;";
	   	   $QueryData = array("Vakt" => $Vaktnavn);
	   	   $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
	   	  // error_log("FinnvaktID_testresult: " . print_r($data, true), 0);
	   	   
	   	   if(count($data) <= 0) $data[0][0] = "Err";
	   	   return $data[0][0];
		}
		
		function SjekkTurEier($turOffset, $grpid, $TimeVar){
		    $Now = Date('Y-m-d', $TimeVar);
			$Statement= "SELECT * FROM `TurnusFordeling` WHERE  `GroupID` = :GroupID AND `Offset` = :Offset AND   `FraDato` < :Dato AND ( `TilDato` IS NULL OR `TilDato` > :Dato) ORDER BY `FraDato` ASC LIMIT 1;";
			$QueryData = array("GroupID" => $grpid, "Offset" => $turOffset, "Dato" => $Now);
			$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
			if(count($data) > 0)return $data[0];
			else return false;
		}
		
		function sjekkFremtidigTur($UID, $GrUID){
		    $Now = Date('Y-m-d', time());
		    $Statement= "SELECT * FROM `TurnusFordeling` WHERE `BrukerID` = :UID AND `GroupID` = :GroupID AND `FraDato` > :FraDato";
		    $QueryData = array("UID" => $UID, "GroupID" => $GrUID, "FraDato" => $Now);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    
//		    error_log("functions 411: " . print_r($data, true), 0);
		    
		    if(count($data) > 0)return $data[0];
		    
		    else return false;
		}

		function sjekkOffset($offset, $turid, $dato){
		    //$sql= "SELECT * FROM `TurnusFordeling` WHERE  `GroupID` = '" . $turid .  "' AND `Offset` = '" . $offset .  "' AND `FraDato` <= '$dato' AND ( `TilDato` IS NULL OR `TilDato` >= '$dato') ORDER BY `FraDato` ASC LIMIT 1;";
		    $Statement= "SELECT * FROM `TurnusFordeling` WHERE  `GroupID` = :GroupID AND `Offset` = :Offset AND `FraDato` > :FraDato AND ( `TilDato` IS NULL OR `TilDato` >=  :TilDato) ORDER BY `FraDato` ASC LIMIT 1;";
		    $QueryData = array( "GroupID" => $GrUID, "Offset" => $offset, "FraDato" => $dato, "TilDato" => $dato);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']); // kolonner i retur: 0:id 	1:BrukerID 2:TurnusID 3:Offset 4:FraDato 5:TilDato
		    if (is_array($data)) return $TurInfo;
		    else return false;
		    
		}
		
		//Sjekk om en ansatt er tildelt en tur i en turnus på et gitt tidspunkt. Brukes i turnusadmin
		function harTur($uid,$grid, $dato, $Limit = 1){
	   // function harTur($uid,$grid, $turid = "", $dato, $Limit = 1){
		        if ($Limit == 1) $Limit =  "ASC LIMIT 1";
		    if ($Limit == 0) $Limit =  "";
		    $Statement= "SELECT * FROM `TurnusFordeling` WHERE  `BrukerID` =:BrukerID AND `GroupID` = :GroupID AND `FraDato` <= :Dato AND ( `TilDato` IS NULL OR `TilDato` > :Dato) ORDER BY `FraDato`". $Limit .";";
		    $QueryData = array("BrukerID" => $uid, "GroupID" => $grid, "Dato" => $dato);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    if(count($data) > 0)return $data[0];
		    else return false;
		    
		}
		function harSnartTur($uid,$grid, $dato, $Limit = 1){
		    if ($Limit == 1) $Limit =  "ASC LIMIT 1";
		    if ($Limit == 0) $Limit =  "";
		    $Statement= "SELECT * FROM `TurnusFordeling` WHERE  `BrukerID` = :BrukerID AND `GroupID` = :GroupID AND `FraDato` > :Dato ORDER BY `FraDato`". $Limit .";";
		    $QueryData = array("BrukerID" => $uid, "GroupID" => $grid, "Dato" => $dato);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    if(count($data) > 0)return $data[0];
		    else return false;
		    
		}
		
		function HentTilganger($uid){
		    $Statement= "SELECT `permission_id` FROM `user_permission_matches` WHERE  `user_id` = :BrukerID AND `permission_id` > '4';";
			$QueryData = array("BrukerID" => $uid);
			
			//error_log($Statement);
			//error_log(print_r($QueryData,true),0);
			
			$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
			if(count($data) > 0){
			    for ($n = 0; $n < count($data); $n++){
			         $result[$n] = $data[$n][0];
			    }
			    return $result;
			}
			else return false;
		}
		
		function ErDetRettGruppe($vid,$guid){
		    $Statement= "SELECT `ID` FROM `VaktType` WHERE  `ID` = :ID AND  `UsedBy` = :UsedBy LIMIT 1";
		    $QueryData = array("ID" => $vid, "UsedBy" => $guid);
		    
		   // error_log(print_r($QueryData, true),0);
		    
			$data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
			if(count($data) > 0)return $data[0];
			
			else return false;	
		
		}
//Hent en spesifikk avdeling etter gitt gruppeid		
		function hentAvdelingsnavn($guid){
		    $Statement= "SELECT `name` FROM `permissions` WHERE `id` LIKE :id;";
		    $QueryData = array("id" => $guid);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
		    //error_log(print_r($data,true),0);
		    if(count($data[0]) > 0)return $data[0][0];
		    else return false;
		}
//finn gruppeID til en gitt avdeling		
		function getGUID($GroupName){
		    $Statement= "SELECT `id` FROM `permissions` WHERE `name` LIKE :name;";
		    $QueryData = array("name" => $GroupName);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
		    if(count($data[0]) > 0)return $data[0][0];
		    else return false;
		}
//hent alle avdelinger som faktisk er avdelinger (under 4 er systemgrpper, 13 er alle brukere)
		function hentAvdelinger(){
		    $Statement= "SELECT `name` FROM `permissions` WHERE `id` > 3 AND `id` NOT LIKE  13";
		    $QueryData = array();
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
		    for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n][0];
		    }
		    return $result;
		}
		
//SqlInjection sjekking og rensing. 		
		function inputCleanse($input){
			$querystrings = array("INSERT", "AND", "OR", "UPDATE", "DELETE");
			$allowedstrings = array("#INSERT#", "#AND#", "#OR#", "#UPDATE#", "#DELETE#");
			$input = str_replace($querystrings, $allowedstrings, $input);
			$output = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_LOW);
			return $output; 
		}

		//Hent alle registrerte turnusavvik (f.eks ferieturnuser) for et gitt år
		function hentAlleTurnusAvvik($taYear, $grp = ""){ //funksjonen tillater $grp, men brukes ikke enda siden det ikke ligger i basen.
		    $Statement = "SELECT * FROM `UpdateLog` WHERE `query` LIKE '%Turnusavvik%'AND `query` LIKE :Group AND `Dato` LIKE :Dato ORDER BY `ID` DESC";
		    $QueryData = array("Dato" => "%".$taYear."%","Group" => "%Group=".$grp."%");
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    return $data;
		}

		function hentTurnusAvvik($ID){ //funksjonen tillater $grp, men brukes ikke enda siden det ikke ligger i basen.
		    $Statement = "SELECT * FROM `UpdateLog` WHERE `ID` LIKE :ID;";
		    $QueryData = array("ID" => $ID);
		    $data = QueryMySQLPrepped($QueryData, $Statement,  $GLOBALS['Database']);
		    if(count($data[0]) > 0)return $data[0];
		    else return false;
		    
		}
		
		function getVarsledeByGroup($GID){ //henter for mange grupper! 
		    //$avd = hentAvdelingsnavn($GID);
		    $Statement = "SELECT `email` FROM `users`  WHERE `id` IN";
		    $Statement = $Statement . "(SELECT `user_id` FROM user_permission_matches A WHERE A.permission_id = 0 ";
		    $Statement = $Statement . "AND EXISTS (SELECT * FROM user_permission_matches WHERE permission_id = :permission_id AND user_id = A.user_id))";
		    $QueryData = array("permission_id" => $GID);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
		    for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n][0];
		    }
		    //return $data;
		    return $result;
		}
		function getVarsledeIDByGroup($GID){
		    //$avd = hentAvdelingsnavn($GID);
		    $Statement = "SELECT `id` FROM `users`  WHERE `id` IN";
		    $Statement = $Statement . "(SELECT `user_id` FROM user_permission_matches A WHERE A.permission_id = 0 ";
		    $Statement = $Statement . "AND EXISTS (SELECT * FROM user_permission_matches WHERE permission_id = :permission_id AND user_id = A.user_id))";
		    $QueryData = array("permission_id" => $GID);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['config']['mysql']['db']);
		    for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n][0];
		    }
		    //return $data;
		    return $result;
		}
		
		function chkUserVariables($UID){
		    $Statement = "SELECT * FROM `UserOptions` WHERE `UID` LIKE :UID;";
			$QueryData = array("UID" => $UID);
			$data = QueryMySQLPrepped($QueryData, $Statement,  $GLOBALS['Database']);
			if(is_array($data[0]) && count($data[0]) > 0)return $data[0];
			else return false;
		}
		
		function hentTildelinger($UID, $Dato, $GrID = "all"){
		    $Statement = "SELECT * FROM `TurnusFordeling`";
		    $Statement = $Statement . "WHERE `BrukerID` = :BrukerID AND `FraDato` <= :Dato AND (`TilDato` >= :Dato OR `TilDato` IS NULL)";
		    $QueryData = array("BrukerID" => $UID, "Dato" => $Dato);
		    
		    if ($GrID != "all"){
		        $Statement = $Statement . "AND `GroupID` = :GroupID";
		        $QueryData["GroupID"] = $GrID;
		    }
		    $data = QueryMySQLPrepped($QueryData, $Statement,$GLOBALS['Database']);
		    if(count($data[0]) > 0){
		        for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n];
		        }
		        return $result;
		    }
		    else return "NoTour";
		    
		    return $result;
		}
		

//Grunngrunnfunksjon for å hente data til oversiktsssiden.Fortsatt under debugging, siden det hender gårsdagens vakter ikke dukker opp. 		
		function getDayPlan($Date, $TurID, $GuID, $test="nei" ){ //Date in unix time
		    
		    $dateStamp = date('Y-m-d',$Date);
		    
		    //Hent liste over ansatte i turnus fordelt over offset:
		    $Statement = "SELECT `Offset`,`BrukerID` FROM `TurnusFordeling`";
		    $Statement = $Statement . "WHERE `FraDato` <= :Date AND (`TilDato` >= :Date OR `TilDato` IS NULL)";
		    $Statement = $Statement . "AND `GroupID` = '" . $GuID . "'";
		    $Statement = $Statement . "ORDER BY `TurnusFordeling`.`Offset` ASC ";
		    $QueryData = array("Date" => $dateStamp/*,"Date" => $dateStamp*/,"GroupID" => $GuID);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    
		    //Finn vakt pr Ansatt etter offset:
		    $turnus = hentTurnus($TurID);
		    $TurInfo = hentTurnusData($TurID);
		    //error_log(print_r($turnus,true),0);
		    
		    for ($n = 1; $n <= $TurInfo[3]; $n++){
		        $TurFordeling = $data[$n-1];
		        $vakt =  HentVakt($TurFordeling[1], $Date, $turnus, $TurInfo[1],$GuID); //HentVakt($UID, $unixtime, $turnus, $Tur_Startdat, gruppe
		        
		        if ($vakt[1]!= "" )$DayPlan[$vakt[1]] = array($TurFordeling[1], $vakt[0], $vakt[3], $vakt[4], $vakt[5], $vakt[6], $vakt[7]);
		    } //Denne returnerer alle vakter i turer som er besatt. Vi må også fange opp ubesatte turer..

		    //$Endringer = 0;
		    if ($Endringer = VaktendringerPaaDag($Date)){
		    
		    for($x = 0; $x < count($Endringer); $x++ ){
		        //error_log("Gruppe: " . $GuID . " endring: " . $Endringer[$x][1]);
		        if(ErDetRettGruppe($Endringer[$x][1],$GuID)) {
		            $vakt = sjekkVaktType($Endringer[$x][1]);
		            if ($vakt[1]!= "" )$DayPlan[$vakt[1]] = array($Endringer[$x][6], $vakt[0], $vakt[3], $vakt[4], $vakt[5], $vakt[6], $vakt[7]);
		        }
		    }
		    }
		    // error_log(print_r($DayPlan, true),0);
		    return $DayPlan;
		}
		
		//skal hente ut en dags turnus med originale eiere:
		function getDayPlanOnlyOriginalOwners($Date, $TurID, $GuID ){ //Date in unix time
		    
		    $dateStamp = date('Y-m-d',$Date);
		    
		    //Hent liste over ansatte i turnus fordelt over offset:
		    $Statement = "SELECT `Offset`,`BrukerID` FROM `TurnusFordeling`";
		    $Statement = $Statement . "WHERE `FraDato` <= :Date AND (`TilDato` >= :Date OR `TilDato` IS NULL)";
		    $Statement = $Statement . "AND `GroupID` = '" . $GuID . "'";
		    $Statement = $Statement . "ORDER BY `TurnusFordeling`.`Offset` ASC ";
		    $QueryData = array("Date" => $dateStamp/*,"Date" => $dateStamp*/,"GroupID" => $GuID);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    
		    //Finn vakt pr Ansatt etter offset:
		    $turnus = hentTurnus($TurID);
		    $TurInfo = hentTurnusData($TurID);
		    //if ($GuID == 8) error_log(print_r($turnus,true),0);
		    
		    for ($n = 1; $n <= $TurInfo[3]; $n++){
		        $TurFordeling = $data[$n-1];
		        $vakt =  HentVakt($TurFordeling[1], $Date, $turnus, $TurInfo[1],$GuID); //HentVakt($UID, $unixtime, $turnus, $Tur_Startdat, gruppe
		        
		        if ($vakt[1]!= "" )$DayPlan[$vakt[0]] = $TurFordeling[1];
		    } 
		    return $DayPlan;
		}
		
		function HentVakt($UID, $unixtime, $turnus, $Tur_Startdato, $GUID){
		    
		    $brukerinfo = turnusInfo2($UID,$unixtime, $GUID);
		    $Vakt = '';
		    
		    if (strtotime($brukerinfo[4]) <= $unixtime && ($unixtime <= strtotime($brukerinfo[5]. " +1 day") || $brukerinfo[5] == '')){
		        $offset = ($brukerinfo[3]*7) - 7; //(turnummer (ukenummer i grunnturnus) * 7 (for å få antall dager totalt i turnusen) - 7 (for starten av uka)
		        
		        if ($offset < 0) $offset = 0;
		        
		        else{
		            $TurLengde = count($turnus);
		            $Tur_Alder_iDager = DagerSiden($Tur_Startdato, $unixtime);
		            $offset_current = $offset + $Tur_Alder_iDager;
		            $Tur_Pos_oper = Turnus_Pos($TurLengde,$offset_current) ;//Unadjusted position in turnus
		            $Curr_Vakt = $turnus[$Tur_Pos_oper];
		            $Vakt = sjekkVaktType($Curr_Vakt);
		        }
		        
		    }
		    return $Vakt;
		}
		
		//hent alle vaktendringer i ett oppgitt d�gn
		function VaktendringerPaaDag($ts){
		    $dato = date('Y-m-d', $ts);
		    $Statement= "SELECT  * FROM `Endringer` WHERE  `Dato` = :Dato ORDER BY `RegDato` ASC";
		    $QueryData = array("Dato" => $dato);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n];
		    }
		    return $result;
		}
		function getAllActiveTours($nixtime){
		    $TourStartDate = date("Y-m-d", $nixtime);
		    $Statement = "SELECT * FROM `Turnus` WHERE `Startdato` <= :Date ORDER BY `permission_group`, `Startdato` ASC";
		    $QueryData = array("Date" => $TourStartDate);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);

		    //returdata: 0: ID, 1: Startdato, 2: GruppeID, 3 Lengde 
		    for ($n = 0; $n < count ($data); $n++){
		        $result[$data[$n][2]] = $data[$n][0];
		    }
		    
/*		    while ($myrow = mysqli_fetch_row($data)){
		        $result[$myrow[2]] = $myrow[0];
		        $n++;
		    }
*/		    return $result;
		    
		}

//Brukes til å hente vakter til å populere vaktoversikten
		function hentAlleVakttyper_sortert(){
		    $Statement = "SELECT * FROM `VaktType` WHERE `Varighet` NOT LIKE '0' ORDER BY `UsedBy`,`Varighet`,`Vakt` ASC";
		    //$Statement = "SELECT * FROM `VaktType` WHERE `Lokasjon` LIKE 'Ada' AND `Varighet` NOT LIKE '0' ORDER BY `UsedBy`,`Varighet`,`Vakt` ASC";
		    $QueryData = array();
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    return $data;
		}

		
		function getOffset($uid, $when){
		    $dstr = date('Y-m-d', $when);
		    $Statement = "SELECT `Offset` FROM `TurnusFordeling` WHERE `BrukerID` = :BrukerID AND `FraDato` < :Date AND (`TilDato` >= :Date OR `TilDato` IS NULL) ORDER BY `FraDato` DESC LIMIT 1";
		    $QueryData = array("BrukerID" => $uid, "Date" => $dstr, "Date" => $dstr);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    return $data[0];
		}
		
		function oversiktHentVakt($VaktNavn, $VaktInfo, $Tid, $Rolle,$TimeVar, $test = "nei"){
		    $n = 0;
		    $Vakten = array();
		    
		    if ($test == "Ja"){
    		    error_log("VaktNavn " . $VaktNavn);
    		    error_log("VaktInfo " . print_r($VaktInfo, true),0);
    		    error_log("Tid " . date("Y-m-d H:i:s" ,$Tid));
    		    error_log("Rolle " . $Rolle);
		    }

		    //some date management: 
//		    $dateTimeNow = new DateTime(Date("Y-m-d",time()));
//		    $dateTimeIncomming = new DateTime(Date("Y-m-d", $Tid));
		    if ( 
		        preg_match ("/" . $Rolle . "/", $VaktInfo[4]) 
		        && $VaktInfo[5] !=  0
		        &&
		        (
		            (
		              $VaktInfo[2] < $VaktInfo[3] //om starttid er f�r slutttid (vanlige vakter)
		              &&
		                $VaktInfo[2] <= date("H:i:s", $Tid) //om starttid er f�r slutttid (vanlige vakter)
	                  &&
		                date("H:i:s", $Tid) < $VaktInfo[3] //om starttid er f�r slutttid (vanlige vakter)
		            )
		            ||
		            (
		                $VaktInfo[2] > $VaktInfo[3] //om starttid er etter enn slutttid (Nattevakter)
		                &&
		                strtotime(date("Y-m-d", $Tid) . " 00:00:00") <= $Tid // og vi er etter midnatt
		                &&
		                date("H:i:s", $Tid) < $VaktInfo[3] //om valgt tid er f�r slutttid (vanlige vakter)
		                )
		          )
		        ){ 		            
		            $Vakten[0] = $VaktNavn; //Vakt

//		            if ($test == "Ja") error_log("VaktInfo1 " . print_r($Vakten, true),0);
		            
		            //Sette farge p� vakttabellen :)
		            if (preg_match ( "/Dag/" ,  $VaktInfo[4]  ))  $Vakten[1] = "Dag";
		            else if (preg_match ( "/Kveld/" ,  $VaktInfo[4]  ))  $Vakten[1] = "Kveld";
		            else if (preg_match ( "/Natt/" ,  $VaktInfo[4]  ))  $Vakten[1] = "Natt";
		            else if (preg_match ( "/Tilkallingsvakt/" ,  $VaktInfo[4]  ))  $Vakten[1] = "Gray";
		            //else if (preg_match ( "/Ikke på vakt /" ,  $VaktInfo[4]  ))  $Vakten[1] = "Today";
		            else $Vakten[1] = "White";
		            
		            $Vakten[2] = $VaktInfo[0]; //AnsattID
		            $Vakten[3] = $VaktInfo[1]; //Vaktid
		            $Vakten[4] = $VaktInfo[5]; //VaktLengde
 		            $Vakten[5] = date("H:i:s", strtotime($VaktInfo[2])); //VaktStart
		            $Vakten[6] = date("H:i:s", $Tid) ; //Timestamp to locate shift we want
		            $Vakten[7] = date("H:i:s", strtotime($VaktInfo[3])); //VaktSlutt
		            if  (date("Y-m-d", $Tid) == date("Y-m-d",$TimeVar))$Vakten["CurrentShiftDay"] = "Today";
		            
		            //Er neste vakt ig�r, idag eller imorgen?
		            $ShitftLengthMod  = $VaktInfo[5];
		            if (date("d", strtotime($Vakten[7] ."-" . $ShitftLengthMod . " hours"))!= date("d", $TimeVar))$Vakten["PrevShiftDay"] = "Yesterday";
		            else $Vakten["PrevShiftDay"] = "Today";
		            
		            if (date("d", strtotime( $Vakten[7] . "+" . $ShitftLengthMod . " hours"))!= date("d", $TimeVar))$Vakten["NextShiftDay"] = "Tomorrow";
		            else $Vakten["NextShiftDay"] = "Today";
		    }

		    if (
		        $VaktInfo[2] > $VaktInfo[3] //om starttid er etter enn slutt-tid (Nattevakter)
		        &&
		        strtotime(date("Y-m-d ", $Tid) . "00:00:00") >= $Tid // og vi er f�r  midnatt
		        )$Vakten["NextShiftDay"] = "Tomorrow";
		  // if ($test == "Ja") error_log("VaktInfo1 " . print_r($Vakten, true),0);
		    return $Vakten;
		    
		}
		
		function SjekkTurnusAktiv($UID, $GUID){
		    $Statement = "SELECT * FROM `User_inTour` WHERE `UID` = :UID AND `GUID` = :GUID AND `inTour_bool` = 1;";
		    $QueryData = array(
		        "UID" => $UID, 
		        "GUID" => $GUID
		    );
		    $dbID = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    return $dbID[0];
		}
		
		function SjekkAktivLeder($UID, $GUID){
		    $Statement = "SELECT * FROM `ledere` WHERE `UID` = :UID AND `GrID` = :GUID AND `StartDate`  < :now1 AND `EndDate` is NULL;";
		    $QueryData = array(
		        "UID" => $UID,
		        "GUID" => $GUID,
		        "now1" => date("Y-m-d", time())
		    );
		    $dbID = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    return $dbID[0];
		}
		
//Henter ut personell som er aktivert for å ha turnus i en gitt gruppe, returnert med navn
		function HentTurnusAktive($GUID){
		    //Get users inTour:
		    $Statement  = "SELECT * FROM `User_inTour` WHERE `GUID` = :GUID AND `inTour_bool` = 1";
		    $QueryData = array("GUID" => $GUID);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    for ($n = 0; $n < count ($data); $n++){
		        $result[$n] = $data[$n][1];
		    }
		    
		    //get users name, put both users names and userID in array:
		    if (is_array($result)){
		        for ($o =0; $o < count($result); $o++){
		        $tempArray = finnAnsattNavn($result[$o]);
		        $result[$o] = array($result[$o],$tempArray[0],$tempArray[1] );
		        }
		        
		    }
		    else $result = false;
		    return $result;
		}
//samme funkson som over, men returnerer kun ansattID		
		function CheckedUsers($GUID){
		    //$Statement  = "SELECT * FROM `User_inTour` WHERE `GUID` = " . $GUID . " AND `inTour_bool` = 1";
		    $Statement  = "SELECT * FROM `User_inTour` WHERE `GUID` = :GUID AND `inTour_bool` = 1";
		    $QueryData = array("GUID" => $GUID);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n][1];
		    }
		    return $result;
		}
		
		function CheckedLeaders($GUID){//returnerer kun ansattID
		    //$Statement  = "SELECT * FROM `User_inTour` WHERE `GUID` = " . $GUID . " AND `inTour_bool` = 1";
		    $Statement  = "SELECT * FROM `ledere` WHERE `GrID` = :GUID AND `StartDate`  <= :now1 AND (`EndDate` is NULL OR `EndDate` > :now2)";
		    $QueryData = array(
		        "GUID" => $GUID,
		        "now1" => date("Y-m-d", time()),
		        "now2" => date("Y-m-d", time())
		    );
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    for ($n = 0; $n < count($data); $n++){
		        $result[$n] = $data[$n][1];
		    }
		    return $result;
		}
		
		

		//Brukes på oversikten for å sjekke om en vakttype er i bruk eller ikke
		function VaktAktiv($VID, $GID, $TimeVar){
		    $Statement = "SELECT * FROM `TurnusTur` WHERE `TurnusTur`.`VaktID`=:VaktID AND `TurnusID` = (SELECT ID FROM `Turnus` WHERE `Startdato` <= :Date AND `permission_group`= :permission_group ORDER BY ID DESC LIMIT 1)";
		    $QueryData = array("VaktID" => $VID,"Date" => date("Y-m-d", $TimeVar),"permission_group" => $GID);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    return $data[0];
		}
		
		//brukes av turnusADm for å sjekke om en tur er ledig eller ikke
		function isTurFree($TurNR, $GroupID){
		    $Statement = "SELECT `TilDato` FROM `TurnusFordeling` WHERE `GroupID` =:GroupID AND `Offset` =:Offset ORDER BY `TurnusFordeling`.`FraDato` DESC LIMIT 1 ";
		    $QueryData = array("GroupID" => $GroupID,"Offset" => $TurNR);
		    $data = QueryMySQLPrepped($QueryData, $Statement, $GLOBALS['Database']);
		    //error_log("Blippa: " . $Statement);
		    //error_log("Blippa: " . print_r($QueryData, true));
		    if (is_array($data))return $data[0];
		    else return false;
		    

		}
		
		function isToday($nixTime){
		    if (date('d-m', $nixTime) == date('d-m', time())) return true;
		    else return false;
		}
		
		?>
