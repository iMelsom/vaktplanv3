
		<?php 
if ($uid != ""){ //block page usage WO login
//$guid = 6;

	$groupid = $GLOBALS['UserPermissions'];
//	error_log(print_r($groupid, true), 0);
?>
<div class="AdminWrapper">
<?php
for($i=0; $i<count($groupid);$i++){
    if ($groupid[$i] != 4){
        $Turnus = finnGjeldendeTurnus($TimeVar, $groupid[$i]);
$TurnusPlan = hentTurnus($Turnus[0]);
$Ansatte = TurnusFordeling($Turnus[0], $TimeVar);
$TickTock = 1;

?>


	<div class="ListVaktTyper Gray">
		<div class="VaktRow">
			<div class="vHead Yellow">Vakter som brukes av <?php echo hentAvdelingsnavn($groupid[$i]); ?></div> 
		</div>
		<div class="VaktRow">
			<div class="vCode Yellow">Vaktkode</div> 
			<div class="spacer"> </div>
			<div class="vStart Yellow">Start</div> 
			<div class="spacer"> </div>
			<div class="vEnd Yellow">Slutt</div> 
			<div class="spacer"> </div>
			<div class="vLen Yellow">Lengde</div> 
			<div class="spacer"> </div>
			<div class="vStart Yellow">Oppmøte Start</div> 
			<div class="spacer"> </div>
			<div class="vEnd Yellow">Oppmøte Slutt</div> 
			<div class="spacer"> </div>
			<div class="vLen Yellow">Oppmøte Lengde</div> 
			<div class="spacer"> </div>
			<div class="vDescr Yellow">Beskrivelse</div> 
			<div class="spacer"> </div>
			<div class="vLoc Yellow">Oppmøtested</div> 
			<div class="spacer"> </div>
			<div class="vNewButton Yellow">
				<a class="ui-button ui-widget ui-corner-all OpenNewShift admWidth" href="#">
					<input type="hidden" ID="GroupID" value="<?php echo $groupid[$i]; ?>">
					Ny vakt
				</a>
			</div>
		</div>
		<?php
		$Vakter =  hentAlleVaktTyper($groupid[$i]);
		//print_r ($vakter);
		for ($v = 0; $v < count ($Vakter); $v++){
		?>
		<div class="VaktRow">
			<div class="vCode White"><?php echo $Vakter[$v][1];?></div>
			<div class="spacer"> </div>
			<div class="vStart White"><?php echo $Vakter[$v][3]; ?></div> 
			<div class="spacer"> </div>
			<div class="vEnd White"><?php echo $Vakter[$v][4]; ?></div> 
			<div class="spacer"> </div>
			<div class="vLen White"><?php echo $Vakter[$v][6]; ?></div> 
			<div class="spacer"> </div>
			<div class="vStart White"><?php echo $Vakter[$v][9]; ?></div> 
			<div class="spacer"> </div>
			<div class="vEnd White"><?php echo $Vakter[$v][10]; ?></div> 
			<div class="spacer"> </div>
			<div class="vLen White"><?php echo $Vakter[$v][8]; ?></div> 
			<div class="spacer"> </div>
			<div class="vDescr White"><?php echo $Vakter[$v][5]; ?></div> 
			<div class="spacer"> </div>
			<div class="vLoc White"><?php echo  $Vakter[$v][2]; ?> </div> 
			<div class="spacer"> </div>
			<div class="vNewButton White">
			<?php  if ($Vakter[$v][7] == 0) echo "<!--!";?>
				<a class="ui-button ui-widget ui-corner-all OpenNewShift admWidth" href="#">
					<input type="hidden" ID="vID" value="<?php echo $Vakter[$v][0]; ?>">
					<input type="hidden" ID="vKode" value="<?php echo $Vakter[$v][1]; ?>">
					<input type="hidden" ID="vStart" value="<?php echo $Vakter[$v][3]; ?>">
					<input type="hidden" ID="vLen" value="<?php echo  $Vakter[$v][6]; ?>">						
					<input type="hidden" ID="vOppmStart" value="<?php echo $Vakter[$v][9]; ?>">
					<input type="hidden" ID="vOppmLen" value="<?php echo  $Vakter[$v][8]; ?>">						
					<input type="hidden" ID="vInfo" value="<?php echo $Vakter[$v][5]; ?>">	
					<input type="hidden" ID="selectedOppmoteSted" value="<?php echo  $Vakter[$v][2]; ?>">
					Oppdater</a>
			<?php if ($Vakter[$v][7] == 0) echo "-->";?>										
				</div>
			</div>
		<?php
		}
		?>
		</div>
		<?php }}?>
	</div>

				<div class="OpenNewShiftDialog" title="Ny Vakttype">
		<form action="/vaktplan_files/Turnus_WriteDB_TurnusAdm.php" method="post" class="editForm" autocomplete="off">
				<input type="text" id="vKde" required><label> Vaktkode</label><br />
				<input type="text" id="vStrt" required><label> Start-tid</label><br />
				<input type="text" id="vLengde" required><label> Varighet (timer)</label><br />
				<input type="text" id="vOppmStrt"><label> Oppmøte-tid</label><br />
				<input type="text" id="vOppmLengde"><label> Oppmøte Varighet (timer)</label><br />
				<input type="text" id="vDesc" required><label> Beskrivelse </label><br />
				<label> Oppmøte hos Ada ved vaktstart?</label>
				<input type="radio" name="vSted" value="Ada"> Ja
				<input type="radio" name="vSted" value="Hjemme"> Nei
				<input type="hidden" ID="vID" value="">
				<input type="hidden" ID="uid" value="<?php echo  $uid; ?>">
				<input type="hidden" ID="PoorSods" value="<?php echo $groupid[$i]; ?>">
		</form>
	</div>

<?php }?>