<?php
$urlmod = "";
if ( $detect->isMobile() ) {
    $urlmod = "mobile_";
}

require_once 'users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(isset($user) && $user->isLoggedIn()){
  $sessionName = Config::get('session/session_name');
  $mySession = Session::get($sessionName);
  Session::put($mySession, time()+43200);
  require_once 'vaktplan_files/init.php';
  
}
?>
		<div class="jumbotron Yellow">
				<?php
				if($user->isLoggedIn()){
				    require_once $pages[$page];
				    ?>
				<?php }else{?>
					<a class="btn btn-warning" href="users/login.php" role="button"><?=lang("SIGNIN_TEXT");?> &raquo;</a>
				<?php }?>
		</div>
<?php  languageSwitcher();?>


<!-- Place any per-page javascript here -->
<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
