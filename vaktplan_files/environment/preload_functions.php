<?php 
if (!function_exists('email')) {
    function email($to,$ccMottager = false, $subject, $body, $opts = [], $ical ="", $attachment = null)
    {
        global $db, $abs_us_root, $us_url_root;
        $results = $db->query('SELECT * FROM email')->first();
        
        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = $results->debug_level;               // Enable verbose debug output
        $mail->XMailer = null;
        if ($results->isSMTP == 1) {
            $mail->isSMTP();
        }             // Set mailer to use SMTP
        $mail->Host = $results->smtp_server;                    // Specify SMTP server
        $mail->SMTPAuth = $results->useSMTPauth;                // Enable SMTP authentication
        $mail->Username = $results->email_login;                 // SMTP username
        $mail->Password = html_entity_decode($results->email_pass);    // SMTP password
        $mail->SMTPSecure = $results->transport;                 // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $results->smtp_port;
        if ($results->authtype != "") {
            $mail->AuthType = $results->authtype;
        }
        
        
        if ($attachment != false) {
            $mail->addAttachment($attachment);
        }
        
        if($ical != ""){ // Addidtion for ical-files from vaktplan
            $attachment=true;
            $mail->addStringAttachment($ical,'ical.ics','base64','text/calendar');
        }
        
        if (isset($opts['email']) && isset($opts['name'])) {
            $mail->setFrom($opts['email'], $opts['name']);
        } else {
            $mail->setFrom($results->from_email, $results->from_name);
        }
        
        if (isset($opts['replyTo'])) {
            $mail->addReplyTo($opts['replyTo']);
        }
        
        if (isset($opts['cc'])) {
            $mail->addCC($opts['cc']);
        }
        
        if ($ccMottager != '') $mail->addCC($ccMottager); //Bakoverkompabilitet mot script i vaktplan
        
        
        if (isset($opts['bcc'])) {
            $mail->addBCC($opts['bcc']);
        }
        
        if (is_array($to)) {
            foreach ($to as $t) {
                $mail->addAddress(rawurldecode($t));
            }
        } else {
            $mail->addAddress(rawurldecode($to));
        }
        if ($results->isHTML == 'true') {
            $mail->isHTML(true);
        }
        
        $mail->Subject = $subject;
        $mail->Body    = $body;
        if (!empty($attachment)) $mail->addAttachment($attachment);
        if (file_exists($abs_us_root . $us_url_root . "usersc/scripts/email_function_override.php")) {
            require_once $abs_us_root . $us_url_root . "usersc/scripts/email_function_override.php";
        }
        $result = $mail->send();
        
        return $result;
    }
}
?>