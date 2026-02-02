<?php
if ($uid != ""){//block page usage WO login
    $groupid = $GLOBALS['UserPermissions'];
    for($ja=0; $ja< count($groupid); $ja++){
        if ($groupid[$ja] != 4){
            
            if (!$year || $year == '')  $year = date('Y', $TimeVar);
	$avviksLogg = hentAlleTurnusAvvik($year, $groupid[$ja]);
//	error_log("TESTDATA: " . print_r($avviksLogg, true), 0);
	
?>
<script>
function handleClick(cb) {
    var r = confirm("Rull tilbake endring?\r\nDette kan ikke angres.");
    if (r == true) {
    	$.post("vaktplan_files/Turnus_WriteDB_TurnusAdm.php", 
    			{ 
    				rollID:cb.value,
    				rollUID:cb.name,
    				UID:<?php echo $uid;?>,
    				UPDTP: 'RollBack'
    			}
   			);
		setTimeout(function(){
			location.reload(true);
			},2000);
	
   	}
}
</script>
<div class="ListAvvik Gray ">
<div class="Scrolltainer_vert">
	<div class="ScrollInternal ListAvvik">
    	<div class="FravRow">
    		<div class="YearArrow Yellow" ></div>
    			<div class="spacer"> </div>
    			<div class="frvTitle taWide Yellow">Turnusavvik for <?php echo hentAvdelingsnavn($groupid[$ja]); ?> i <?php echo $year; ?> </div> 
    			<div class="spacer"> </div>
    			<?php 
    			//if ($year != date('Y', $TimeVar)) printf('<div class="YearArrow Yellow" onClick="parent.location=`https://%s/index.php?Year=%s&tabnr=%s`" >%s --></div>',$GLOBALS['MineVakterURL'], $year+1, $tabNR, $year+1); 
    			/*if ($year == date('Y', $TimeVar))*/ printf('<div class="YearArrow GrayOut"></div>'); 
    			?>
   		</div>
    	<div class="FravRow">
        	<div class="fWho Yellow">Person</div> 
        	<div class="spacer"> </div>
        	<div class="fDate Yellow">Startdato</div> 
        	<div class="spacer"> </div>
        	<div class="fData Yellow">Fra excelark (UE = Uendret vakt fra original turnus)</div> 
        	<div class="spacer"> </div>
	    	<div class="chButton Yellow">Rollback*:</div>
    	</div>
    	<?php 
    	$dataFound = false;
    		for($n=0; $avviksLogg[$n] != ''; $n++ ) {
    		    
    		    $ThingsToRemove = array("Turnusavvik=", "\"", "User=","Group=", "StartDate=", "&#10;", "\\");
    		        		    
    		    $tempCleaned = str_replace( "; &", ";&", $avviksLogg[$n][1]);//Old logstyle compat
    		    
    		    $tempCleaned = str_replace( " ,",",", $tempCleaned);
    		    $tempAvvik = explode(" " , $tempCleaned);
    		    //error_log("PreTest: " . print_r($temptest, true),0);
    		    //error_log("TestTest: " . print_r($TestTest, true),0);

    		    /*
    		    $tempData = str_replace( ";&", ";UE&", $tempAvvik[0]);
    		    $tempData = preg_replace("/^&#9;/", "UE&#9;", $tempData);
    		    $tempData = str_replace("&#9;", ",", $tempData);
    		    $tempData = preg_replace("/,\z/", "",$tempData);
    		    */
    		    
    		    $tempData = str_replace($ThingsToRemove, "", $tempAvvik[0]);
    		    $TempDataArray = explode("," , $tempData);
//    		    error_log("TestTest: " . print_r($TempDataArray, true),0);
    		    
    		    $tempData ="";
    		    for($i=0;$i < count ($TempDataArray); $i++){
    		        if ($TempDataArray[$i] == "")$TempDataArray[$i] = "UE";
    		        if ($tempData != "") $tempData = $tempData . ",";
    		        $tempData = $tempData . $TempDataArray[$i];
    		    }
    		    
    		    $tempUser = str_replace($ThingsToRemove, "", $tempAvvik[1]);
				$tempDate = str_replace($ThingsToRemove, "", $tempAvvik[2]);
				$dataFound  = True;
		 ?>
		<div class="FravRow">
			<div class="fWho White"><?php echo finnAnsattNavn($tempUser)[0]?></div> 
    		<div class="spacer"> </div>
    		<div class="fDate White"><?php echo strftime("%e %b", strtotime($tempDate)) ?></div> 
    		<div class="spacer"> </div>
    		<div class="fData White"><?php echo $tempData ?></div> 
    		<div class="spacer"> </div>
    		<div class="chButton White">
    		<?php 
    		if(date("Y-m-d", $TimeVar) == $avviksLogg[$n][2] || date("Y-m-d", $TimeVar) == date("Y-m-d", strtotime($avviksLogg[$n][2] . " +1 day"))){  ?>
    			<input type="checkbox" onclick='handleClick(this);' id="cb" name="<?php  echo $tempUser;?>" value="<?php  echo $avviksLogg[$n][0] ;?>">
    		<?php  } else echo "Ikke Mulig";?> 
			</div>
    	</div>
  		<?php  } if ($dataFound == False){?>
    		<div class="FravRow">
        		<div class="fravText White">Det er ikke registrert avvik i turnusen for <?php echo $year; ?> </div>
        	</div>
    	<?php } ?>
	</div>
</div>
	
</div>
<div class="FravRow">
	<div class="instHelp">
		*Rollback kun mulig samme og neste dag som endring ble registrtet.						
    </div>
</div>	<?php } ?>
	<?php } ?>
		
<?php } ?>