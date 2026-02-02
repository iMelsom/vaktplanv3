<?php //Social media sharing meta tags (delete if you don't want them) ?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php 
header( 'Content-type: text/html; charset=utf-8' );

ini_set("display_errors", 0);
ini_set('session.gc_maxlifetime', '43200');//viewport tag is inside the template
// <meta name="viewport" content="width=device-width, initial-scale=1">
?>
<meta name="description" content="">
<meta name="author" content="">

<?php //URL for website (link address) ?>
<meta property="og:url" content="">

<?php //type of site ?>
<meta property="og:type" content="website">

<?php //title of site (title of share) ?>
<meta property="og:title" content="Userspice Site">

<?php //description of site (text which appears when sharing) ?>
<meta property="og:description" content="Powered by UserSpice">

<?php //URL for preview image ?>
<meta property="og:image" content="">
<link rel="shortcut icon" href="<?=$us_url_root?>favicon.ico">

<?php  //Vaktplan-additions:
if (!isset($urlmod)) $urlmod ="";
if(file_exists($abs_us_root.$us_url_root.'/vaktplan_files/css/vaktplan.css')){?><link href="/vaktplan_files/css/<?php echo $urlmod;?>vaktplan.css" rel="stylesheet">
<?php } ?>


<link rel="stylesheet" type="text/css" href="/vaktplan_files/css/spectrum.css">
<link rel="stylesheet" type="text/css" href="/vaktplan_files/js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="/vaktplan_files/js/jquery-ui/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="/vaktplan_files/js/jquery-ui/jquery-ui.structure.min.css">
<link rel="stylesheet" type="text/css" href="/vaktplan_files/js/jquery-ui/jquery-ui.structure.css">
<link rel="stylesheet" type="text/css" href="/vaktplan_files/js/jquery-ui/jquery-ui.theme.css">
<link rel="stylesheet" type="text/css" href="/vaktplan_files/js/jquery-ui/jquery-ui.theme.min.css">
<link rel="stylesheet" type="text/css" href="/vaktplan_files/js/daterangepicker/daterangepicker.css">
<link rel="stylesheet" type="text/css" href="/vaktplan_files/css/timepicker.css">


<script src = "/vaktplan_files/js/jquery-3.4.1.min.js">  </script>
<script src = "/vaktplan_files/js/jquery-ui/jquery-ui.min.js">  </script>
<script src = "/vaktplan_files/js/jquery-ui/jquery-ui.js">  </script>
<script src = "/vaktplan_files/js/daterangepicker/moment.min.js">  </script>
<script src = "/vaktplan_files/js/daterangepicker/daterangepicker.js">  </script>
<script src = "/vaktplan_files/js/editDialog_frav.js">  </script>
<script src = "/vaktplan_files/js/editDialog_vakt.js">  </script>
<script src = "/vaktplan_files/js/editDialog_turadm.js">  </script>
<script src = "/vaktplan_files/js/T_mineinstillinger_autosaver.js">  </script>

<script type="text/javascript" src="/vaktplan_files/js/spectrum.js"></script>
<script src = "/vaktplan_files/js/colorpicker.js">  </script>
<script src = "/vaktplan_files/js/timepicker.js">  </script>
<script src = "/vaktplan_files/js/datepicker-nn.js">  </script>
<script src = "/vaktplan_files/js/datepicker-nb.js">  </script>
<script src=  "/vaktplan_files/js/jscolor/jscolor.js"></script>

<?php //vaktplan-addition ends ?>
