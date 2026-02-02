<?php
//require_once 'vaktplan_files/init.php';
//require_once 'includes/turnus_DBinfo.php';
//require_once 'includes/turnus_functions.php';
$defaultEmail = hentEpostAdresse($uid);
$checkValue = $definedUserVariables[8];
if ($checkValue == '' ) $checkValue= 0;
//error_log (print_r($avd, 1),0);
//require_once '../users/init.php';
//if (!securePage($_SERVER['PHP_SELF'])) {
//    die();
//}

//error_log($definedUserVariables[5], 0);
$hooks = getMyHooks();
if ($hooks['bottom'] == []) {
    $resize = [];
} else {
    $resize = [];
}
includeHook($hooks, 'pre');
if (!empty($_POST['uncloak'])) {
    logger($user->data()->id, 'Cloaking', 'Attempting Uncloak');
    if (isset($_SESSION['cloak_to'])) {
        $to = $_SESSION['cloak_to'];
        $from = $_SESSION['cloak_from'];
        unset($_SESSION['cloak_to']);
        $_SESSION[Config::get('session/session_name')] = $_SESSION['cloak_from'];
        unset($_SESSION['cloak_from']);
        logger($from, 'Cloaking', 'uncloaked from ' . $to);
        $cloakHook =  getMyHooks(['page' => 'cloakEnd']);
        includeHook($cloakHook, 'body');
        usSuccess("You are now you");
        Redirect::to($us_url_root . 'users/admin.php?view=users');
    } else {
        usError("Something went wrong. Please login again");
        Redirect::to($us_url_root . 'users/logout.php');
    }
}

$grav = fetchProfilePicture($user->data()->id);
$raw = date_parse($user->data()->join_date);
$signupdate = $raw['month'] . '/' . $raw['year'];
if ($hooks['bottom'] == []) { //no plugin hooks present
    $resize = [
        'cardClass' => 'col-md-6 offset-md-3',
        'nameSize' => 'style="font-size:3em;"',
        'sinceSize' => 'style="font-size:2.25em;"',
    ];
} else {
    $resize = [
        'cardClass' => 'col-md-3',
        'nameSize' => '',
        'sinceSize' => '',
    ];
}?>
<script>
function handleClick(cb) {
}

$(function() {
	  $('input[name="daterange"]').daterangepicker({
		    "locale": {
		        "format": "DD/MM/YYYY",
		        "separator": " til ",
		        "applyLabel": "Velg",
		        "cancelLabel": "Avbryt",
		        "fromLabel": "Fra",
		        "toLabel": "Til",
		        "customRangeLabel": "Custom",
		        "weekLabel": "U",
		        "daysOfWeek": [
		            "Sø",
		            "Ma",
		            "Ti",
		            "On",
		            "To",
		            "Fr",
		            "Lø"
		        ],
		        "monthNames": [
		            "Januar",
		            "Februar",
		            "Mars",
		            "April",
		            "Mai",
		            "Juni",
		            "Juli",
		            "August",
		            "September",
		            "Oktober",
		            "November",
		            "Desember"
		        ],
		        "firstDay": 1
		    },
	    opens: 'left'
	  }, function(start, end, label) {
		    var r = confirm("Send kalenderfil til lagret epostadresse?");
		    if (r == true) {
	    	$.post("vaktplan_files/turnus2ical.php", 
	    			{ 
	    				STARTDT:start.format('YYYY-MM-DD'),
	    				ENDDT:end.format('YYYY-MM-DD'),
	    				UID:<?php echo $uid;?>
	    			},
              function(resp){   
                        $("#sendResults")
                        	.append(resp)
                        	.animate({
	                  		  opacity: 1
	                		})
                        	;    
                    }
	   			);
	    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
	  	}
	  }
	  );
	});
// Fade out the note after 5 seconds
setTimeout(function ()
{
    $('#sendResults').animate({
        opacity: 0
    }, 250);
}, 20000);

</script>

    <div class="instCol">
      <div class="row">
          <div class="card p-4 alternate-background" style="width:100%">
            <div class="image text-center">
              <img src="<?= $grav; ?>" width="60%" alt="profile thumbnail" class="profile-replacer">
              <p class="mt-3" <?= $resize['nameSize'] ?>><span id="fname" class="font-weight-bold fw-bold"><?= $user->data()->fname . ' ' . $user->data()->lname; ?> </span>
                <br />
                <span class="idd">@<?= $user->data()->username ?></span>
              </p>
              <p><a href="<?= $us_url_root ?>users/user_settings.php" class="btn btn-primary btn-block mt-3"><?= lang('ACCT_EDIT'); ?></a></p>
    
              <?php if (isset($_SESSION['cloak_to'])) { ?>
                <p>
                <form class="" action="" method="post">
                  <input type="hidden" name="uncloak" value="Uncloak!">
                  <button class="btn btn-danger btn-block" role="submit">Uncloak</button>
                </form>
                </p>
              <?php  } //end cloak button 
              ?>
              <?php includeHook($hooks, 'body'); ?>
              <div class="px-2 rounded mt-2" <?= $resize['sinceSize'] ?>><span class="join small"><?= lang('ACCT_SINCE'); ?>: <?= $signupdate; ?></span> </div>
            </div>
    
          </div>
      </div>
    </div>
   <div class="instContainer">
    <div class="instCol">
        <form>
        	<div class="InnstillingBoks">
        		<div class="instHeader Yellow">
        			Send epost til følgende konto: 
        		</div>
        		<div class="instInput">
        			<input type="hidden" id="instUID" value="<?php echo $uid?>" />
        			<input 	type="text" 
        					name="autosave-forms-altemail" 
        					value="<?php  if ($definedUserVariables[5] != '') echo $definedUserVariables[5]; ?>" 
        					placeholder="Alternativ email" 
        					onkeyup="saveEmailData()" 
        					id="instEmail" 
        					class="instmailInput defInput"/>&nbsp;<label>Kopi</label>
        			<input 	type="checkbox" 
        					name="autosave-forms-sendboth" 
        					value="1" 
        					onchange="saveEmailData()" 
        					id="instEmailBoth" 
        					<?php if ($checkValue == 1) echo "checked"; ?>
        					> <span id="savedemail"></span>
        		</div>
        		<div class="instHelp">
        			Er feltet tomt, sendes epost til <?php  echo $defaultEmail[0]; ?>
        		</div>
        		
        	</div>
           
        	<BR />
        </form>
        <form>
        	<div class="InnstillingBoks">
        		<div class="instHeader Yellow">
        			Standard oppstartside: 
        		</div>
        		<div class="instInput ">
        			<input type="hidden" id="instUID" value="<?php echo $uid?>" />
 
        			  <select id="instStartPage" name="autosave-forms-altPage" onchange="saveStartpagedata()" class="instSelector">
                        <option value="Oversikt">Oversikt</option>
                        <?php for ($blipp = 0;$blipp <= count($avd);$blipp++){?>
                        	<option 
                        		value="<?php echo $avd[$blipp] ?>"
                        		<?php if ($avd[$blipp] == $definedUserVariables[2]) echo " selected"?>
                        		><?php echo $avd[$blipp] ?></option>
                        <?php }?>
                      </select><span id="savedoppstart"></span>
        			
        		</div>
        		<div class="instHelp">
        			Velg hvilken turnusside du vil skal vises etter innlogging
        		</div>
        	</div>
        	<BR />
        </form>
        <form>
        	<div class="InnstillingBoks">
        		<div class="instHeader Yellow">
        			Turnusvisning:  
        		</div>
        		<div class="instInput">
        			<input type="hidden" id="instUID" value="<?php echo $uid?>" /><label>Antall måneder som skal vises: </label>
        			<input 	type="text" 
        					name="autosave-forms-altNumMonths" 
        					value="<?php  if ($definedUserVariables[3] != '') echo $definedUserVariables[3]; ?>" 
        					placeholder="2" 
        					onkeyup="saveNumMonths()" 
        					id="instNumMonth" 
        					class="instFormInput defInput"/><span id="savedNumMonths"></span>
        		</div>
        		<div class="instHelp">
        			For at turvisningen ikke skal bli treg anbefaltes 1-2 mnd.
        		</div>
       			<div class="instInput">
        			<input type="hidden" id="instUID" value="<?php echo $uid?>" />
        			<hr>
        			<label>Start å vise så fort som mulig: </label>&nbsp;<input 	type="checkbox" 
        					name="autosave-forms-altDumpUpdate" 
        					value="1" 
        					onchange="saveDumpUpdate()" 
        					id="instDumpUpdate" 
        					<?php if ($definedUserVariables[9] == 1) echo "checked"; ?>
        					/>&nbsp;<span id="savedDumpUpdate"></span>
        		</div>
        		<div class="instHelp">
        			Velg om du vil starte å se med en gang, eller vente til alt er prosessert. Lastetid vil være like lang.
        		</div>
        	</div>
           
        	<BR />
        </form> 
        <form>
        	<div class="InnstillingBoks">
        		<div class="instHeader Yellow">
        			Dager i turnus før i dag: 
        		</div>
        		<div class="instInput">
        			<input type="hidden" id="instUID" value="<?php echo $uid?>" />
        			<input 	type="text" 
        					name="autosave-forms-altPreDays" 
        					value="<?php  if ($definedUserVariables[7] != '') echo $definedUserVariables[7]; ?>" 
        					placeholder="3" 
        					onkeyup="savePreDays()" 
        					id="instPreDays" 
        					class="instFormInput defInput"/><span id="savedPreDays"></span>
        		</div>
        		<div class="instHelp">
        		</div>
        	</div>
           
        	<BR />
        </form>    
       	<div class="InnstillingBoks">
       		<div class="instHeader Yellow">Send kalenderfil fra dato til dato:</div>
     		<div class="instInput">
		        <input type="text" name="daterange"  value="<?php echo date("d/m/Y", $TimeVar)?>  - <?php echo date("d/m/Y", strtotime("+1 day"))?>" /><label class="instHelp">&nbsp;Velg datoer</label>
		        <span id="sendResults"></span>
		    </div>
		    	<div class="instHelp">
        			Sender en iCal-fil med alle dine vakter mellom de to datoene du velger
        		</div>
		    
	    </div>
    </div>
    <div class="instCol">
        <form id="colorChanger">
        	<div class="InnstillingBoks">
        		<div class="instHeader Yellow">
        			Fargevalg:
        		</div>
        		<div class="instInput">
        			<?php
        			if ($definedUserVariables[6] != '' ) $savedColorsArray = unserialize($definedUserVariables[6]);
        //			print_r ($savedColorsArray);
        			
        			for ($i=1; oversettFraversGrunn($i) != ''; $i+=1){
        				$fraverBeskrivelse = oversettFraversGrunn($i);
        				 if ($savedColorsArray == '') $currentColor =  $fraverBeskrivelse[3];
        				 if (is_array($savedColorsArray)) $currentColor =  $savedColorsArray[$fraverBeskrivelse[2]];
        				?>
           <p style="font-size:12px;">
                <input name="<?php echo $fraverBeskrivelse[2];?>" 
                       onchange="saveColorData()" 
                       class="jscolor fargeVelger" 
                       value="<?php echo $currentColor;?>">
                <?php 
                echo $fraverBeskrivelse[1];
                //echo utf8_encode($fraverBeskrivelse[1]);
                ?>
            </p>
            	<?php }?>
          		</div>
        		<div class="instHelp">
        			<input type='hidden' name='UID' ID='UID' value='<?php echo $uid; ?>'>
        			Hvilke farger vil du turnusen skal bruke
        		</div>
        	</div>
        </form>
        	<BR />
        <form id="colorReset">
        	<div class="InnstillingBoks">
        		<div class="instHeader Yellow">
        			Reset farger:
        		</div>
        		<div class="instInput">
      			<input type='hidden' name='UID' ID='UID' value='<?php echo $uid; ?>'>
        		<input type="hidden"
        				name="ResetColor"
        				value="1">
                <input  type="button"
                		id="resetColorButton" 
                        onclick="resetColorData()" 
                        value="Tilbakestill"><label class="instHelp">&nbsp;Gjenoppretter standardfargene</label>
        		</div>
        	</div>
        </form>
    </div>

</div>