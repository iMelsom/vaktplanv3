<?php
if ($uid != ""){ //block page usage WO login
    require_once 'vaktplan_files/turnus_dialogs.php';
    //Settig default values
    //AvdelingID, trengs for å behandle rett turnus
    $guid = getGUID($page); 
    $AvdName = hentAvdelingsnavn($guid);
    
    //error_log($guid);
    //error_log($guid);
    
    //Get user permissions to pages. If in group, allow editing, if not view only.
    $tilganger = HentTilganger($uid); 
    
    
    //Antall måneder manuelt ønsket:  
    $numMonths = $_REQUEST['numMonths'];
    
    // Henter standardverdier fra personlige innstillger dersom måneder ikke er oppgitt
    if (!$numMonths || $numMonths == '') $numMonths = $definedUserVariables[3];
    
    //henter defaultverdier dersom personlig alternativ ikke er satt
    if ((!$numMonths || $numMonths == '') && $urlmod == "mobile_")$numMonths = 1; //mobile view only one month. Speed up, data down.
    if (!$numMonths || $numMonths == '') $numMonths = 2; //default number of months to view
    
   
    //Antall dager som skal vises før i dag (historikklengde i visning): 
    $preDays = $definedUserVariables[7]; // hent fra personlige innstillinger
    if ($preDays == '') $preDays = 3; //default, dersom personlig innstilling ikke er satt
    
    
    //Date and time calculations
    $chosenDate = $_REQUEST['date'];
    if ($chosenDate != '') $startDate = strtotime($chosenDate);
    else $startDate = strtotime(date("d-m-Y", strtotime(date("d-m-Y", $TimeVar) . " -" . $preDays . " days")));
    $datepicker = $startDate;
    
    //Lagre oppriinnelig antall måneder. Dette trengs for de måneder der startmåneden kun er noen få dager. Da legger vi på en mnd.
    $numMonthsStart = $numMonths;
    //add a month if we are in the final week of first month
    if (date("t", $startDate) - date("j", $startDate) <=7 ) $numMonths++;
    
    //Counting days in view, Antall måneder må brekkes ned i antall dager, inkludert predager, og slutten av måneden ekstramåned. 
    for ($i =0; $i < $numMonths; $i++){
        if ($i == 0) $daysToCount = date("t", $startDate) - date("j", $startDate);
        if ($i > 0 ) $daysToCount = $daysToCount + date("t", strtotime(date("Y-m-d", $startDate). " +" . $i . "months"));
    }
    if ($daysToCount < date("t", $startDate)) $daysToCount = date("t", $startDate);
    $daysToCount = $daysToCount+1; //just one more day (due to base zero counting)
    if ($numMonths != $numMonthsStart)$daysToCount =  $daysToCount + $preDays; // To handle bug in monthshifting with predays.
    
    //Getting the pixel width of the year header:
    //Days between start & finish, if all days are in same year
    $dynWidthYear = $maxWidht = ($daysToCount)* 26; //width of deafult year no yearshift

    //Do we pass over into a new year?
    //Days between start and the new year:
    if (date("m", $startDate) > date("m", strtotime(date("Y-m-d", $startDate)." + " . $daysToCount . "days"))) $dynWidthYear = (365 - date("z", $startDate)) * 25; //widht of rest of year
    
    //Getting the pixel width of the first month header:
    $dynWidthMonth = (date("t", $startDate) - date("j", $startDate)) * 24; //width of first month header
    if (date("t", $startDate) == date("j", $startDate))$dynWidthMonth = 24;
    
    //Get employee list, including personell witout rota
    $ansatte = hentAnsatte3($guid, date("Y-m-d", $startDate));
    $numAnsatte = count($ansatte);
    
    
    //lag endrings- og fraværsarray:
    // Gjøres her av hastighetshensyn, selv om det kan bety en ekstra reload dersom endringer skjer i det turnus lastes og genereres. Risk for dette er lav og medfører kun at endringer ikke vises før reload
    $toDate = date("Y-m-d", strtotime(date("Y-m-d", $startDate)." + " . $daysToCount . "days"));
    $altfraver = hentFraversliste(date("Y-m-d",$startDate),$toDate );
    $alleendringer = hentVaktendringer(date("Y-m-d",$startDate),$toDate );
    
    //Get base rota:
    $turnusID = finnGjeldendeTurnus($startDate,$guid); //ID  = $turnusID[0] Startdato = $turnusID[1]
    $turnus = hentTurnus($turnusID[0]);
    ?>

<script>
//Script som tar seg av å markere en linje når det hueks av forran ansattnavn. Viser hele den turnuslinjen  med rød ramme. 
// valgt linje har CSS-markering " marklinje[ansattID]" (uten braketter)
//det genereres uheldigvis en scriptsnutt pr ansatt (utbedringspotensiale?) Dette gjøres slik nå siden visning er basert på kolonnefokus, og ikke linjefokus. 

function calc()  
{
	<?php
	       $TickTock = 1;
	       $resetColor = "#ffffff";
	
 			for($i=0; $i<$numAnsatte;$i++){ 
				?>
				  if (document.getElementById('markLineID<?php echo $ansatte[$i][0];?>').checked == true) 
				  {
					 $(".markLine<?php echo $ansatte[$i][0];?>").css("border-color", "red"); 
				  }
				  if (document.getElementById('markLineID<?php echo $ansatte[$i][0];?>').checked == false) 
				  {
						 $(".markLine<?php echo $ansatte[$i][0];?>").css("border-color", "black"); 
				  }
			<?php if ($TickTock == 1){ $TickTock = 2; $resetColor = "#f2f2f2";}
			      else {$TickTock = 1; $resetColor = "#ffffff";}
            } 
     ?>	
}
$(document).ready(function(){
	var realDate = new Date('<?php echo date("Y-m-d", $datepicker);?>');
	$( "#TurStart" ).datepicker({ dateFormat: 'yy-mm-dd',  firstDay: 1 });
	$( "#TurStart" ).datepicker('setDate', realDate);
});
</script>

<!--  Start turnusvisning -->
<div class="turnusWrap table">

	<!-- hent og lag Venstre kolonne -->
	
	<!-- Check-bokser for linjeutheving -->
    <div class =" turnusAgentList">
    		<div class="turnusDatoValg" ></div>
			<?php for($i=0; $i<$numAnsatte;$i++){ if ($ansatte[$i][0] != 2) {?>
				<div  class="TurnusAgentCellHeight markCell markRow"><input type="checkbox" id="markLineID<?php echo $ansatte[$i][0];?>" onclick="calc();"></div>
			<?php }} ?>	
 				<div  class="TurnusAgentCellHeight markCell markRow"><input type="checkbox" id="markLineID2" onclick="calc();"></div>
    </div>
    
    <!-- Dato- og lengdevelger -->
    <div class =" turnusAgentList">
   		<div class="turnusDatoValg" >
       		<form action="#" method="post" autocomplete="off">
    			<label> <?php  if ($urlmod != "mobile_")echo  "Vis turnus fra:";if ($urlmod == "mobile_")echo  "Start:"; ?>&nbsp;</label><input type="text" class ="strtDate" name="date" id="TurStart" onchange="this.form.submit()"></br>
    			<label> <?php  if ($urlmod != "mobile_")echo  "Turnusl";if ($urlmod == "mobile_")echo  "L"; ?>engde:&nbsp;</label><input type="text" class ="numMonths" name="numMonths" value="<?php echo $numMonths;?>" onchange="this.form.submit()"><label>&nbsp;  <?php  if ($urlmod != "mobile_")echo  "M&aring;neder";if ($urlmod == "mobile_")echo  "Mnd"; ?></label>
       		</form>
   		</div>
   		
   <!--  
   --   Lag ansattliste
   --   Anchor link for å kunne aktivere popup for å registere sykefravær
   --   alternerer farge for å gjøre lesing lettere
   --   Sjekker rettigheter (tilhører samme gruppe (skriverett) eller superbruker (turnusansvarlig), eller bare lov å se turnus (leserett, alle andre)) 
    -->		
			<?php $TickTock = 1; for($i=0; $i<$numAnsatte;$i++){ 
			    if ($ansatte[$i][0] != 2) { 
//			    error_log ("guid: " . $guid . " tilganger: " .  print_r($tilganger, 1), 0);
    			    if (in_array ($guid, $tilganger)){ 
    			    ?><!--  
    			       --><a class="regSyk" href="#">
    							<input type="hidden" Name ="Elevated" ID="Elevated" value="<?php echo $ElevatedAccess ?>">
    							<input type="hidden" Name ="AnsattID" ID="AnsattID" value="<?php echo $ansatte[$i][0] ?>">
    							<input type="hidden" ID="ForNavn" value="<?php echo $ansatte[$i][1] ?>">						
    							<input type="hidden" ID="uid" value="<?php echo $uid ?>">						
    							<input type="hidden" ID="sykFra" value="<?php echo date('Y-m-d' ,$TimeVar); ?>">	
    							<input type="hidden" ID="sykTil" value="<?php echo date('Y-m-d' ,$TimeVar); ?>">		
    							<input type="hidden" ID="ShowValidCheckbox" value="0">		
								<input type="hidden" ID="Description" value="">		
    							<input type="hidden" ID="GUID1" value="<?php echo $guid ?>"><?php } 
    							?><div  class="TurnusAgentCellHeight turnusAgents Axx<?php echo $TickTock;?> markLine<?php 
    						 echo $ansatte[$i][0];?>"><?php 
    						 echo $ansatte[$i][1] . " ";  if ($urlmod != "mobile_")echo $ansatte[$i][2]; if ($TickTock== 1) $TickTock = 2; else $TickTock = 1;?></div><?php 
    						 if (in_array($guid, $tilganger)) { ?><!--  
    				  --></a><?php } ?>
    			<?php }
			     } 
			     $navn = finnAnsattNavn(2);
			     $Etternavn = "";
			     if ($urlmod != "mobile_") $Etternavn = $navn[1];
//			     if ($urlmod == "mobile_") 
			     ?>	
			
			<!--  Ansatt nr 2 er Placeholderansatt "Udekket", skal ikke kunne settes syk eller ta i mot fravær-->
 				<div  class="TurnusAgentCellHeight turnusAgents Axx<?php echo $TickTock;?> markLine2"><?php echo $navn[0] . " " . $Etternavn; if ($TickTock== 1) $TickTock = 2; else $TickTock = 1;?></div>
	
		<!--  vis fraværskoder med beskrivelse, tre kolonner -->
		<div class="fravBeskrivelse"> 
			<?php
			for ($i=1; oversettFraversGrunn($i) != ''; $i+=1){
				$fraverBeskrivelse = oversettFraversGrunn($i);
				if (($i <= 4 && $urlmod != "mobile_")||($i <= 2 && $urlmod == "mobile_")){
				?>
				<div class="farger">
					<div class="beskrivelse <?php echo $fraverBeskrivelse[2];?>">
					</div>
					<div class="beskrivelsetext">
						<?PHP  if (!mb_check_encoding($fraverBeskrivelse[1], 'UTF-8'))echo utf8_encode($fraverBeskrivelse[1]); 
						else echo $fraverBeskrivelse[1];
						?>
					</div>
				</div>
			<?php } if (($i > 4 && $i <= 8&& $urlmod != "mobile_")||($i > 2 && $i <= 4&& $urlmod == "mobile_")){ ?>
				<div class="farger">
					<div class="beskrivelse <?php echo $fraverBeskrivelse[2];?>">
					</div>
					<div class="beskrivelsetext">
						<?PHP  if (!mb_check_encoding($fraverBeskrivelse[1], 'UTF-8'))echo utf8_encode($fraverBeskrivelse[1]); 
						else echo $fraverBeskrivelse[1];
						?> 
					</div>
				</div>
			<?php } if (($i > 8 && $urlmod != "mobile_")||($i > 4 && $urlmod == "mobile_")){ ?>
				<div class="farger">
					<div class="beskrivelse <?php echo $fraverBeskrivelse[2];?>">
					</div>
					<div class="beskrivelsetext">
						<?PHP  if (!mb_check_encoding($fraverBeskrivelse[1], 'UTF-8'))echo utf8_encode($fraverBeskrivelse[1]); 
						else echo $fraverBeskrivelse[1];
						?> 
					</div>
				</div>
			<?php }}?>
		</div>
 				
    </div>
    
    <!-- Start hovedvisning av turnus -->
    <div class ="Scrolltainer">
       	<div class ="ScrollInternal" style="width:<?php echo $maxWidht; ?>px">
       	
       	<!--  Header med overskrift, årstall og mulighet for å hoppe enkeltmåneder eller enketlår frem eller tilbake i tid -->
       		<div class="turnusDatoRow Yellow"> 
     			<div class="turnusYearHead YrRight"><a href="https://<?php echo $GLOBALS['MineVakterURL']; ?>?page=<?php echo $page; ?>&date=<?php echo Date("Y-m-01", strtotime('-1 year', $startDate)) ?>&numMonths=<?php echo $numMonths; ?>"><- forrige &aring;r</a></div>
    			<div class="turnusYearHead MntRight"><a href="https://<?php echo $GLOBALS['MineVakterURL']; ?>?page=<?php echo $page; ?>&date=<?php echo Date("Y-m-01", strtotime('-1 month', $startDate)) ?>&numMonths=<?php echo $numMonths; ?>"><- forrige m&aring;ned</a></div>
      			<div class="headDivider Yellow turnusYearHead" style="width:<?php echo $dynWidthYear-600;?>px;"><?php echo date("Y", $startDate);?></div>
    	   		<!-- If we get CSS fails when num months pass to next year, this is where -->
                <?php if (date("Y",$startDate) < (date("Y", strtotime(date("d-m-Y", $startDate). " + " . $numMonths . " months")))) { 
                    $dynWidthYear_old = $dynWidthYear;
                    $dynWidthYear = ($daysToCount - ($dynWidthYear/25))*25;
                    ?>
           			<div class=" headDivider Yellow turnusYearHead" style="left:<?php echo $dynWidthYear_old+600;?>px;"><?php echo date("Y", $startDate) +1;?></div>
                <?php }?>
    			<div class="turnusYearHead MntLeft"><a href="https://<?php echo $GLOBALS['MineVakterURL']; ?>?page=<?php echo $page; ?>&date=<?php echo Date("Y-m-01", strtotime('+1 month', $startDate)) ?>&numMonths=<?php echo $numMonths; ?>">neste m&aring;ned -></a></div>
    			<div class="turnusYearHead YrLeft"><a href="https://<?php echo $GLOBALS['MineVakterURL']; ?>?page=<?php echo $page; ?>&date=<?php echo Date("Y-m-01", strtotime('+1 year', $startDate)) ?>&numMonths=<?php echo $numMonths; ?>">neste &aring;r-></a></div>
       		</div>

      		<div class="turnusDatoRow Yellow"> 
      		 <?php    for ($i =0; $i < $numMonths; $i++){ 
                    $currDate = strtotime(date("28-m-Y", $startDate) . " + " . $i . " month");
                    if ($dynWidthMonth == 0)$dynWidthMonth = date("t", $currDate)  * 24; 
                    ?>	
       			<div class="Yellow turnusMonthHead" style="width:<?php echo $dynWidthMonth +30;?>px;">
       				<?php  echo strftime('%b',$currDate);?>
       			</div>
            	<?php $dynWidthMonth = 0;} ?>
    		</div>
    		
    		<!-- Start å generere turkolonner, en kolonne pr dag -->
    		
      		<div class="turnusDatoRow"> 
     		<?php    
                for ($i =0; $i < $daysToCount; $i++){ 
                    //Send current round to screen if on pc:
                    if ($urlmod != "mobile_" && $definedUserVariables[9] == "1"){
                        ob_end_flush(); // her forsøker vi å få turen til å dumpe så langt den er kommet. Hadde mer effekt før optimalisering
                        ob_start();
                        //flush();
                        //ob_flush();
                    }
                    
                    $currDate = strtotime(date("d-m-Y", $startDate) . " + " . $i . " days");
                    $PrintedVakt = array();
                    
                    if (date('d-m-Y', $currDate) == date('d-m-Y', $TimeVar)){
                        if (date("D", $currDate) == 'Sat' || date("D", $currDate) == 'Sun' || testForHoliday($currDate)) $DayType = "TodayHoliday";
                        else $DayType = "Today";
                    }
                    else if (date("D", $currDate) == 'Sat' || date("D", $currDate) == 'Sun' || testForHoliday($currDate)) $DayType = "Holiday";
                    else $DayType = "";
                 ?>	
                 
                 <!--  er det onsdag legg inn ukenummer øverst -->
            	<div class="turnusCol">
            		<div class="turnusDatoRow Yellow turnusWeekHead"><?php if (date("D", $currDate) == "Wed") echo date("W", $currDate);?></div>
            		<div class="turnusDatoRow turnusDayHead <?php echo $DayType; ?>"><?php echo date("d", $currDate);?></div>
            		<div class="turnusDatoRow turnusDayHead <?php echo $DayType; ?>"><?php echo strftime("%a",$currDate);?></div>
            		
            		<!--  Hent og list den aktive dagens vakter -->
        			<?php 
        			$g=0;
        			
        			//First make sure the rota is the current one:
        			// sjekk om dagens dato er større enn eller lik aktiv turnus sitt endetidspunkt, og om lenge turnusen har en satt ende. Har vi passert ende, må det hentes ny tur
        			if ( $currDate > $turnusID[4] && $turnusID[4] != NULL){
            			    $turnusID = finnGjeldendeTurnus($currDate, $guid);
            			    $turnus = hentTurnus($turnusID[0]);
            			}

                    $TickTock = 1; 
        			$c = 1;
        			for($j=0; $j<$numAnsatte;$j++){ 
        			        $OrgVakt = '';
        			        if ($ansatte[$j][0] != 2) { 
        			            //$SjekkStartet = turnusStartet($ansatte[$j][0], $currDate, $guid);
        			            if (is_array($turnusID) && strtotime($turnusID[1]) <= $currDate /*&& $SjekkStartet*/){
        			                
        			                //hent den ansattes vakt i turusen. Den regnes ut pr ansatt etter en offsett-regel. 
        			                //kan optimaliseres i V3, slik at man regner ut offsetplassering kun en gang etter startdag. 
        			                $vakt =  HentVakt($ansatte[$j][0], $currDate, $turnus, $turnusID[1], $guid);
        			                if (getOffset($ansatte[$j][0] , $currDate)) $regPrintedOffset[$c] = getOffset($ansatte[$j][0] , $currDate)[0];
        			                $c++;
        			                //error_log(print_r($vakt,true),0);
        			                //resett noen nødvendige variabler
        			                $BorteVekke = false;
        			                $Hjemmekontor = false;
        			                $showUtfortDato = false;
        			                
        			                //hent personens fravær
        			                $fraver = sjekkFraver2($altfraver[$ansatte[$j][0]], $currDate);
        			                
        			                //hent fraværsbeskrivelse om det er fravær
        			                if (is_array($fraver))$fraverBeskrivelse = oversettFraversGrunn($fraver[3]);
        			                $beskrivelse = $fraverBeskrivelse[1];
        			                if ($fraver[4] != "") $beskrivelse = $beskrivelse .  "\r\n" . $fraver[4];
        			                $OrgVakt = $beskrivelse .  "\r\n Org. vakt: " . $vakt[1];
        			                
        			               //sørg for å flytte turnusvakter dersom fravær er registrert
        			                if (is_array($fraver)){
        			                    if (!sjekkVaktFlytt($vakt[0], $currDate)){
        			                        if ($vakt[1] != 'X' && $vakt[1] != 'T' && $vakt[1] != 'O' && $vakt[1] != 'I' && $vakt[1]!= '' && $fraver[3] != '9' && $fraver[3] != '13' ){
        			                            $TilUdekket[$g][0]=$vakt[0];
        			                            $TilUdekket[$g][1]=$vakt[1];
        			                        }
        			                        $g++;
        			                    }
        			                    if ($fraver[3] != '9' && $fraver[3] != '13' )$BorteVekke = True;
        			                    if ($fraver[3] == '9' || $fraver[3] == '13' )$Hjemmekontor = True;
        			                }
        			                if ($BorteVekke == False && !in_array($vakt[0],$PrintedVakt)) array_push($PrintedVakt, $vakt[0]);
        			                
        			                $SkipMe = false; //skal det komme mulighet for å flytte eller bytte vakta? Dersom den allerede er byttet/flyttet skal den såt markert, men ikke kunne flyttes igjen her. 
        			                
        			                if (is_array($alleendringer[date("Y-m-d", $currDate)][$vakt[0]])){
        			                    //gi vakta gråfarge og marker den byttet bort
        			                    if ($alleendringer[date("Y-m-d", $currDate)][$vakt[0]][3] == "Bytte" && $alleendringer[date("Y-m-d", $currDate)][$vakt[0]][2] !=  $ansatte[$j][0]){
        			                        $VaktFarging = 'ByttetBort ';
        			                        $vakt[1] = '<font color="#a6a6a6">' . $vakt[1] . 'b></font>'; 
        			                        $SkipMe = true;
        			                        
        			                    }
        			                    //gi vakta gråfarge, den er flyttet og skal ikke markeres byttet
        			                    if ($alleendringer[date("Y-m-d", $currDate)][$vakt[0]][3] != "Bytte" && $alleendringer[date("Y-m-d", $currDate)][$vakt[0]][2] !=  $ansatte[$j][0]) {
        			                        $VaktFarging = 'ByttetBort ';
        			                        $vakt[1] = '<font color="#a6a6a6">' . $vakt[1] . '></font>'; 
        			                        $SkipMe = true;
        			                        
        			                    }
        			                    
        			                }
        			                
        			                //Dersom dette er en bakvakt med oppmøtetid, hent oppmøtetidene (onprem) for å vise tidene i mouseover
        			                if ($vakt[8] != '' || $vakt[8] != NULL) $vakt[6] = $vakt[8];
        			                if ($vakt[9] != '' || $vakt[9] != NULL) $vakt[3] = $vakt[9];
        			                if ($vakt[10] != '' || $vakt[10] != NULL) $vakt[4] = $vakt[10];
        			                $start = $vakt[3];
        			                $end = $vakt[4];
        			                $length = $vakt[6];
        			                
        			                //create mouseover link for å hente popup for å flytte eller bytte vakt. 
        			                if ((($vakt[1] != 'X' && $vakt[1] != 'T' && $vakt[1] != 'O' )|| $ElevatedAccess == 1)
        			                && $vakt[1] !=  ''&& $vakt[1] !=  'I'
				                          && $SkipMe == false 
				                          && in_array($guid, $tilganger))
        			                    $vakt[1] = '<a class="opener" href="#" title="Start: ' . $vakt[3] . ' Varighet: ' . $vakt[6] . ' Slutt: ' . $vakt[4]. '">
										<input type="hidden" ID="vaktID" value="' . $vakt[0] . '">
										<input type="hidden" ID="gruppeID" value="' . $guid. '">
										<input type="hidden" ID="vaktNavn" value="' . $vakt[1] . '">
										<input type="hidden" ID="vaktDato" value="' . date('Y-m-d', $currDate) . '">
										<input type="hidden" ID="vaktEier" value="' . $ansatte[$j][0] . '">
										<input type="hidden" ID="gjortAv" value="' . $uid . '">
										<input type="hidden" ID="GUID2" value="' . $guid . '">
										'. $vakt[1] . '
										</a> ';
        			                    
        			                    //har personen ekstravakter på den aktive dagen? Kan være flere enn en...
        			                    foreach ($alleendringer[date("Y-m-d", $currDate)] as &$value) {
        			                        if ($value[2] == $ansatte[$j][0]){
        			                            //Dersom personen kan ha vakter for flere grupper, sjekk om ekstravakta er for den aktive gruppen
        			                            $TrueVakt = ErDetRettGruppe($value[1],$guid);
        			                            
        			                            //Sjekk at vakten ikke har blitt flyttet videre til en annen person        			                            
        			                            if (fortsattGyldig($value[4], $value[1], $ansatte[$j][0], $currDate) && $BorteVekke == false && $TrueVakt){
        			                                $ekstravakt_beskrivelse = sjekkVaktType($value[1]);
        			                                
        			                                $BytteData = "";
        			                                if ($Printvakt != '' )$Printvakt = $Printvakt . '</br>';
        			                                
        			                                //Dersom dette er en bakvakt med oppmøtetid, hent oppmøtetidene (onprem) for å vise tidene i mouseover
        			                                if ($ekstravakt_beskrivelse[8] != '' || $ekstravakt_beskrivelse[8] != NULL) $$ekstravakt_beskrivelse[6] = $ekstravakt_beskrivelse[8];
        			                                if ($ekstravakt_beskrivelse[9] != '' || $ekstravakt_beskrivelse[9] != NULL) $ekstravakt_beskrivelse[3] = $ekstravakt_beskrivelse[9];
        			                                if ($ekstravakt_beskrivelse[10] != '' || $ekstravakt_beskrivelse[10] != NULL) $ekstravakt_beskrivelse[4] = $ekstravakt_beskrivelse[10];
        			                                $start = $ekstravakt_beskrivelse[3];
        			                                $end = $ekstravakt_beskrivelse[4];
        			                                $length = $ekstravakt_beskrivelse[6];
        			                                
        			                                //create mouseover link for å hente popup for å flytte eller bytte vakt. 
        			                                if (in_array($guid, $tilganger))$Printvakt = $Printvakt . '
        												<a class="opener" href="#" title="Start: ' . $ekstravakt_beskrivelse[3] . ' Varighet: ' . $ekstravakt_beskrivelse[6] . ' Slutt: ' . $ekstravakt_beskrivelse[4]. $BytteData . '">
        													<input type="hidden" ID="vaktID" value="' . $ekstravakt_beskrivelse[0] . '">
        													<input type="hidden" ID="vaktNavn" value="' . $ekstravakt_beskrivelse[1] . '">
        													<input type="hidden" ID="vaktDato" value="' . date('Y-m-d', $currDate) . '">
        													<input type="hidden" ID="vaktEier" value="' . $ansatte[$j][0]. '">
        													<input type="hidden" ID="gjortAv" value="' . $uid . '">
        													<input type="hidden" ID="GUID2" value="' . $guid . '">
        											';
        			                                
        			                                
        			                                //Legg vakten i utskriftsbufferet
        			                                $Printvakt = $Printvakt . '>';
        			                                $Printvakt = $Printvakt . $ekstravakt_beskrivelse[1];
        			                                if ($value[2] == 'Bytte'){
        			                                    $Printvakt =   $Printvakt .  'b';
        			                                }
        			                                if (in_array($guid, $tilganger))$Printvakt = $Printvakt . '</a> ';
        			                            }
        			                            
        			                            if (fortsattGyldig($value[4], $value[1], $ansatte[$j][0], $currDate) && $BorteVekke == true){
        			                                $ekstravakt_beskrivelse = sjekkVaktType($value[1]);
        			                                $OrgVakt = $OrgVakt . " og Xtra-vakt: " . $ekstravakt_beskrivelse[1];
        			                                $TilUdekket[$g][0]=$ekstravakt_beskrivelse[0];
        			                                $TilUdekket[$g][1]=$ekstravakt_beskrivelse[1];
        			                                $g++;
        			                            }
        			                            
        			                            if (!in_array($ekstravakt_beskrivelse[0],$PrintedVakt)) array_push($PrintedVakt, $ekstravakt_beskrivelse[0]); // legger til vakten som dekket
        			                        }
        			                    }
        			                    

        			                    ?>
        			                    
        			                    <!--  Dersom det er registert fravær list dette. Superbruker kan endre fravær her -->
            				<div  class="TurnusDefaultCellHeight turnusContentCells markLine<?php 
            				                echo $ansatte[$j][0] . " ";
            				                if ($ElevatedAccess == 1 && ($BorteVekke || $Hjemmekontor)) echo ' regSyk ';
            				                if ($DayType == "" && !$BorteVekke && !$Hjemmekontor) echo 'Axx'.$TickTock; 
									        else if ($BorteVekke || $Hjemmekontor) echo $fraverBeskrivelse[2];
									        else echo $DayType;
									        ?>"<?php 
									        if (!$BorteVekke && $vakt[1] != 'X' && $vakt[1] != 'T' && $vakt[1] != 'O' && $vakt[1] != 'I' && $vakt[1] !=  '' && $SkipMe == false) echo 'title="Start: ' . $start . ' Varighet: ' . $length . ' Slutt: ' . $end . '"';
        									if ($BorteVekke) echo ' title="' . $OrgVakt . '"';
        									echo '"';
        									?> >
            					<?php 
            					if (!$BorteVekke && $vakt[1] != 'I') echo $vakt[1];
            					if (!$BorteVekke && $Printvakt != 'I' && $Printvakt != '' && $TrueVakt) echo "<br>".$Printvakt;
            					if ($BorteVekke || $Hjemmekontor){
            					    if ($ElevatedAccess == 1){ ?>
										<form  autocomplete="off">
											<input type="hidden" ID="Task" value="Update">
											<input type="hidden" ID="Elevated" value="<?php echo $ElevatedAccess ?>">
											<input type="hidden" ID="FravID" value="<?php echo $fraver[5]; ?>">
											<input type="hidden" NAME="Ansatt" ID="Ansatt" value="<?php echo $ansatte[$j][0]; ?>">
											<input type="hidden" ID="Grunn" value="<?php echo $fraver[3]; ?>">
											<input type="hidden" ID="FraDato" value="<?php echo $fraver[0]; ?>">
											<input type="hidden" ID="TilDato" value="<?php echo $fraver[1]; ?>">
											<input type="hidden" ID="Description" value="<?php echo $fraver[4]; ?>">
											<input type="hidden" ID="uid" value="<?php echo $uid; ?>">
											<input type="hidden" ID="GUID2" value="<?php echo $guid; ?>">
											<input type="hidden" ID="ShowValidCheckbox" value="1">		
										</form>
										
										
									<?php } //regSyk
								}
								
								
            					//$vakt[1] = '';
            					$Printvakt = '';
            					unset($vakt);
            					unset($Printvakt);
            					
            					if ($TickTock== 1) $TickTock = 2; 
            					   else $TickTock = 1;
            					 ?>
            				</div>
        			<?php }}} 
        			
        			//Sjekk UDekt:
        			
        			$vakt[1] = '';
        			$Printvakt = '';
        			$BorteVekke = false;
        			
/*
 *                  turnuslengde i uker = max ofset. Sjekk om alle ofset fins i  $regprintedoffset
//        			$turnusID
*/
        			//check that all tours have been printed: 
        			for ($K = 1; $K <= $turnusID[3]; $K++){
        			    unset($vaktTempTemp);
        			    unset($value);
        			    
        			    if (!in_array($K, $regPrintedOffset)){
        			        //sjekk at turen virkelig ikke er besatt enda: 
        			        //OBS: Feiler p� n�r den skal starte � vise som udekket, og viser som helt udekket hele tiden 
        			        
        			        if (!sjekkOffset($K,$turnusID[2], date('Y-m-d', $currDate))){
        			            $value = HentVaktUdekket($K, $currDate, $turnus, $turnusID[1]);
         			            if (!in_array($value[0],$PrintedVakt)){
         			                if ($value[1] != 'X' && $value[1] != 'O' && $value[1] != 'T'  && $value[1] != 'I' && in_array($guid, $tilganger)){
            			                $vaktTempTemp =  $vaktTempTemp . '<a class="opener Test' . $K . '" href="#" onclick="notCall(event)"><!--';
            			                $vaktTempTemp =  $vaktTempTemp . '--><input type="hidden" ID="vaktID" value="' . $value[0] . '">';
            			                $vaktTempTemp =  $vaktTempTemp . '<input type="hidden" ID="vaktNavn" value="' . $value[1] . '">';
            			                $vaktTempTemp =  $vaktTempTemp . '<input type="hidden" ID="vaktDato" value="' . date('Y-m-d', $currDate) . '">';
            			                $vaktTempTemp =  $vaktTempTemp . '<input type="hidden" ID="vaktEier" value="2">';
            			                $vaktTempTemp =  $vaktTempTemp . '<input type="hidden" ID="gjortAv" value="' . $uid . '">';
            			                $vaktTempTemp =  $vaktTempTemp . '<input type="hidden" ID="GUID2" value="' . $guid . '">';
            			            }
            			            if ($value[1] != 'I')$vaktTempTemp =  $vaktTempTemp .  $value[1]; //we wish to mask the "I" vakt, it means "Ikke på vakt". 
            			            if ($value[1] != 'X' && $value[1] != 'O' && $value[1] != 'T'  && $value[1] != 'I' && in_array($guid, $tilganger))$vaktTempTemp =  $vaktTempTemp . '</a>';
            			            $vaktTempTemp =  $vaktTempTemp . '<br>';
            			            $vaktTemp =  $vaktTemp . $vaktTempTemp;
            			            //error_log($vaktTemp);
        			            }
        			        }
        			    }
        			}
        			
        			if (is_array($TilUdekket)){
        			    foreach ($TilUdekket as $value){
        			        
        			        if ($value[1] != 'X' && $value[1] != 'O' && $value[1] != 'T'  && $value[1] != 'I' && in_array($guid, $tilganger)){
        			            $vaktTemp =  $vaktTemp . '<a class="opener" href="#">';
								$vaktTemp =  $vaktTemp . '<input type="hidden" ID="vaktID" value="' . $value[0] . '">';
								$vaktTemp =  $vaktTemp . '<input type="hidden" ID="vaktNavn" value="' . $value[1] . '">';
								$vaktTemp =  $vaktTemp . '<input type="hidden" ID="vaktDato" value="' . date('Y-m-d', $currDate) . '">';
								$vaktTemp =  $vaktTemp . '<input type="hidden" ID="vaktEier" value="2">';
								$vaktTemp =  $vaktTemp . '<input type="hidden" ID="gjortAv" value="' . $uid . '">';
								$vaktTemp =  $vaktTemp . '<input type="hidden" ID="GUID2" value="' . $guid . '">';
        			        }
        			        if ($value[1] != "I"){
        			        $vaktTemp =  $vaktTemp .  $value[1];
        			        if (in_array($guid, $tilganger))$vaktTemp =  $vaktTemp . '</a>';
        			        $vaktTemp =  $vaktTemp . '<br>';
        			        }
        			    }
        			}
        			
        			$ekstravakt = sjekkEkstravakt('2', $currDate);
        			if (is_array($ekstravakt) && $ekstravakt[0] != '') {
        			    $f = 0;
        			    while($ekstravakt[$f] != ''){
        			        if (fortsattGyldig($ekstravakt[$f][0], $ekstravakt[$f][1], '2', $currDate) && ErDetRettGruppe($ekstravakt[$f][1],$guid)){
        			            $ekstravakt_beskrivelse = sjekkVaktType($ekstravakt[$f][1]);
        			            if ($vaktTemp != '' )$vaktTemp = $vaktTemp . '</br>';
        			            if (in_array($guid, $tilganger) && $ekstravakt_beskrivelse[1] != "I"){
            			            $vaktTemp = $vaktTemp . '<a class="opener" href="#" >';
            			            $vaktTemp = $vaktTemp . '<input type="hidden" ID="vaktID" value="' . $ekstravakt_beskrivelse[0] . '">';
    								$vaktTemp = $vaktTemp . '<input type="hidden" ID="vaktNavn" value="' . $ekstravakt_beskrivelse[1] . '">';
    								$vaktTemp = $vaktTemp . '<input type="hidden" ID="vaktDato" value="' . date('Y-m-d', $currDate) . '">';
    								$vaktTemp = $vaktTemp . '<input type="hidden" ID="vaktEier" value="2">';
    								$vaktTemp = $vaktTemp . '<input type="hidden" ID="gjortAv" value="' . $uid . '">';
    								$vaktTemp = $vaktTemp . '<input type="hidden" ID="GUID2" value="' . $guid . '">';
        			            }
        			            //if ($ekstravakt[$f][2] == 'Bytte'){
        			            $vaktTemp = $vaktTemp . '>';
        			            //	}
        			            if (in_array($guid, $tilganger))$vaktTemp = $vaktTemp . $ekstravakt_beskrivelse[1] . '</a> ';
        			        }
        			        $f++;
        			    }
        			}
        			
        			?>	
    				<div  class="turnusContentCells Udekket markLine2 <?php if ($DayType == "") echo 'Axx'.$TickTock; else echo $DayType;?>"><?php 
            					if (!$vaktTemp) $vaktTemp = ""; //ikketest!
            					if ($ekstravakt_beskrivelse[1] != "I")echo $vaktTemp; //ikketest!
            					if ($TickTock== 1) $TickTock = 2;
            					else $TickTock = 1;
            					$vaktTemp  = '';
            					$vakt[1] = '';
            					unset($vaktTemp);
            					unset ($TilUdekket);
            					unset ($regPrintedOffset);
            					?></div>
                 </div>
     <?php 
     //Send current round to screen if on pc:
     if ($urlmod != "mobile_" && $definedUserVariables[9] == "1"){
         //error_log ($urlmod . " og " . $definedUserVariables[9]);
        // ob_start();
         flush();
         ob_flush();
         ob_end_flush();
     }
     
                
                } ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>
