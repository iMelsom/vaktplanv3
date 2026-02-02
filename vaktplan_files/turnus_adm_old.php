<?php 
if ($uid != ""){
    $groupid = $GLOBALS['UserPermissions'];

?>
<div class="AdminWrapper">
<?php
for($ja=0; $ja< count($groupid); $ja++){
    if ($groupid[$ja] != 13){

    $Turnus = finnToppTreTurnus($groupid[$ja]);
    //error_log (print_r($Turnus, true));
    $numTours = count ($Turnus);
    // Alle turnuser er i utgangspunktet ikke der! : 
    $TurnusPlanForrige = "";
    $TurnusPlan = "";
    $TurnusPlanNeste = "";
    $PrevTourID = "";
    $CurrentTourID = "";
    $NextTourID = "";

    //populer turnuser med data fra database, etter starttid p� turnus
    if ($numTours == 1 && $Turnus[0][1] > date("Y-m-d", $TimeVar) ){ //kun en tur, Startdato i fremtiden
        $TurnusPlanNeste = hentTurnus($Turnus[0][0]);
        $NextTourID = $Turnus[0];
    }
    if ($numTours == 1 && $Turnus[0][1] <= date("Y-m-d", $TimeVar)){ //kun en tur, n�v�rende
        $TurnusPlan = hentTurnus($Turnus[0][0]);
        $CurrentTourID = $Turnus[0];
    }
    if ($numTours == 2 && $Turnus[0][1] > date("Y-m-d", $TimeVar) ){ // To turnuser, n�v�rende og fremtidig
        $TurnusPlan = hentTurnus($Turnus[1][0]);
        $TurnusPlanNeste = hentTurnus($Turnus[0][0]); 
        $CurrentTourID = $Turnus[1];
        $NextTourID = $Turnus[0];
    }
    if ($numTours == 2 && $Turnus[0][1] <= date("Y-m-d",$TimeVar)){ // To turnuser, n�v�rende og forrige
        $TurnusPlanForrige = hentTurnus($Turnus[1][0]);
        $TurnusPlan = hentTurnus($Turnus[0][0]);
        $PrevTourID = $Turnus[1];
        $CurrentTourID = $Turnus[0];
    }
    //Tre turnuser, Tre fremtidige
    if ($numTours == 3 && $Turnus[0][1] > date("Y-m-d", $TimeVar) && $Turnus[1][1] > date("Y-m-d", $TimeVar) && $Turnus[2][1] > date("Y-m-d", $TimeVar)){ 
        $TurnusPlanNeste = hentTurnus($Turnus[2][0]);
        $NextTourID = $Turnus[2];
    }
    //Tre turnuser, n�v�rende og to fremtidige, effektivt 2... 
    if ($numTours == 3 && $Turnus[0][1] > date("Y-m-d", $TimeVar) && $Turnus[1][1] > date("Y-m-d", $TimeVar) && $Turnus[2][1] <= date("Y-m-d", $TimeVar)){
        $TurnusPlan = hentTurnus($Turnus[2][0]);
        $TurnusPlanNeste = hentTurnus($Turnus[1][0]);
        $CurrentTourID = $Turnus[2];
        $NextTourID = $Turnus[1];
    }
    //Tre turnuser, forrige, n�v�rende og neste
    if ($numTours == 3 && $Turnus[0][1] > date("Y-m-d", $TimeVar) && $Turnus[1][1] <= date("Y-m-d", $TimeVar) && $Turnus[2][1] <= date("Y-m-d", $TimeVar)){
        $TurnusPlanForrige = hentTurnus($Turnus[2][0]);
        $TurnusPlan = hentTurnus($Turnus[1][0]);
        $TurnusPlanNeste = hentTurnus($Turnus[0][0]);
        $PrevTourID = $Turnus[2];
        $CurrentTourID = $Turnus[1];
        $NextTourID = $Turnus[0];
    }
    //Tre turnuser, to gamle, og n�v�rende, effektivt 2... 
    if ($numTours == 3  && $Turnus[0][1] <= date("Y-m-d", $TimeVar) && $Turnus[1][1] <= date("Y-m-d", $TimeVar) && $Turnus[2][1] <= date("Y-m-d", $TimeVar)){
        $TurnusPlanForrige = hentTurnus($Turnus[1][0]);
        $TurnusPlan = hentTurnus($Turnus[0][0]);
        $PrevTourID = $Turnus[1];
        $CurrentTourID = $Turnus[0];
    }
    
    //$TurnusPlan = hentTurnus($Turnus[0]);
    $Fordeling = TurnusFordeling($CurrentTourID[0], $TimeVar);
    $ansatte = HentTurnusAktive($groupid[$ja]); 
	$TickTock = 1;
	
	
	//Create ansattlist of tourless: 
//	$ansatte[$t][0]
    unset($utentur2);
    $eep = 0;
	for($counter = 0; $counter < count($ansatte); $counter++){
	    if(!harTur($ansatte[$counter][0],$groupid[$ja], $CurrentTourID[0], date("Y-m-d", $TimeVar)) 
	           || harSnartTur($ansatte[$counter][0],$groupid[$ja], $CurrentTourID[0], date("Y-m-d", $TimeVar))
	           && $ansatte[$counter][0] != "20")
	        {
	        $utentur2[$eep] = $ansatte[$counter];
	        $eep++;
	    }
	}
	//error_log(print_R($utentur2, true), 0);
	
	
	?>
	<div class="tViewWrapper">
		<div class="TurnusViewer AdminButtons">
			<div class="AdmButton">
				<a class="ui-button ui-widget ui-corner-all OpenNewTour admWidth" href="#">
				<input type="hidden" ID="uID" value="<?php echo  $uid ?>">						
				<input type="hidden" ID="GrID" value="<?php echo  $groupid[$ja] ?>">						
				Ny </a>
			</div>
			<div class="AdmButton">
				<a class="ui-button ui-widget ui-corner-all OpenAddVacAdjust admWidth" href="#">
				<input type="hidden" ID="uID" value="<?php echo  $uid ?>">						
				<input type="hidden" ID="sendMail" value="quiet">						
				<input type="hidden" ID="ShowValidCheckbox" value="0">		
				<input type="hidden" ID="groupid" value="<?php echo  $groupid[$ja] ?>">						
				Avvik</a>
			</div>

			<div class="TurnusViewer UnassignedPersonell " ID="UnassignedPersonell">
					<div class="uAssignHead">
						Ledige eller planlagte: 
					</div>
					<div class="target Unassigned">
						<?php
						$jp = 0 ;
						while ($jp <= count ($utentur2)){
						    $Fremtidstur = sjekkFremtidigTur($utentur2[$jp][0], $groupid[$ja]);
						   // error_log("turnus_adm  118: " . print_r($Fremtidstur, true), 0);
						    $start = "";
						    if (isset($Fremtidstur[0])) $start = " (tur " . $Fremtidstur[3] . ", " . Date("d.m", strtotime($Fremtidstur[4])) . ")";
						?>
							<div class="dragdropTurAssign LTail floatDown Assignable setStartDateAnsattTour" name="DragDrop" id="<?php echo $utentur2[$jp][0]; ?>">
								<?php 
								echo $utentur2[$jp][1] . $start;
								?>
    							<input type="hidden" ID="groupassign" value="<?php echo  $groupid[$ja] ?>">	
    							<?php 
            					$AvailTour = array();
            					
            					//$w er antall turer i en turnus. Det må finnes Antall ledige, evt når i nåværende, samt antall ledige samt når i neste. 
            					if (is_array($TurnusPlan)||is_array($TurnusPlanNeste)){
            					    if ($NextTourID[3] >= $CurrentTourID[3]){ //Er de like lange, eller er neste tur lengre, er kan vi bruke lengden til neste turnus 
            					        $numTours = $NextTourID[3];
            					    }
            					    if ($NextTourID[3] < $CurrentTourID[3]){ // er neste turnus kortere (eller ikke satt) bruker vi nåværende
            					        $numTours = $CurrentTourID[3];
            					    }
            					}
            					$blupp = 1;
            					for ($nqi=1; $nqi<= $numTours; $nqi++)
            					{
            					    $chckFree = isTurFree($nqi, $CurrentTourID[2]);
            					    if($chckFree[0] != ''){
            					        $AvailTour[$blupp] = array("TourID" =>$nqi, "AvailDate" => $chckFree[0] );
            					        $blupp++;
            					    }
            					    if ($nqi > $CurrentTourID[3] && $chckFree[0] == ''){
            					        $AvailTour[$blupp] = array("TourID" =>$nqi, "AvailDate" => $NextTourID[1] );
            					        $blupp++;
            					    }
            					}
            					
            					//error_log("Blippa: " . print_r($AvailTour, true));
            					if ($AvailTour[1] == '' || !$AvailTour[0]) $outputJSON = "Ingen ledige turer";
            					if ($AvailTour[1] != '') $outputJSON = json_encode($AvailTour);
            					//error_log("Blippa: " . json_encode($AvailTour));
            					
            					?>
            					<input type="hidden" ID="freeTourData" value="<?php echo htmlspecialchars( $outputJSON ); ?>">
            				
												
							</div>
						<?php
						$jp++;
						}
						?>
						
					</div>
			</div>			
		</div>
		<div class="TurnusSpacer"></div>
		<div class="TurnusViewer PrevTurnus">
			<div class="HeadLine">
				<div class="TurnusViewerHead floatLeft">
					Forrige turnus for <?php if (SjekkAlias($groupid[$ja])) echo SjekkAlias($groupid[$ja])[0]; else echo hentAvdelingsnavn($groupid[$ja]); ?><br>
					<?php  if ($TurnusPlanForrige != "") echo  "Fra: " . Date("d.m.y", strtotime($PrevTourID[1])). " Til: " . Date("d.m.y", strtotime($PrevTourID[4])); ?>
					<?php  if ($TurnusPlanForrige == "") echo "Ingen tidligere turnus" ; ?>
				</div>
			</div>
			<?php
			if ($TurnusPlanForrige != ""){
			$j = 0;
			$TickTock = 1;
			?>
			<div class="TurnusBox floatLeft">
				<div class="WeekLine floatDown">
					<div class="Vakt LeaderHead floatLeft Axx<?php echo $TickTock;?>">  </div>
				</div>
			</div>
				<?php
			if ($TickTock== 1) $TickTock = 2;
			else $TickTock = 1;
			$w = 1;
			while ($j < count ($TurnusPlanForrige)){
			?>
			<div class="TurnusBox floatLeft">
				<div class="WeekLine floatDown">
					<div class="Vakt LHead floatLeft Axx<?php echo $TickTock;?>"> Uke <?php echo $w; ?> </div>
					<?php
					for ($n=0; $n < 7; $n++){
					    $Vaktinfo =sjekkVaktType($TurnusPlanForrige[$j]);
					?>
					<div class="Vakt Axx<?php echo $TickTock;?>"><?php echo $Vaktinfo[1] ?> </div>
					<?php
						$j++;
						}
					?>
				</div>
			</div>
				<?php
				$w++;
				if ($TickTock== 1) $TickTock = 2;
				else $TickTock = 1;
				}
	//			echo $w;
			}
				?>
		</div>
		<div class="TurnusSpacer"></div>
		<div class="TurnusViewer CurrentTurnus">
			<div class="HeadLine">
				<div class="TurnusViewerHead floatLeft setEndDateTour" id="<?php echo $CurrentTourID[0]; ?>" name="<?php echo hentAvdelingsnavn($groupid[$ja]); ?>">
					Aktiv turnus for <br><?php if (SjekkAlias($groupid[$ja])) echo SjekkAlias($groupid[$ja])[0]; else echo hentAvdelingsnavn($groupid[$ja]); ?><br>
					<?php  if ($TurnusPlan != "") echo "Fra: " . Date("d.m.y", strtotime($CurrentTourID[1])); ?>
					<?php  if ($CurrentTourID[4] != "") echo " Til: " . Date("d.m.y", strtotime($CurrentTourID[4])); ?>
					<?php  if ($TurnusPlan == "") echo "Ingen tidligere turnus" ; ?>
				</div>
				<div class="TurnusViewerTail floatLeft">
					</br></br>Fordeling: 
				</div>
			</div>
			<?php
			if ($TurnusPlan != ""){
			$j = 0;
			$TickTock = 1;
			?>
			<div class="TurnusBox floatLeft">
				<div class="WeekLine floatDown">
					<div class="Vakt LeaderHead floatLeft Axx<?php echo $TickTock;?>"> Leder: </div>
				</div>
			</div>
				<?php
			if ($TickTock== 1) $TickTock = 2;
			else $TickTock = 1;
			$w = 1;
			while ($j < count ($TurnusPlan)){
			?>
			<div class="TurnusBox floatLeft">
				<div class="WeekLine floatDown">
					<div class="Vakt LHead floatLeft Axx<?php echo $TickTock;?>"> Uke <?php echo $w; ?> </div>
					<?php
					for ($n=0; $n < 7; $n++){
					$Vaktinfo =sjekkVaktType($TurnusPlan[$j]);
					?>
					<div class="Vakt Axx<?php echo $TickTock;?>"><?php echo $Vaktinfo[1] ?> </div>
					<?php
						$j++;
						}
					?>
				</div>
			</div>
				<?php
				$w++;
				if ($TickTock== 1) $TickTock = 2;
				else $TickTock = 1;
				$leder =  finnLeder($groupid[$ja], date("Y.m.d", time()), $Level = 3);
				$lederTurID =  finnLederturID($groupid[$ja], date("Y.m.d", time()), $Level = 3);
				
				}
				?>

			<div class="Assign AssignedPersonell floatLeft" ID="AssignedPersonell">
					<div class = "target Assigned floatDown LTail" id="<?php echo  $CurrentTourID[2] . '-L'; ?>">
						<?php $navn = finnAnsattNavn($leder); ?>
						<div class="dragdropTurAssign Assignable setEndDateAnsattTour" name="DragDrop" id="<?php echo $lederTurID; ?>">
						<?php 
							echo $navn[0];
							?>
						</div>
					</div>
				<?php
				for ($x=1; $x < $w; $x++){
				    $AnsID = SjekkTurEier($x, $groupid[$ja],$TimeVar);
					$navn = finnAnsattNavn($AnsID[1]);
					$endDate = "";
					if ( $AnsID[5] != "") $endDate = " (" . Date("d.m", strtotime($AnsID[5])) . ")";
				?>
					<div class = "target Assigned floatDown LTail" id="<?php echo  $CurrentTourID[2] . '-' .$x; ?>">
						<?php if ($AnsID[0] != ''){	?>
						<div class="dragdropTurAssign Assignable setEndDateAnsattTour" name="DragDrop" id="<?php echo $AnsID[0]; ?>">
						<?php 
							echo $navn[0] . $endDate;
							?>
						</div>
						<?php }	?>
					</div>
				<?php } ?>
			</div>
				<?php
			}
			?>
			
		</div>
		<div class="TurnusSpacer"></div>
		<div class="TurnusViewer NextTurnus">
			<div class="HeadLine">
				<div class="TurnusViewerHead floatLeft">
					Neste turnus for <?php if (SjekkAlias($groupid[$ja])) echo SjekkAlias($groupid[$ja])[0]; else echo hentAvdelingsnavn($groupid[$ja]); ?><br>
					<?php  if ($TurnusPlanNeste != "") echo "Fra: " . Date("d.m.y", strtotime($NextTourID[1])); ?>
					<?php  if ($TurnusPlanNeste == "") echo "Ingen ny turnus tilgjengelig" ; ?>
				</div>
			</div>
			<?php
			if ($TurnusPlanNeste != "") {
			$j = 0;
			$TickTock = 1;
			?>
			<div class="TurnusBox floatLeft">
				<div class="WeekLine floatDown">
					<div class="Vakt LeaderHead floatLeft Axx<?php echo $TickTock;?>">  </div>
				</div>
			</div>
				<?php
			if ($TickTock== 1) $TickTock = 2;
			else $TickTock = 1;$w = 1;
			while ($j < count ($TurnusPlanNeste)){
			?>
			<div class="TurnusBox floatLeft">
				<div class="WeekLine floatDown">
					<div class="Vakt LHead floatLeft Axx<?php echo $TickTock;?>"> Uke <?php echo $w; ?> </div>
					<?php
					for ($n=0; $n < 7; $n++){
					    $Vaktinfo =sjekkVaktType($TurnusPlanNeste[$j]);
					?>
					<div class="Vakt Axx<?php echo $TickTock;?>"><?php echo $Vaktinfo[1] ?> </div>
					<?php
						$j++;
						}
					?>
				</div>
			</div>
				<?php
				$w++;
				if ($TickTock== 1) $TickTock = 2;
				else $TickTock = 1;
				}
	//			echo $w;
			}
				?>
		</div>
		<div class="TurnusSpacer"></div>
			<div class="TurnusViewer UnassignedInfo">
					<div class="instHelp">
								* Personell listet i "uten tur" og  i fordelingslisten, starter tur neste dag.						
					</div>
					<div class="instHelp">
								* Personer kan justeres i turnus på to måter: 	<br>					
								** Trekk personens navn mellom "Ledige eller Planlagte" og en ledig turuke under "Fordeling" for å tilegne en tur, eller motsatt for å fjerne fra tur<br>
								** dobbeltklikk et navn under "Ledige eller Planlagte" for å kunne velge en ledig tur samt sette oppstartsdato, eller et navn under "Fordeling" for å sette en sluttdato.<br> 
					</div>
			</div>			

		<div class="setEndDateAnsattTourDialog" title="Sluttdato">
			<form action="vaktplan_files/Turnus_WriteDB_TurnusAdm.php" method="post" class="editForm"  autocomplete="off">
					<label> Sett sluttdato: </label></br>
					<input type="text" class="usertourEndDate">
					<br />Tøm, eller la være tomt, for ingen sluttdato<br />
					<input type="hidden" ID="userTourID">
					<input type="hidden" ID="userOffsetcode">
			</form>
		</div>

		<div class="setEndDateTourDialog" title="Sluttdato">
			<form action="vaktplan_files/Turnus_WriteDB_TurnusAdm.php" method="post" class="editForm"  autocomplete="off">
					<label> Sett sluttdato: </label></br>
					<input type="text" class="tourEndDate" tabindex="-1">
					<br />Tøm, eller la være tomt, for ingen sluttdato<br />
					<input type="hidden" ID="TourID">
			</form>
		</div>

		<div class="planStartDateAnsattTourDialog" title="Startdato">
			<form action="" method="post" class="editForm"  autocomplete="off">
					<select  class="usertourOffsetID" id="usertourOffsetID" onchange="singleSelectChangeValue()">
					</select></br>
					<label> Sett startdato: </label></br>
					<input type="text" class="usertourStartDate" id="usertourStartDate"></br>
					<input type="hidden" ID="assingTourID">
					<input type="hidden" ID="assingUID">
					<input type="hidden" ID="TourGroup">
			</form>
		</div>

		
		<div class="OpenNewTourDialog" title="Ny Turnus">
			<form action="vaktplan_files/Turnus_WriteDB_TurnusAdm.php" method="post" class="editForm"  autocomplete="off">
					<label> Turstart</label></br>
					<input type="text" class="TurStart">
					<br />Lim inn hele turnusplanen rett fra Excel:<br /><textarea id="Input" cols="55" rows="10"/> </textarea>
					<input type="hidden" ID="Ididit" value="<?php echo  $uid; ?>">
					<input type="hidden" ID="PoorSods" value="<?php echo  $groupid[$ja]; ?>">
			</form>
		</div>
		

		<div class="VacationTourDialog" title="Ferieavvik(må legges inn en gang pr bruker)">
			<form action="vaktplan_files/Turnus_WriteDB_TurnusAdm.php" method="post" class="editForm" autocomplete="off">
					<select ID="Operator">
						<option value="" > Velg Ansatt </option>
						<?php
					for($t=0; $t<count($ansatte);$t++){ 
						if ($ansatte[$t][0] != 20) {
								echo '<option value="' . $ansatte[$t][0] . '">' .  finnAnsattNavn($ansatte[$t][0])[0]  . '</option>';
							}
					}
						?>
					</select></br>
					Dato første avviksvakt:</br>
					<input type="text" class="FerieTurStart"></br>
					<br />Lim inn ferieavvik rett fra Excel:<br /><textarea id="fInput" cols="55" rows="10"/> </textarea>
					<input type="hidden" ID="OopsIdiditagain" value="<?php echo $uid; ?>">
					<input type="hidden" ID="iWorkHere" value="<?php echo $groupid[$ja]; ?>">
			</form>
		</div>
		
	</div>
	<?php } ?>
	<?php } ?>
</div>
	<?php } ?>


