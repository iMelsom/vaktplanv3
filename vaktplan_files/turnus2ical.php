<?php 
//error_log(print_r($_POST, true), 0);
require_once '../users/init.php';  //make sure this path is correct!

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if(isset($user) && $user->isLoggedIn()){
    
$UID = $_POST['UID'];

if (strpos($url,'test') !== false) {
    $GLOBALS['testmode'] = "Test";
}
else{
    $GLOBALS['testmode'] = "NotTest";
}

//Set main database
if ($GLOBALS['testmode'] ==  "Test"){
    $GLOBALS['Database'] = 'minevakter_test';
    $GLOBALS['UserDB'] = 'minevakter_USpice';
}
if ($GLOBALS['testmode'] ==  "NotTest"){
    $GLOBALS['Database'] = 'vaktplan';
    $GLOBALS['UserDB'] = 'vaktplan_users';
}

require_once "includes/turnus_DBinfo.php";
require_once "includes/turnus_functions.php";
//$UID=9;
if ($UID != '') $uid = $UID; //Just som copy/paste and sloppyness hardening. 
if ($UID == '') $UID = $uid;

//if($UID=3)$UID = $uid = 13;

require_once 'init.php'; //, vaktplaninit, Trenger UID. Må hentes etter UID er definert.


$Startdato = $_POST['STARTDT'];
$Sluttdato = $_POST['ENDDT'];
$ical_dato_datestamp = date("Ymd\THis", time());

$definedUserVariables = chkUserVariables($uid);
if (($definedUserVariables[8] == '0') && ($definedUserVariables[5] != '' || $definedUserVariables[5] != false))$emailMottager =  $definedUserVariables[5];
if (($definedUserVariables[8] == '0') && ($definedUserVariables[5] == '' || $definedUserVariables[5] == false))$emailMottager = hentEpostAdresse($uid)[0];
if (($definedUserVariables[8] == '1') && ($definedUserVariables[5] != '' || $definedUserVariables[5] != false)){$emailMottager = hentEpostAdresse($uid)[0];$ccMottager =  $definedUserVariables[5];}
//error_log ("Mottager: " .$emailMottager);

//Generer brukervariabler: 
$harTur = "";
$turnus = "";
$extravakt = "";

//construct ICAL-file:
//File header:
$ical_content ="";
$ical_content = $ical_content .
"BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
PRODID:-//JBV//EN
X-WR-CALNAME:Turnus NOS
X-WR-TIMEZONE:\"Europe/Oslo\"
";

//File Contents: 
    $Tildelinger = hentTildelinger($UID, $Startdato); //Hent alle tildelte turer
    for ($a=0; strtotime($Startdato . " +".$a." day") <= strtotime($Sluttdato)&& $Tildelinger [0]!= "NoTour"; $a++){
       $AktivNixDato = strtotime($Startdato . " +".$a." day");
       $AktivDato = $TempDato = date("Y-m-d", $AktivNixDato);
       $vktCount = 0 ;
       
       for($c=0; $c < count($Tildelinger) && $Tildelinger[$c] != "NoTour";$c++){
           
           if ($Tildelinger[$c][5] != NULL && strtotime($AktivDato) > strtotime($Tildelinger[$c][5]) ){ //To check for changes in rotaoffset
               $Tildelinger[$c] = hentTildelinger($UID,$AktivDato,$Tildelinger[$c][2] );
           }
           
           if ($Tildelinger[$c] != "" && $Tildelinger[$c] != "NoTour"){
               //Husk � sjekke om turnusen er ny fra dag til dag. 
               $TurID = finnGjeldendeTurnus($AktivNixDato, $Tildelinger[$c][2]);
               $Turnus = hentTurnus($TurID[0]);
               
               //Hent vakter: 
               //Ordin�re vakter: 
               $Vakt =  HentVakt($UID, $AktivNixDato, $Turnus, $TurID[1],$Tildelinger[$c][2]); //Hentvakt henter ogs� vaktinfo
               //if onPRem is set:
               if ($Vakt[8] != '' || $Vakt[8] != NULL) $Vakt[6] = $Vakt[8]; //length
               if ($Vakt[9] != '' || $Vakt[9] != NULL) $Vakt[3] = $Vakt[9];  //Start
               if ($Vakt[10] != '' || $Vakt[10] != NULL) $Vakt[4] = $Vakt[10]; //Slutt
               
               if (strtotime($Vakt[3]) > strtotime($Vakt[4])) $TempDato = date("Y-m-d", strtotime($AktivDato . "-1 day"));
               
               // Har ordin�r vakt blirr flytta p�?
               $VaktBytte = sjekkVaktFlytt($Vakt[0], $AktivNixDato);
               // Er det tildelt ekstravakter? 
               $ekstravakt = sjekkEkstravakt($UID, $AktivNixDato); //sjekk ekstravakt sjekker kun om det er tildelt en ekstravakt, men henter ikke info
               
               //TilPrint
               $Output = array();
               
               //Behandle eventuelle vaktbytter: 
               if ($VaktBytte != false && $VaktBytte[2] =='Bytte'){
                   $Vakt[5] = '(byttet bort)'; //om vakta er bytta bort, marker den bytta bort
               }
               if ($VaktBytte != false && $VaktBytte[2] !='Bytte') {
                   unset($Vakt); // er vakta flytta, bare flytt og fjern den fra lista. Skal ordin�rt kun skje ved frav�r, eller turnusendringer
               }
               
               //Behandle registrert frav�r: 
               $fraver = sjekkFraver($UID, $AktivNixDato);
               if ($fraver[2] != ''){
                   $fraverBeskrivelse = oversettFraversGrunn($fraver[2]);
                   $Vakt[5] = $fraverBeskrivelse[1];
                   $Vakt[2] = "Fri";
                   $Vakt[1] = "";
               }
               
               //Legg vakter klar til skriving: 
               if(is_array($Vakt)){
                   $Output[$vktCount] = $Vakt;
                   $vktCount++;
               }

//              error_log("Output: " . print_r($Output, true));
               
               //Behandle eventuelle ekstravakter: 
              
               if (is_array($ekstravakt) && $ekstravakt[0] != '') {
                   $i = 0;
                   for ($g = 0; $g < count($ekstravakt) && is_array($ekstravakt); $g++){
                           $i++;
                           if ($ekstravakt[$g] != "" && is_array($ekstravakt[$g])){
                               $ekstravakt[$g] =  sjekkVaktType($ekstravakt[$g][1]);
                               //if onPRem is set:
                               if ($ekstravakt[$g][8] != '' || $ekstravakt[$g][8] != NULL) $ekstravakt[$g][6] = $ekstravakt[$g][8];
                               if ($ekstravakt[$g][9] != '' || $ekstravakt[$g][9] != NULL) $ekstravakt[$g][3] = $ekstravakt[$g][9];
                               if ($ekstravakt[$g][10] != '' || $ekstravakt[$g][10] != NULL) $ekstravakt[$g][4] = $ekstravakt[$g][10];
                               
                           }
                           if ($ekstravakt[$g] == "" || !is_array($ekstravakt[$g]))unset($ekstravakt[$g]);
                           
                           //marker som byttevakter om de er byttet: 
                           if ($ekstravakt[$g][2] == 'Bytte' && is_array($ekstravakt[$g])){
                               $ekstravakt[$g][1] = $ekstravakt[$g][1] ." (byttevakt)";
                               $ekstravakt[$g][5] =  $ekstravakt[$g][5] ." (byttevakt)";
                           }
                           
                           // marker som ekstravakter om de er ekstra: 
                           if ($ekstravakt[$g][2] != 'Bytte' && is_array($ekstravakt[$g])){
                               $ekstravakt[$g][1] = "Ekstravakt: " . $ekstravakt[$g][1];
                               $ekstravakt[$g][5] = "Ekstra " . $ekstravakt[$g][5];
                           }
                   }
                  // error_log("UID: " .$UID  ." AktivNixDato: " .$AktivNixDato  . " ekstravakt: " . print_r($ekstravakt, true));
                   
 //                  error_log(print_r($preventDoubles,true),0);
                   $Output = array_merge($Output ,$ekstravakt);
                   $vktCount++;
                   //error_log(print_r($Output,true),0);
               }
               if (!is_array($ekstravakt) || $ekstravakt[0] == '') {
                   unset($ekstravakt);
               }
           }
           
           //add event(s) to file
           for ($h = 0; $h < count($Output); $h++){
               if($Output[$h] != ''&& $Output[$h][5] != 'X'&& $Output[$h][5] != 'O'&& $Output[$h][5] != 'T'){
                   if ($Output[$h][6] == '') $ical_duration = "PT0H0M0S";
                   else{
                       $hourMin = explode(".", $Output[$h][6]);
                       if ($hourMin[1] ==  "00")$hourMin[1] = 00;
                       if ($hourMin[1] ==  "25")$hourMin[1] = 15;
                       if ($hourMin[1] ==  "50")$hourMin[1] = 30;
                       if ($hourMin[1] ==  "75")$hourMin[1] = 45;
                       $ical_duration = "PT" .$hourMin[0] . "H".$hourMin[1]."M0S";
                   }
                   $vakt_timestamp = strtotime($TempDato . " " . $Output[$h][3]);
                   $ical_dato_vaktstart = date("Ymd\THis", $vakt_timestamp);
                   
                   
                   $ical_uid = $vakt_timestamp. "-" .$Output[$h][0] ."-". $emailMottager[0];
    $ical_content = $ical_content .
"BEGIN:VEVENT
DTSTAMP:" .$ical_dato_datestamp. "
DTSTART:" .$ical_dato_vaktstart."
DURATION:" .$ical_duration. "
UID:" . $ical_uid . "
CLASS:PUBLIC
CREATED:" .$ical_dato_datestamp. "
DESCRIPTION:" . $Output[$h][5] . "
LOCATION:" . $Output[$h][2] . "
ORGANIZER;CN=OPM.Minevakter:mailto:varsling@minevakter.no
LAST-MODIFIED:"  . $ical_dato_datestamp . "
SUMMARY:". $Output[$h][1] . "
END:VEVENT
";
           }
        }
       
       }
    }
    //File footer: 
$ical_content = $ical_content .
"END:VCALENDAR";
//error_log($ical_content);

    //Send file
    //$mail_result=email($to,$subject,$body,$opts =[],$ical_content );
    //if($mail_result) error_log('Mail sent successfully');
    
    $email = new PHPMailer();
    $email->isSMTP();
//    $email->SMTPDebug = 3;
//    $email->SMTPDebugoutput = "error_log";
    $email->SMTPSecure = false;
    $email->SMTPAutoTLS = false;
    //$email->Host = 'localhost';
    $email->Host = 'mailrelay.li.jbv.local';
    $email->Mailer = 'smtp';
    $email->Port = 25;
    $email->CharSet = 'UTF-8';
    $email->Encoding = 'base64';
    $email->SetFrom('OPMVarsling@banenor.no', 'OPM Nettovervåkning'); //Name is optional
    //$email->SetFrom('ian.magnus.melsom@banenor.no', 'OPM Nettoverv�kning'); //Name is optional
    $email->Subject   = 'Turnus Oppdatering';
    $email->Body      = ' Her er turnusen din for ' . $date. ' - Vær oppmerksom på at Microsoft Outlook ikke respekterer tidsoneinstllinger på kalenderinvitasjoner';
    $email->addAddress( $emailMottager );
//     $email->AddAddress( "ian.magnus.melsom@banenor.no" );
//    $email->AddAddress("ian.m.melsom@gmail.com");
    if ($ccMottager != '') $email->addCC( $ccMottager );
    $email->addStringAttachment($ical_content, "turnus_".$Startdato."-".$Sluttdato. ".ical");
    
    //$email->AddAttachment( $file_to_attach, 'example_001.pdf' );
    if ($email->Send()) echo "Kalenderfil sendt\n";
    //if ($email->Send()) error_log( "Kalenderfil sendt\n");
    else {
        error_log( "Feil med sending av epost. ");
        error_log( "Feilmelding: " . $email-> ErrorInfo);
        
    }
}
?>