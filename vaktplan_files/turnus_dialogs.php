 <?php
    if ($uid != ""){ //block page usage WO login
?>

	<div class="VaktEdit" title="Vaktredigering">
		<form action="Turnus_WriteDB.php" method="post" class="editForm"  autocomplete="off">
			<label> <div name="printHeader"></div> </label></br>
			<label class="dialogTXT" > Jeg &oslash;nsker &aring;: </label></br>
			<input type="radio" name="vaktChangeDecr" value="Bytte" checked /><label class="dialogTXT"> bytte bort vakten </label></br>
			<input type="radio" name="vaktChangeDecr" value="Flytte" /><label class="dialogTXT"> flytte vakten </label></br>
			<?php if ($ElevatedAccess == 1){ ?>
					<select ID="endreVaktType">
						<option value="" > Endre vakttype </option>
						<?php
						$vaktType = hentAlleVaktTyper($guid);
						for($t=0; $vaktType[$t]!= '' ;$t++){
						    if ($vaktType[$t][1] != 'X' && $vaktType[$t][1] != 'T' && $vaktType[$t][1] != 'O')echo '<option value="' . $vaktType[$t][0] . '">' .  utf8_encode ($vaktType[$t][1])  . '</option>';
							}
						?>
					</select></br>
					Merknad:<br /><textarea id="Merknad2" cols="30" rows="2"/></textarea>
			<?php } ?>
			<?php
				$ansatte = HentTurnusAktive($guid); 
			?>
			<select ID="vaktTo">
				<option value="" > Velg operat&oslash;r </option>
				<?php
			for($t=0; $t<count($ansatte);$t++){ 
				//if ($ansatte[$t][0] != 20) {
			    echo '<option value="' . $ansatte[$t][0] . '">' .  $ansatte[$t][1]  . '</option>';
					//}
			}
				?>
				<option value="20"> Udekket </option> <!--  Udekket må være med i lista, men trengs ikke så ofte ellers -->
			</select></br>
			<input type="checkbox" name="mailNotif4" id ="mailNotif4" value="mailNotif4" checked /><label class="dialogTXT"> Varsle mottaker pr epost </label></br>
			<input type="hidden" name="FUID" value="" />
			<input type="hidden" name="UID" value="" />
			<input type="hidden" name="GUID" value="" />
		</form>
	</div>
	
	<?php if ($ElevatedAccess == 0){ ?><div class="RegSykd" title="Sykdomsregistrering"> <?php } ?>
	<?php if ($ElevatedAccess == 1){ ?><div class="RegSykd" title="Frav&aelig;rs/Endringsregistering"> <?php } ?>
	
		<form action="Turnus_WriteDB_frav.php" method="post" class="editForm" autocomplete="off">
			<label> <div name="printHeader"></div> </label></br>
			<?php if ($ElevatedAccess == 1){ ?>
					<select ID="FravType">
						<option value="" > Type Fravær </option>
						<?php
							$fravType = hentFravTyper(NULL);
							for($t=0; $fravType[$t]!= '' ;$t++){
								echo '<option value="' . $fravType[$t][0] . '">' .  $fravType[$t][1]  . '</option>';
							}
						?>
					</select></br>
					Merknad:<br /><textarea id="Merknad3" cols="30" rows="2"/></textarea><br />
			<?php } ?>
			<?php if ($ElevatedAccess == 0){ ?>
					<select ID="FravType">
						<option value="" > Type Fravær</option>
						<?php
							$fravType = hentFravTyper(NULL);
							for($t=1; $fravType[$t]!= '' ;$t++){
							    //error_log(print_r($fravType[$t], true), 0);
							    if ($fravType[$t][4] == 0) echo '<option value="' . $fravType[$t][0] . '">' .  $fravType[$t][1]  . '</option>';
							}
						?>
					</select></br>
					Merknad:<br /><textarea id="Merknad2" cols="30" rows="2"/></textarea>
			<?php } ?>
			<input type="text" id="start"><label> Start</label>
			<input type="text" id="slutt"><label> Slutt</label>
			<?php if ($ElevatedAccess == 1) {?>
			<div id="ValidChecker"><input type="checkbox" name="ValidTest" id ="ValidTest" value="ValidTest" checked/>
			<label> Fjern kryss for å slette </label></div>
			<div id="resetXvakt"><input type="checkbox" name="ResetXVTest" id ="ResetXVTest" value="ResetXVTest"/>
			<label> Resett vaktendringer</label></div>
				<label class="dialogTXT"> Må kombineres med å fjerne kryss for å slette fravær</label></br>
				<label class="dialogTXT"> MERK: Dette kan ikke reverseres!</label></br>
			</br><input type="checkbox" name="sendMail" id ="sendMail"/>
				<label class="dialogTXT"> Send epostvarsel til ansatt</label></br>
				<label class="dialogTXT"> Velges "Syk" sendes mail automatisk til Ansatt, Gruppeleder og Turnusansvarlig</label></br>
			<?php } ?>
			<input type="hidden" name="UID" value="" />
			<input type="hidden" name="GUID" value="" />
			<input type="hidden" name="AnsID" id ="AnsID" value="" />
			<input type="hidden" name="FID" value="" />
			<input type="hidden" name="mailNotif" value="send" />
		</form>
	</div>


 <?php
}
?>
