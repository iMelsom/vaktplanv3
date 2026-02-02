<?php 
/*
 * Add personell to rota availability depending on your groups and rights. 
 */

//
//Funskjoner / pseduofunskjoner: 
// sjekkSuperbruker($uid) - har brukeren superbrukerrett? 
// GetGroups -> HentTilganger($uid) - hvilke grupper er brukeren med i? 
// Getusers(group) -> hentAnsatte2($AvdID)  !! Pass p� at en bruker ikke listes flere ganger!!
//                                                -> Brukere = array( [ID]=>[Navn]), da vil en brukerID kun lagres en gang (Skrives over hver gang)
// hentAvdelingsnavn($guid)
// Hent person-turnusmatrix (guid)
//




$SuperBruker = sjekkSuperbruker($uid);
$Groups = HentTilganger($uid); // henter alle grupper med ID over 3, alts� ikke varsling, New User, Admin eller Superbruker
$numGroups = count($Groups);
$availUSers = array();

?>

<?php 
for ( $n = 0; $n < $numGroups; $n++){
    if ($Groups[$n] != 4){
    $usersTemp = hentAnsatte2($Groups[$n]); //Alle ansatte
    $checkedUsers = CheckedLeaders($Groups[$n]); // Alle som er registrert som sjefer i aktuell gruppe
    //error_log(print_r($checkedUsers, true),0);
    
    if (is_Array($usersTemp)){
        for ($m = 0; $m <= count ($usersTemp); $m++){
        if (is_array($usersTemp[$m]) && $usersTemp[$m][1]!= "Udekte" ) {
            if (!$availUSers[$usersTemp[$m][0]]) $availUSers[$usersTemp[$m][0]] = array($usersTemp[$m][0], 
                                                                                        $usersTemp[$m][1], 
                                                                                        $usersTemp[$m][2], 
                                                                                        array(  $Groups[$n] =>array( $Groups[$n],
                                                                                                "inWork" => 1, 
                                                                                                "inTour" => 0))
                                                                                        );
             else {  
                $tempArray = array($Groups[$n] => $Groups[$n]); 
                $availUSers[$usersTemp[$m][0]][3][$Groups[$n]] =array( $Groups[$n], "inWork" => 1, "inTour" => 0);
            }
        }
    }
    }
    if (is_Array($checkedUsers)){
        for ($s=0; $s<count($checkedUsers); $s++){
        if ($availUSers[$checkedUsers[$s]] == ''){
            $needThatName = finnAnsattNavn($checkedUsers[$s]);
            $availUSers[$checkedUsers[$s]] = array($checkedUsers[$s],
                $needThatName[0],
                $needThatName[1],
                array(  $Groups[$n] =>array( $Groups[$n],
                    "inWork" => 0,
                    "inTour" => 1))
            );
            //error_log(print_r($availUSers[$checkedUsers[$s]], true),0);
        }
        $availUSers[$checkedUsers[$s]][3][$Groups[$n]]["inTour"]= 1;
    }

    }
}
//    error_log(print_R($availUSers, true),0);

?>
<script>
function handleClick(cb) {
//alert ("Click, new value = " + cb.value + " " + cb.name + " " + cb.checked);
	$.post("vaktplan_files/Turnus_WriteDB_PersonellToTour.php", 
			{ 
				CHECKED:cb.checked,
				tUID:cb.name,
				UID:<?php echo $uid;?>,
				GID:cb.value,
				LDR:1
			}
			);
	}

</script>
<div class="centerWrapper">

<div class="defTable White">
	<div class="defRow defCellDown">
		<div class="spacerDown defCell"> </div>
		<div class="TableTitle defTitle Yellow">Definer ledere i turnusen: </div> 
	</div>
	<div class="defCol">
		<div class="spacerDown defCellDown"> </div>
		<div class="invisiCellDown defCellDown White"></div> 
<?php foreach ($availUSers as $key => &$value){  ?>
		<div class="spacerDown defCellDown"> </div>
		<div class="defName <?php if ($urlmod == "mobile_") echo " mobName ";?>defCellDown Yellow"><?php  echo $value[1]; ?></div> 
<?php } ?>
	</div>
	
<?php 
for ( $o = 0; $o < $numGroups; $o++){
    if ($Groups[$o] != 4){
        ?>
	<div class="defCol spacer"></div>
	<div class="defCol">
		<div class="spacerDown defCellDown"> </div>
		<div class="Selector defCellDown Yellow "><?php echo hentAvdelingsnavn($Groups[$o]);?></div> 
<?php foreach ($availUSers as $key => &$value){  
    //$activeStatus = SjekkTurnusAktiv($value[0], $Groups[$o]);
 //   if ($key == 4) error_log(print_r($value, true), 0);
    
        ?>
		<div class="spacerDown defCellDown"> </div>
		<?php if ($value[3][$Groups[$o]][0] == $Groups[$o] || $value[3][$Groups[$o]]["inTour"] == 1){   ?>
		<div class="Selector defCellDown <?php if ($value[3][$Groups[$o]]["inWork"] == 0 && $value[3][$Groups[$o]]["inTour"] == 1) echo "Red";?>"><input type="checkbox"  
												 onclick='handleClick(this);' 
												 id="cb" 
												 name="<?php echo $value[0];?>" 
												 value="<?php echo $Groups[$o];?>" 
												 <?php if ($value[3][$Groups[$o]]["inTour"] == 1) echo "checked"?>></div> 
		<?php }
		if ($value[3][$Groups[$o]]["inWork"] == 0 && $value[3][$Groups[$o]]["inTour"] == 0){   ?>
		<div class="Selector defCellDown Black"></div> 
		<?php } ?>
<?php } ?>
	</div>
<?php } ?>
<?php } ?>
<?php } ?>

</div>
