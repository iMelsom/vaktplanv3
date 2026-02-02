<?php
if ($uid != ""){ //block page usage WO login
    //$guid = 4;
    //$uid = 6;
    $turnusID =  '';
	
	$guid = HentTilganger($uid);
	//error_log(print_r($guid,true),0);
	
    $turnus = array();
    $n = 0;
    $chosenDate = isset($_REQUEST['date'])? $_REQUEST['date'] : time();
    
    function getTurnusData($uid, $guid, $nixtime){
        $day = hvilkenDagErDetIdag($nixtime, 1, "Fixed");
        $turnus["turnusData"] = finnGjeldendeTurnus($day[1],$guid);
        $turnus["turnustur"] = turnusInfo2($uid,$day[1], $guid);
        $turnus["turnusContent"] = hentTurnus( $turnus["turnusData"][0]);
        $turnus["turnusLengde"] = count($turnus["turnusContent"]);
        $turnus["turnusTurAlderiDager"] = DagerSiden($turnus["turnusData"][1], $day[1]);
        return $turnus;
    }

	foreach ($guid as $value) {
	        $turnus[$value] = getTurnusData($uid, $value, $chosenDate);
	}
	
	$definedUserVariables = chkUserVariables($uid);
	if ($definedUserVariables[5] != '' || $definedUserVariables[5] != false)$emailMottager =  $definedUserVariables[5];
	if ($definedUserVariables[5] == '' || $definedUserVariables[5] == false)$emailMottager = hentEpostAdresse($uid)[0];

	$toDate = date("Y-m-d", strtotime(date("Y-m-d", $chosenDate)." + 32 days"));
	$altfraver = hentFraversliste(date("Y-m-d", hvilkenDagErDetIdag($chosenDate, 1, "Fixed")[1]),$toDate );
	$alleendringer = hentVaktendringer(date("Y-m-d",hvilkenDagErDetIdag($chosenDate, 1, "Fixed")[1]),$toDate );
	
	

?>

			<div class="Month">
				<div class="Month_head">
					<div class="Week_head">
						<div class="Day_empty">
							<a class="href_adjust" href="http://<?php echo $GLOBALS['MineVakterURL']; ?>/index.php?page=minkal&date=<?php echo strtotime('-1 Month', $chosenDate) ?>"><</a>
						</div>
						<?php
						$k = 0;
						for($i = 1; $i <= 7; $i++){
								$day = hvilkenDagErDetIdag($chosenDate, $i, "Fixed");
								if ($i == 6 || $i == 7 || testForHoliday($day[1])) $dayType = "Holiday";
								else $dayType = "NormalDay";
								?>
								<div class="WeekDay_head<?php if ($i ==7) echo " Sunday"; ?>">
									<div class="Head <?php echo $dayType; ?>">
										<?php echo $day[0] ."\n";?>
									</div>
								</div>
								<?php
							}
						?>
						<div class="Day_empty">
							<a class="href_adjust" href="http://<?php echo $GLOBALS['MineVakterURL']; ?>/index.php?page=minkal&date=<?php echo  strtotime('+1 Month', $chosenDate) ?>">></a>
						</div>

						
					</div>
					<?php 
					$tempdate= $chosenDate;
					for($i = 1; $i <= 5; $i++){
					
							?>
							<div class="Week">
								<div class="WeekNR">
									<div class="Info_nr">
									<?php echo date('W', $tempdate); ?>
									</div>
								</div>
								<?php 
								for($j = 1; $j <= 7; $j++){
									$day = hvilkenDagErDetIdag($tempdate, $j, "Fixed");
									if ($j == 6 || $j == 7 || testForHoliday($day[1])) $dayType = "Holiday";
									else $dayType = "NormalDay";
									?>

									<div class="WeekDay"> 
										<div class="Head <?php echo $dayType; ?>  <?php if (isToday($day[1])) echo "Today"; ?>> ">
											<?php echo date('d.m',$day[1]) ."\n";?>
										</div>
										<div class="Info <?php if (isToday($day[1])) echo "Today"; ?> ">
											<?php 
											foreach ($guid as $value) {
											    if( isset($turnus[$value]["turnustur"][3]) && $turnus[$value]["turnustur"][3] != ""){
											        if ($turnus[$value]["turnusData"][4]  != "" && $turnus[$value]["turnusData"][4] < $day[1]){
											            $turnus[$value] = getTurnusData($uid, $value, $day[1]);
											            $k = 0;
											        }
											        
        											$turnus[$value]["turnusOffsetCurrent"] = ($turnus[$value]["turnustur"][3]*7) - 7 + $turnus[$value]["turnusTurAlderiDager"]+$k;
        											$turnus[$value]["turnusPosCurrent"] = Turnus_Pos($turnus[$value]["turnusLengde"],$turnus[$value]["turnusOffsetCurrent"]);
        											
        											$Curr_Vakt = $turnus[$value]["turnusContent"][$turnus[$value]["turnusPosCurrent"]];
        											$Vakt = sjekkVaktType($Curr_Vakt);
        											
        											$fraver = sjekkFraver2($altfraver[$uid],  $day[1]);
                									if (is_array($fraver)) $fraverBeskrivelse = oversettFraversGrunn($fraver[3]);
                											
                											else{
                											    if ($Vakt[0] != 39)echo $Vakt[5] . "<br>"; //Ikke vis "ikke på vakt"
        											         }
											     }
											 }
											 if (is_array($fraver)) echo $fraverBeskrivelse[1];
        									   foreach ($alleendringer[date("Y-m-d", $day[1])] as &$value) {
        										  if ($value[2] == $uid){
        										     //Sjekk at vakten ikke har blitt flyttet videre til en annen person
        										     if (fortsattGyldig($value[4], $value[1], $uid, $day[1])){
        											            error_log(print_r($value,true),0);
        											            $ekstravakt_beskrivelse = sjekkVaktType($value[1]);
        											            $Printvakt =   $ekstravakt_beskrivelse[5];
        											            
        											            if ($value[2] == 'Bytte'){
        											                $Printvakt =   $Printvakt .  ' (Byttevakt)';
        											            }
        											            else{
        											                $Printvakt =   $Printvakt .  ' (Ekstravakt)';
        											            }
        											            
        											            echo $Printvakt . "<br>";
        											        }
        											    }
    											     }
											
											?>
										</div>
									</div>
									<?php
									$k++;
								
								}
								$tempdate = strtotime("+7 day", $tempdate);
							
							?>
							</div>
							<?php
						}
					?>
				</div>
				
			</div>
		<?php
}
?>