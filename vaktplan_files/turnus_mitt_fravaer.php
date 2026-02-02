<?php
if ($uid != ""){ //block page usage WO login
//error_log($tabNR, 0);

   // Add selector for selecting user. Superusers may edit its groups users leave-registrations
   $tempUID = $uid; //saved for resetting at end. 
   $selectedUID = $_REQUEST['selUID'];
   $year = $_REQUEST['Year'];
   $tilganger = HentTilganger($uid); //Get user permissions to pages. 
   
   //error_log ("mittfravær 11: " . $year);
   
   if ($selectedUID != '' && $selectedUID != $uid)$uid = $selectedUID;
   //error_log ("mittfravær 14: " .$uid, 0);
   
   if ($ElevatedAccess == 1){
       ?>
       <form action="#"  method="post">
           <div class="InnstillingBoks">
 	          <div class="instHeader Yellow">Hvem vil du sjekke frav&aelig;r for? </div>
               <div class="instInput ">
        	       <input type="hidden" id="page" value="mittfrav" />
                   <select name="selUID" id="selUID" onchange="this.form.submit()" class="instSelector">
                   <?php for ($blipp = 0;$blipp < count($tilganger);$blipp++){
                       //error_log("New: " .$tilganger[$blipp]);
                       
                       if ($tilganger[$blipp]!= 13 &&  count($tilganger) == 2)  $guid == $tilganger[$blipp];
                       if ($tilganger[$blipp]!= 13){
                           $ansatte = hentAnsatte2($tilganger[$blipp]);
                           for ($blippuru = 0;$blippuru < count($ansatte);$blippuru++){?>
                             <option value="<?php echo $ansatte[$blippuru][0] ?>" <?php if ($ansatte[$blippuru][0] == $uid) echo " selected"?>><?php echo $ansatte[$blippuru][1] ?></option>
                      <?php }}}?>
                    </select>
                </div>
	            <div class="instHelp">Som standard vises eget frav&aelig;r. </div>
            </div>
       </form>
   <?php  } 
   
   if ($guid == "") $guid = 0;
   if (!$year || $year == '')  $year = date('Y', $TimeVar);
   //error_log ("mittfravær 38: " . $year);
   
$kjentFrav = hentFrav($uid, $year, true );
//error_log ("mittfravær 41: " . print_r($kjentFrav, true),0);



?>
			<div class="fravaer_wrapper">
				<div class="ListFrav Gray">
					<div class="FravRow">
						<div class="YearArrow Yellow" onClick="parent.location=`https://<?php echo $GLOBALS['MineVakterURL'] ?>?page=mittfrav&Year=<?php echo $year-1 ?>`"><-- <?php echo $year-1; ?></div> 
						<div class="spacer"> </div>
						<div class="frvTitle Yellow">Fravær for <?php echo $year; ?> </div> 
						<div class="spacer"> </div>
						<?php 
						if ($year != date('Y', $TimeVar)) printf('<div class="YearArrow Yellow" onClick="parent.location=`https://%s/index.php?page=mittfrav&Year=%s`" >%s --></div>',$GLOBALS['MineVakterURL'], $year+1, $tabNR, $year+1); 
						if ($year == date('Y', $TimeVar)) printf('<div class="YearArrow GrayOut">%s --></div>', $year+1); 
						?>
					</div>
					<div class="FravRow">
						<div class="Reason Yellow">Fraværsgrunn</div> 
						<div class="spacer"> </div>
						<div class="fravDate Yellow">Fra</div> 
						<div class="spacer"> </div>
						<div class="fravDate Yellow">Til</div> 
						<div class="spacer"> </div>
						<div class="NumDays Yellow">#Dager</div> 
						<div class="spacer"> </div>
						<div class="chButton Yellow">
							<a class="ui-button ui-widget ui-corner-all OpenEdit" href="#">
								<input type="hidden" ID="uID" value="<?php echo  $uid ?>">
								<input type="hidden" ID="GUID_frv" value="<?php echo $guid ?>">						
								<input type="hidden" ID="sendMail" value="quiet">						
								<input type="hidden" ID="ShowValidCheckbox" value="0">		
								<input type="hidden" ID="ElevatedAcc" value="<?php echo $ElevatedAccess ?>">	
							Registrer</a>
						</div>
					</div>
					<?php
					$VakterFordelt = 0;
					$dataFound = false;
					for($n=0; $kjentFrav[$n] != ''; $n++ ) { 
					$dataFound  = True;
//					function hentTildelinger($UID, $Dato, $GrID = "all"){
					$Tildelinger = hentTildelinger($uid, $kjentFrav[$n][3]); //Hent alle tildelte turer
					for ($a=0; strtotime($kjentFrav[$n][3] . " +".$a." day") <= strtotime($kjentFrav[$n][4])&& $Tildelinger [0]!= "NoTour"; $a++){
					    $AktivNixDato = strtotime($kjentFrav[$n][3] . " +".$a." day");
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
					                //error_log("TurnusMittFravær 103: BLIPP " . $VakterFordelt);
					                //Erstattes med databaseaktivitet
					                $Test = sjekkVaktFlytt($Vakt[0], $AktivNixDato);
//					                error_log(print_r($Test, true),0);
					                if(is_array($Test))$VakterFordelt = 1;
					            }
					        }
					    }
					}
					?>
					<div class="FravRow">
						<div class="Reason White"><?php 
						if (!mb_check_encoding(oversettFraversGrunn($kjentFrav[$n][2])[1], 'UTF-8'))echo utf8_encode(oversettFraversGrunn($kjentFrav[$n][2])[1]);
						else echo oversettFraversGrunn($kjentFrav[$n][2])[1]; 
						?></div> 
						<div class="spacer"> </div>
						<div class="fravDate White"><?php echo strftime("%e %b", strtotime($kjentFrav[$n][3])) ?></div> 
						<div class="spacer"> </div>
						<div class="fravDate White"><?php echo strftime("%e %b", strtotime($kjentFrav[$n][4])) ?></div> 
						<div class="spacer"> </div>
						<div class="NumDays White"><?php echo  beregnDager($kjentFrav[$n][3], $kjentFrav[$n][4]) ?> </div> 
						<div class="spacer"> </div>
						<div class="chButton White">
							<a class="ui-button ui-widget ui-corner-all OpenEdit" href="#">
								<input type="hidden" ID="FravID" value="<?php echo  $kjentFrav[$n][0] ?>">
								<input type="hidden" ID="FravType" value="<?php echo    $kjentFrav[$n][2] ?>">
								<input type="hidden" ID="uID" value="<?php echo  $uid ?>">						
								<input type="hidden" ID="sendMail" value="quiet">						
								<input type="hidden" ID="fraDate" value="<?php echo  $kjentFrav[$n][3] ?>">	
								<input type="hidden" ID="toDate" value="<?php echo  $kjentFrav[$n][4] ?>">		
								<input type="hidden" ID="Description" value="<?php echo  $kjentFrav[$n][8] ?>">		
								<input type="hidden" ID="ShowValidCheckbox" value="1">		
								<input type="hidden" ID="assignedXVCheckbox" value="<?php echo $VakterFordelt ?>">	
								<input type="hidden" ID="ElevatedAcc" value="<?php echo $ElevatedAccess ?>">	
								<input type="hidden" ID="GUID_frv" value="<?php echo $guid ?>">						
									
								Oppdater</a>								
						</div>
					</div>


					
					<?php }
					if ($dataFound == False){ 
					?>
					<div class="FravRow">
						<div class="fravText White">Det er ikke registrert fravær i år</div>
					</div>
					<?php } 
					if ($dataFound == True){ //stil in dev!
					?>
					<div class="FravRow">
						<div class="fravText White">* Antall fraværsdager er estimert</div> 
					</div>
					<?php } ?>
				</div>

			</div>
						<div class="RegFrav" title="Fraværsredigering">
				<form action="/users/Turnus_WriteDB_frav.php" method="post" class="editForm" autocomplete="off">
					<select ID="FravSelect">
						<option value="" > Type Fravær </option>
						<?php
							$fravType = hentFravTyper(NULL);
							for($t=0; $fravType[$t]!= '' ;$t++){
							    if ($fravType[$t][4] != 2 || ($fravType[$t][4] == 2 && $ElevatedAccess == 1)) echo '<option value="' . $fravType[$t][0] . '">' .  $fravType[$t][1]  . '</option>';
							}
						?>
					</select></br>
					Merknad:<br /><textarea id="Merknad" cols="30" rows="2"/></textarea><br />
					<input type="text" id="datoFra"><label> Start</label>
					<input type="text" id="datoTil"><label> Slutt</label>
					<div id="ValidCheckbox"><input type="checkbox" name="ValidTest" id ="ValidTest" value="ValidTest" checked/>
					<label> Fjern kryss for å slette </label></div>
					<div id="resetXvakt"><input type="checkbox" name="ResetXVTest" id ="ResetXVTest" value="ResetXVTest"/>
					<label> Resett vaktendringer</label>
					<label class="dialogTXT"> Må kombineres med å fjerne kryss for å slette fravær</label></br>
					<label class="dialogTXT"> MERK: Dette kan ikke reverseres!</label></div>
					<input type="hidden" name="UID" value="<?php echo  $uid ?>" />
					<input type="hidden" name="GUID" value="" />
					<input type="hidden" name="fravID" value="" />
					<input type="hidden" name="sendMail" value="" />
				</form>
			</div>
		<?php
	  $uid = $tempUID;
}?>