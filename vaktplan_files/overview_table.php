<?php
if ($uid != ""){ //block page usage WO login
    
    // Takler ikke at det legges inn en nyere turnus.
    // Må også passe på at det takler endring i DB i "Turnusfordeling" til å lete på gruppeID istedet for på turnusID
    // Den sjekker heller ikke ferie og fravær. 
    // Den må også sikre at det kan hende turnus endrer seg mellom to dager. 
    
    $Yesterday = strtotime("-1 day");
    $Today =$TimeVar;
    $Tomorrow = strtotime("+1 day");
    
    
// Problem: GetAllActiveTours henter ut alle turer, ikke bare aktive, med gyldighet fra før en gitt dato.. 
// Men ved litt sortering og kreativ overskrivning koser vi oss med å hente ut bare den siste registrerte for hver gruppe på en gitt dato
    $activeToursYesterday = getAllActiveTours($Yesterday);
    $activeToursToday = getAllActiveTours($Today);
    $activeToursTomorrow = getAllActiveTours($Tomorrow);
    //gir aktivtur[gruppe] ([tur1], [tur2], ...,[turN])
    
    //error_log(print_r($activeToursToday, true),0);

//Lets walk througt the valley of tears and get all of the ones crying.. Eller la oss finne vaktlistene for de forskjellige gruppene :D
// Må takle å sjekke om turnusen i går eller i morgen er den samme som den idag
    $Plan = array();
    foreach($activeToursToday as $key => &$value){ 
        
        //error_log($key);
        //error_log($value);
        if ($activeToursYesterday[$key] == $value)$Plan["Yesterday"][$key] = getDayPlan($Yesterday, $activeToursYesterday[$key], $key);
        if ($activeToursYesterday[$key] != $value)$Plan["Yesterday"][$key] = getDayPlan($Yesterday, $value, $key);
        $Plan["Today"][$key] = getDayPlan($Today, $value, $key, "ja");
        if ($activeToursTomorrow[$key] == $value)$Plan["Tomorrow"][$key] = getDayPlan($Tomorrow, $activeToursTomorrow[$key], $key);
        if ($activeToursTomorrow[$key] != $value)$Plan["Tomorrow"][$key] = getDayPlan($Tomorrow, $value, $key);
    }
 
   error_log(print_r($Plan["Today"], true), 0);
    
//Some time management
    $DayNow = date("D", $TimeVar);
    $HourNow = date("H", $TimeVar) +1;

$Weekday = array("Mon", "Tue", "Wed", "Thu", "Fri"); 
$Weekend = array("Sat", "Sun");

$AlleVakttyper_sortert =  hentAlleVakttyper_sortert(); //we want an array of all shift types registered, that has hours, and is at OPM
//array description: (0 ID, 1 Vakt, 2 Lokasjon, 3 Start, 4 Slutt, 5 Beskrivelse, 6 varighet, 7 usedby, 8 erhelg, 9 dag)

// LEgg inn tabellheader. Vi bruker en div/css-basert tabell. 
?>
<div class="centerWrapper">

<div class="defTable White">
	<div class="defRow">
		<div class="spacer defCell"> </div>
		<div class="TableContent TableTitle defTitle Yellow">Vaktoversikt: </div> 
	</div>
	<div class="defRow">
		<div class=" spacer defCell"> </div>
		<div class="invisiCell defCell White"></div> 
	<?php if ($urlmod != "mobile_"){?>
		<div class="spacer defCell"> </div>
		<div class="TableContent defName defCell Yellow">Forrige Skift</div> 
		<div class="spacer defCell"> </div>
		<div class="TableContent defName defCell Yellow">P&aring; vakt n&aring;</div> 
		<div class="spacer defCell"> </div>
		<div class="TableContent defName defCell Yellow">Neste skift</div> 
	<?php } ?>
	<?php if ($urlmod == "mobile_"){?>
		<div class="spacer defCell"> </div>
		<div class="TableContent defName defCell Yellow">N&aring;</div> 
		<div class="spacer defCell"> </div>
		<div class="TableContent defName defCell Yellow">Neste</div> 
	<?php } ?>
	</div>
	
<?php 

//Start å hente ut å generere tabellrader: 
// en rad pr dagskift pr gruppe. Vi antar at hver type dagskift definerer alle potensielle roller, hvor noen er udekket deler av døgnet. 
$n = 0;


while ($n < count($AlleVakttyper_sortert)){
    error_log("overv.82: " . print_r($AlleVakttyper_sortert[$n],true),0);
    if (($AlleVakttyper_sortert[$n][6] == 8 || $AlleVakttyper_sortert[$n][6] == 7 
    || preg_match ( "/Tilkallingsvakt/" ,  $AlleVakttyper_sortert[$n][5]))
    && VaktAktiv($AlleVakttyper_sortert[$n][0], $AlleVakttyper_sortert[$n][7], $TimeVar)){ 
            ;
   // error_log($AlleVakttyper_sortert[$n][5],0);
    //$AlleVakttyper_sortert inneholder en tabell med alle vakttypene i løpet av et døgn med bakvakter filtrert ut, og sortert på vaktlengde
    //Løkka går til vi treffer 12timersvaktene, siden rollene primært defineres av 8timersvaktene. 
    //Når en tabellinje er definert av et dagskift (ex NO1) kan det aktive skiftet (natt- dag- kveld- helg dag - helg natt) hentes ut fra samme tabell
    
    //Vi ønsker å liste aktive roller, ikke aktive skift. Rollene defineres av dagskiftet. 
    $noon = new DateTime('12:00'); // Midt på dagen. Vi ønsker tross allt dagskiftet, så la oss hente dem med å plassere oss tidsmessig midt i.. 
    $now = $Today;
    $begin = new DateTime($AlleVakttyper_sortert[$n][3]); //Vakt start
    $end = new DateTime($AlleVakttyper_sortert[$n][4]); //Vakt end
    $Gruppe = $AlleVakttyper_sortert[$n][7];
    //error_log(print_r($Gruppe,true),0);
    
    if ($noon >= $begin && $noon <= $end && $AlleVakttyper_sortert[$n][7] != 0){// kun dagvakter, ikke kvelds- eller nattevakter!
        if (preg_match('/[0-9]/', $AlleVakttyper_sortert[$n][1])){
            $Rolle = hentAvdelingsnavn($AlleVakttyper_sortert[$n][7]). "-" .$AlleVakttyper_sortert[$n][1]; //slik vi ønsker å skrive headeren
            $TempRole = str_replace("-", "", $Rolle); //slik den er notert i databasen
        }
        if (!preg_match('/[0-9]/', $AlleVakttyper_sortert[$n][1])){
            $Rolle = hentAvdelingsnavn($AlleVakttyper_sortert[$n][7]); //slik vi ønsker å skrive headeren
            $TempRole = $Rolle; //slik den er notert i databasen
        }
        
        //Hvilke vakter er aktive akkurat nå? 
        error_log (print_r($Plan["Today"][8],true),0);
         foreach ($Plan["Today"][$Gruppe] as $key => &$value){ 
            $VaktHolder = oversiktHentVakt($key, $value, $now, $TempRole, $TimeVar, $test = "nei");
            $infoTemp = sjekkVaktType($VaktHolder[3]);
            if (is_array($VaktHolder) && $infoTemp[2] == "Ada" ){
                /* Vi ønsker for tiden ikke bakvaktene. */
                if($Vakten['1B']== '' && preg_match ( "/Tilkallingsvakt/i" ,  $infoTemp[5])) $Vakten['1B'] = $VaktHolder;
                if($Vakten[1]== '' && !preg_match ( "/Tilkallingsvakt/i" ,  $infoTemp[5])) $Vakten[1] = $VaktHolder;
                error_log(print_r($VaktHolder,true),0);
                unset($VaktHolder);
            }
         }
         //error_log(print_r($Vakten,true),0);
         
        if ( $Vakten[1]["CurrentShiftDay"] == "Tomorrow"){
            foreach ($Plan["Tomorrow"][$Gruppe] as $key => &$value){
                $VaktHolder = oversiktHentVakt($key, $value, $Tomorrow, $TempRole, $TimeVar, $test = "nei");
                $infoTemp = sjekkVaktType($VaktHolder[3]);
                if (is_array($VaktHolder) && $infoTemp[2] == "Ada" ){
                    /* Vi ønsker for tiden ikke bakvaktene. */
                    if($Vakten['1B']== '' && preg_match ( "/Tilkallingsvakt/i" ,  $infoTemp[5])) $Vakten['1B'] = $VaktHolder;
                    if($Vakten[1]== '' && !preg_match ( "/Tilkallingsvakt/i" ,  $infoTemp[5])) $Vakten[1] = $VaktHolder;
                    unset($VaktHolder);
                    //error_log(print_r($infoTemp,true),0);
                }
            }
        }
        
//Calculate times in previous and next shift:
        //Forrige skift: 
        foreach ($Plan[$Vakten[1]["PrevShiftDay"]][$Gruppe] as $key => &$value){
 
            if ($Vakten[1][4] && $Vakten[1][4] != "" && $Vakten[1][4] != 0 && $Vakten[1][4] < 8) $ShitftLengthMod  = $Vakten[1][4] + 2;
            if ($Vakten[1][4] && $Vakten[1][4] != "" && $Vakten[1][4] != 0 && $Vakten[1][4]  >= 8 ) $ShitftLengthMod  = $Vakten[1][4]  + 5;
//            if ($Vakten[1][4] && $Vakten[1][4] != "" && $Vakten[1][4] != 0 && $Vakten[1][4]  >= 13 ) $ShitftLengthMod  = $Vakten[1][4];
            
            $prevStart = strtotime($Vakten[1][7] . " -" . $ShitftLengthMod . " hours");
            if ($Vakten[1][4] && $Vakten[1][4] != "" && $Vakten[1][4] != 0 && $Vakten[1][4]  >= 13 ) $prevStart = strtotime($Vakten[1][7] . " -" . $ShitftLengthMod . " minutes");
            
            //if ($Vakten[1][3] == 43) error_log ("overview 150: " . date('Y-m-d',$prevStart));
           
            $VaktHolder2 = oversiktHentVakt($key, $value, $prevStart, $TempRole, $TimeVar,  $test = "nei"); //"nei"/"Ja"
            
            //if ($Vakten[1][3] == 43) error_log("overview 154: " .print_r($VaktHolder2,true),0);

            $infoTemp2 = sjekkVaktType($VaktHolder2[3]);
            if (is_array($VaktHolder2) && $infoTemp2[2] == "Ada" ){
                /* Vi ønsker for tiden ikke andre bakvakter enn driftvaktenes. */
                if($Vakten['0B']== '' && preg_match ( "/Tilkallingsvakt/i" ,  $infoTemp2[5])) $Vakten['0B'] = $VaktHolder2;
                if($Vakten[0]== '' && !preg_match ( "/Tilkallingsvakt/i" ,  $infoTemp2[5])) $Vakten[0] = $VaktHolder2;
            }
        }
        
        //Neste Skift: 
        foreach ($Plan[$Vakten[1]["NextShiftDay"]][$Gruppe] as $key => &$value){
            if ($Vakten[1][4] && $Vakten[1][4] != "" && $Vakten[1][4] != 0 && $Vakten[1][4] < 8) $ShitftLengthMod  = $Vakten[1][4]+1;
            if ($Vakten[1][4] && $Vakten[1][4] != "" && $Vakten[1][4] != 0 && $Vakten[1][4]  >= 8) $ShitftLengthMod  = $Vakten[1][4]+5;
            $nextStart = strtotime($Vakten[1][5] . "+" . $ShitftLengthMod . " hours");
            $VaktHolder3 = oversiktHentVakt($key, $value, $nextStart, $TempRole, $TimeVar, $test = "nei");
             
           // error_log(print_r($VaktHolder3, true),0);
            $infoTemp = sjekkVaktType($VaktHolder3[3]);
            
            if (is_array($VaktHolder3) && $infoTemp[2] == "Ada" ){
            /* Vi ønsker for tiden ikke bakvaktene. */
                if($Vakten['2B']== '' && preg_match ( "/Tilkallingsvakt/i" ,  $infoTemp[5])) $Vakten['2B'] = $VaktHolder3;
                if($Vakten[2]== '' && !preg_match ( "/Tilkallingsvakt/i" ,  $infoTemp[5])) $Vakten[2] = $VaktHolder3;
                // error_log(print_r($infoTemp,true),0);
            }
        }

//Vakter: 
        //Forrige skift: 
        $AnsattID[0] = $Vakten[0][2];
        
        //Dette skiftet: 
        $AnsattID[1] = $Vakten[1][2];
        
        //Neste Skift:
        $AnsattID[2] = $Vakten[2][2];
        
//        error_log("otabl.192: " . print_r($AnsattID, true), 0);
        
               
        // Sjekk $VaktBytte : 
        $NyEier["Forrige"] = sjekkVaktFlytt($Vakten[0][3], $prevStart);
        //error_log(date("Y-m-d H:i:s", $prevStart));
        if (is_array ($NyEier["Forrige"])){
            $NyEier["Forrige"][2] = utf8_encode($NyEier["Forrige"][2]);
            $NyEier["Forrige"][2] = str_replace("Flytte", "(X) ",$NyEier["Forrige"][2]);
            $NyEier["Forrige"][2] = str_replace("Bytte", "(B) ",$NyEier["Forrige"][2]); 
            $NyEier["Forrige"][2] = str_replace("F.Avvikl.", "(X) ",$NyEier["Forrige"][2]);
            $AnsattID[0] = $NyEier["Forrige"][6];
        }

        $NyEier["Denne"] = sjekkVaktFlytt($Vakten[1][3], $TimeVar);
        if (is_array ($NyEier["Denne"])){
            $NyEier["Denne"][2] = utf8_encode($NyEier["Denne"][2]);
            $NyEier["Denne"][2] = str_replace("Flytte", "(X) ",$NyEier["Denne"][2]);
            $NyEier["Denne"][2] = str_replace("Bytte", "(B) ",$NyEier["Denne"][2]); 
            $NyEier["Denne"][2] = str_replace("F.Avvikl.", "(X) ",$NyEier["Denne"][2]);
            $AnsattID[1] = $NyEier["Denne"][6];
        }
        $NyEier["Neste"] = sjekkVaktFlytt($Vakten[2][3], $nextStart);
        if (is_array ($NyEier["Neste"])){
            $NyEier["Neste"][2] = utf8_encode($NyEier["Neste"][2]);
            $NyEier["Neste"][2] = str_replace("Flytte", "(X) ",$NyEier["Neste"][2]); 
            $NyEier["Neste"][2] = str_replace("Bytte", "(B) ",$NyEier["Neste"][2]);
            $NyEier["Neste"][2] = str_replace("F.Avvikl.", "(X) ",$NyEier["Neste"][2]);
            $AnsattID[2] = $NyEier["Neste"][6];
        }
        
        //error_log("otabl.224: " . print_r($AnsattID, true), 0);
        
        
        if ($AnsattID[2] == 20 || $AnsattID[2] == 20 || $AnsattID[2] == 20) $PrintNextTest = 1;
        if ($AnsattID[1] == 20){
            $Temp[1] = SjekkAltVakt($now, $Gruppe, $TempRole);
            $AnsattID[1] = $Temp[1][0];
        }
        if ($AnsattID[0] == 20){ 
            $Temp[0] = SjekkAltVakt($prevStart, $Gruppe, $TempRole);
            $AnsattID[0] = $Temp[0][0];
        }
        if ($AnsattID[2] == 20){ 
            $Temp[2] = SjekkAltVakt($nextStart, $Gruppe, $TempRole);
            $AnsattID[2] = $Temp[2][0];
        }

        //error_log("otabl.240: " . print_r($AnsattID, true), 0);
        
        
        //aktive bakvakter?
        if ($Vakten['0B'] != ''){
           $AnsattID['0B'] = $Vakten['0B'][2];
            // Sjekk $VaktBytte :
            $NyEier["Forrige_bak"] = sjekkVaktFlytt($Vakten['0B'][3], $prevStart); //feil skjer her ved flytta bakvakter. 
            
            if (is_array ($NyEier["Forrige_bak"])){
                $NyEier["Forrige_bak"][2] = utf8_encode($NyEier["Forrige_bak"][2]);
                $NyEier["Forrige_bak"][2] = str_replace("Flytte", "(X) ",$NyEier["Forrige_bak"][2]);
                $NyEier["Forrige_bak"][2] = str_replace("Bytte", "(B) ",$NyEier["Forrige_bak"][2]);
                $NyEier["Forrige_bak"][2] = str_replace("F.Avvikl.", "(X) ",$NyEier["Forrige_bak"][2]);
                $AnsattID['0B'] = $NyEier["Forrige_bak"][6];
            }
        }
        if ($Vakten['1B'] != ''){
            $AnsattID['1B'] = $Vakten['1B'][2];
            // Sjekk $VaktBytte :
            $NyEier["Denne_bak"] = sjekkVaktFlytt($Vakten['1B'][3], $TimeVar);
            if (is_array ($NyEier["Denne_bak"])){
                $NyEier["Denne_bak"][2] = utf8_encode($NyEier["Denne_bak"][2]);
                $NyEier["Denne_bak"][2] = str_replace("Flytte", "(X) ",$NyEier["Denne_bak"][2]);
                $NyEier["Denne_bak"][2] = str_replace("Bytte", "(B) ",$NyEier["Denne_bak"][2]);
                $NyEier["Denne_bak"][2] = str_replace("F.Avvikl.", "(X) ",$NyEier["Denne_bak"][2]);
                $AnsattID['1B'] = $NyEier["Denne_bak"][6];
            }
        }
        if ($Vakten['2B'] != ''){
            $AnsattID['2B'] = $Vakten['2B'][2];
            // Sjekk $VaktBytte :
            $NyEier["Neste_bak"] = sjekkVaktFlytt($Vakten['2B'][3], $nextStart);
            if (is_array ($NyEier["Neste_bak"])){
                $NyEier["Neste_bak"][2] = utf8_encode($NyEier["Neste_bak"][2]);
                $NyEier["Neste_bak"][2] = str_replace("Flytte", "(X) ",$NyEier["Neste_bak"][2]);
                $NyEier["Neste_bak"][2] = str_replace("Bytte", "(B) ",$NyEier["Neste_bak"][2]);
                $NyEier["Neste_bak"][2] = str_replace("F.Avvikl.", "(X) ",$NyEier["Neste_bak"][2]);
                $AnsattID['2B'] = $NyEier["Neste_bak"][6];
            }
        }
        
        //error_log("otabl.282: " . print_r($AnsattID, true), 0);
        
//error_log("Overv.264: Neste Vakt: " . print_r($Vakten[2], true), 0);
        
        //Hent navn.      
        // $NyEier <- legg til prefiks for bytte (b) eller ekstra (x)
        
        //Hovedskift:
        if (finnAnsattNavn($AnsattID[0])[0] != 'Udekte'){  
            $Navn = finnAnsattNavn($AnsattID['0']);
            if (isset($Navn[0]))  $forrigeSkift = $NyEier["Forrige"][2].$Navn[0]." ".  $Navn[1].".";
            //if (isset($Navn[0]))  $forrigeSkift = $NyEier["Forrige"][2].$Navn[0]." ".  mb_substr($Navn[1],0,2).".";
            else if (!isset($Navn[0]) && (Date("D", $prevStart) == "Sat" || (Date("D", $prevStart) == "Sun")))  $forrigeSkift = " ";
            else {$forrigeSkift = "Udekket";$Vakten[0][1] = "Red";}
        }
        if (finnAnsattNavn($AnsattID[1])[0] != 'Udekte'){  
            $Navn = finnAnsattNavn($AnsattID['1']);
          //  error_log("otabl 299: " .  $Navn[1]);
            if (isset($Navn[0]))$detteSkiftet = $NyEier["Denne"][2].$Navn[0]." ".  $Navn[1].".";
            //if (isset($Navn[0]))$detteSkiftet = $NyEier["Denne"][2].$Navn[0]." ".  mb_substr($Navn[1],0,2).".";
            else  if (!isset($Navn[0]) && (Date("D", $TimeVar) == "Sat" || (Date("D", $TimeVar) == "Sun")))  $detteSkiftet = " ";
            else {$detteSkiftet = "Udekket";$Vakten[1][1] = "Red";}
        }
        if (finnAnsattNavn($AnsattID[2])[0] != 'Udekte'){ 
            $Navn = finnAnsattNavn($AnsattID['2']);
            if (isset($Navn[0]))$nesteSkift = $NyEier["Neste"][2].$Navn[0]." ".  $Navn[1].".";
            //if (isset($Navn[0]))$nesteSkift = $NyEier["Neste"][2].$Navn[0]." ".  mb_substr($Navn[1],0,2).".";
            else  if (!isset($Navn[0]) && (Date("D", $nextStart) == "Sat" || (Date("D", $nextStart) == "Sun")))  $nesteSkift = " ";
            else {$nesteSkift = "Udekket";$Vakten[2][1] = "Red";}
        }
        
        //Bakvakter:
        if ($AnsattID['0B'] != '' && finnAnsattNavn($AnsattID['0B'])[0] != 'Udekte'){ 
            $Navn = finnAnsattNavn($AnsattID['0B']);
            if (isset($Navn[0]))$forrigeSkift_bak = $NyEier["Forrige_bak"][2].$Navn[0]." ". $Navn[1].".";
            //if (isset($Navn[0]))$forrigeSkift_bak = $NyEier["Forrige_bak"][2].$Navn[0]." ". mb_substr($Navn[1],0,2).".";
            //           if (!isset($Navn[0]) && (Date("D", $prevStart) == "Sat" || (Date("D", $prevStart) == "Sun")))  $forrigeSkift_bak = " ";
            else {$forrigeSkift_bak = "Udekket";$Vakten['0B'][1] = "Red";}
        }
        if ($AnsattID['1B'] != '' && finnAnsattNavn($AnsattID['1B'])[0] != 'Udekte'){ 
            $Navn = finnAnsattNavn($AnsattID['1B']);
            if (isset($Navn[0]))$detteSkiftet_bak = $NyEier["Denne_bak"][2].$Navn[0]." ". $Navn[1].".";
            //if (isset($Navn[0]))$detteSkiftet_bak = $NyEier["Denne_bak"][2].$Navn[0]." ". mb_substr($Navn[1],0,2).".";
            //            if (!isset($Navn[0]) && (Date("D", $TimeVar) == "Sat" || (Date("D", $TimeVar) == "Sun")))  $detteSkiftet_bak = " ";
            else {$detteSkiftet_bak = "Udekket";$Vakten['1B'][1] = "Red";}
        }
        if ($AnsattID['2B'] != '' && finnAnsattNavn($AnsattID['2B'])[0] != 'Udekte'){ 
            $Navn = finnAnsattNavn($AnsattID['2B']);
            if (isset($Navn[0]))$nesteSkift_bak = $NyEier["Neste_bak"][2].$Navn[0]." ". $Navn[1].".";
            //if (isset($Navn[0]))$nesteSkift_bak = $NyEier["Neste_bak"][2].$Navn[0]." ". mb_substr($Navn[1],0,2).".";
            //           if (!isset($Navn[0]) && (Date("D", $nextStart) == "Sat" || (Date("D", $nextStart) == "Sun")))  $nesteSkift_bak = " ";
            else {$nesteSkift_bak = "Udekket";$Vakten['2B'][1] = "Red";}
        }

/*
 * mb_substr($Navn[1],0,1)
        error_log("otabl 318: " . $forrigeSkift);
        error_log("otabl 319: " . $detteSkiftet);
        error_log("otabl 320: " . $nesteSkift);
        error_log("otabl 321: " . $forrigeSkift_bak);
        error_log("otabl 322: " . $detteSkiftet_bak);
        error_log("otabl 323: " . $nesteSkift_bak);
*/        
        //Alternate coloring:
        //Yesterday:
        if ($Vakten[0][1] == "" || $Vakten[0][1] == "Today") $Vakten[0][1] = "NormalDay";
        
        //Tomorrow:
        if ($Vakten[2][1] == ""|| $Vakten[2][1] == "Today") $Vakten[2][1] = "sx";
        
        //chekck utf8
        /*
        if (!mb_check_encoding($forrigeSkift, 'UTF-8'))$forrigeSkift = utf8_encode($forrigeSkift);
        if (!mb_check_encoding($detteSkiftet, 'UTF-8'))$detteSkiftet = utf8_encode($detteSkiftet);
        if (!mb_check_encoding($nesteSkift, 'UTF-8'))$nesteSkift = utf8_encode($nesteSkift);
        if (!mb_check_encoding($forrigeSkift_bak, 'UTF-8'))$forrigeSkift_bak = utf8_encode($forrigeSkift_bak);
        if (!mb_check_encoding($detteSkiftet_bak, 'UTF-8'))$detteSkiftet_bak = utf8_encode($detteSkiftet_bak);
        if (!mb_check_encoding($nesteSkift_bak, 'UTF-8'))$nesteSkift_bak = utf8_encode($nesteSkift_bak);
        */
        
        //sjekk om ansatt er i ferie, dersom ansattnavnet fortsatt er merket som i ferie, vil vakten være udekket. 
        if (sjekkFraver($AnsattID[0], $prevStart, false)){$forrigeSkift = "Udekket";$Vakten[0][1] = "Red";}
        if (sjekkFraver($AnsattID[1], $TimeVar, false)){$detteSkiftet = "Udekket";$Vakten[1][1] = "Red";}
        if (sjekkFraver($AnsattID[2], $nextStart, false)){ $nesteSkift = "Udekket";$Vakten[2][1] = "Red";}
        if (sjekkFraver($AnsattID['0B'], $prevStart, false)){$forrigeSkift_bak = "Udekket";$Vakten['0B'][1] = "Red";}
        if (sjekkFraver($AnsattID['1B'], $TimeVar, false)){$detteSkiftet_bak = "Udekket";$Vakten['1B'][1] = "Red";}
        if (sjekkFraver($AnsattID['2B'], $nextStart, false)){ $nesteSkift_bak = "Udekket";$Vakten['2B'][1] = "Red";}
/*        
        error_log("otabl 318: " . $forrigeSkift);
        error_log("otabl 319: " . $detteSkiftet);
        error_log("otabl 320: " . $nesteSkift);
        error_log("otabl 321: " . $forrigeSkift_bak);
        error_log("otabl 322: " . $detteSkiftet_bak);
        error_log("otabl 323: " . $nesteSkift_bak);
 */       
        if (SjekkAlias($AlleVakttyper_sortert[$n][7])) $Rolle = SjekkAlias($AlleVakttyper_sortert[$n][7])[0];
        
       // error_log ("Otabl 347: test alias: " . print_R(SjekkAlias($AlleVakttyper_sortert[$n][7]), true),0);
        
        //Create row: 
?>
	<div class="defRow">
		<div class="spacer defCell"> </div>
		<div class="TableContent defName defCell Yellow "><?php echo $Rolle;?></div> 
		<?php if ($urlmod != "mobile_"){?>
		<div class="spacer defCell"> </div>
		<?php if ($Vakten['0B'] == ''){?>
		<div class="TableContent defName defCell <?php echo $Vakten[0][1];?>"><?php echo $forrigeSkift;//forrige skift ?></div> 
		<?php } ?>
		<?php if ($Vakten['0B'] != ''){?>
		<div class="TableContent defName_half defCell border_NoRight <?php echo $Vakten[0][1];?>"><?php echo $forrigeSkift;//forrige skift ?></div> 
		<div class="TableContent defName_divider_y<?php if ($Vakten[0][1] == "Red") echo $Vakten['0B'][1];?> defCell border_divider"></div>
		<div class="TableContent defName_half defCell border_NoLeft <?php echo $Vakten['0B'][1];?>"><?php echo " " .$forrigeSkift_bak;//forrige skift ?></div>
		<?php } ?>
		<?php } ?>
		<div class="spacer defCell"> </div>
		<?php if ($Vakten['1B'] == '' || $urlmod == "mobile_"){?>
		<div class="TableContent defName defCell <?php echo $Vakten[1][1];?>"><?php echo $detteSkiftet//aktivt skift ?></div> 
		<?php } ?>
		<?php if ($Vakten['1B'] != '' && $urlmod != "mobile_"){?>
		<div class="TableContent defName_half defCell border_NoRight <?php echo $Vakten[1][1];?>"><?php echo $detteSkiftet;//forrige skift ?></div> 
		<div class="TableContent defName_divider<?php if ($Vakten[1][1] == "Red")echo $Vakten['1B'][1];?> defCell border_divider"></div>
		<div class="TableContent defName_half defCell border_NoLeft <?php echo $Vakten['1B'][1];?>"><?php echo " " .$detteSkiftet_bak;//forrige skift ?></div>
		<?php } ?>
		<div class="spacer defCell"> </div>
		<?php if ($Vakten['2B'] == '' || $urlmod == "mobile_"){?>
		<div class="TableContent defName defCell <?php echo $Vakten[2][1];?>"><?php echo $nesteSkift//neste skift ?></div> 
		<?php } ?>
		<?php if ($Vakten['2B'] != '' && $urlmod != "mobile_"){?>
		<div class="TableContent defName_half defCell border_NoRight <?php echo $Vakten[2][1];?>"><?php echo $nesteSkift;//forrige skift ?></div> 
		<div class="TableContent defName_divider_t<?php if ($Vakten[2][1] == "Red")echo $Vakten['2B'][1];?> defCell border_divider"></div>
		<div class="TableContent defName_half defCell border_NoLeft <?php echo $Vakten['2B'][1];?>"><?php echo " " .$nesteSkift_bak;//forrige skift ?></div>
		<?php } ?>
	</div>

    
<?php 
    //unsett current round:
    unset ($Vakten);
    unset ($AnsattID);
    unset($VaktHolder);
    unset($VaktHolder2);
    unset($VaktHolder3);
    $forrigeSkift = $detteSkiftet = $nesteSkift = "" ;
    $forrigeSkift_bak = $detteSkiftet_bak = $nesteSkift_bak = "" ;
    }
    }
    $n++;
    }

  if ($urlmod != "mobile_"){
?>
	<div class="defRow">
		<div class="spacer defCell"> </div>
	</div>
	<div class="defRow">
		<div class="spacer defCell"> </div>
		<div class="TableContent defInfo defCell Dag">Dag</div> 
		<div class="spacer defCell"> </div>
		<div class="TableContent defInfo defCell Kveld">Kveld</div> 
		<div class="spacer defCell"> </div>
		<div class="TableContent  defInfo defCell Natt">Natt</div> 
		<div class="spacer defCell"> </div>
		<div class="TableContent  defInfo defCell White">(X) Ekstra</div> 
		<div class="spacer defCell"> </div>
		<div class="TableContent  defInfo defCell White">(B) Bytte</div> 
		<div class="spacer defCell"> </div>
		<div class="TableContent  defInfo defCell Gray">Tilkallingsvakt</div> 
		<div class="spacer defCell"> </div>
		<div class="TableContent  defInfo defCell NormalDay">I Går</div> 
		<div class="spacer defCell"> </div>
		<div class="TableContent  defInfo defCell sx">I Morgen</div> 

</div>

<?php } ?>
</div>
<?php  } ?>
