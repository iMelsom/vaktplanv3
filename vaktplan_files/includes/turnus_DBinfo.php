<?php

function QueryMySQLPrepped($QueryData, $Statement, $DB){
    
     
    //error_log($Statement);
 //   error_log(print_r($QueryData,true),0);
    $config_mysql_host= "localhost"; //Your host name
    require getcwd().'/vaktplan_files/environment/db_info.php';
    $config_mysql_database = $DB;
    //error_log(getcwd() );
    //error_log($config_mysql_pass);
    //error_log($DB);
    
    //	PDO prepped: 
    $pdo = new PDO("mysql:host=$config_mysql_host;dbname=$config_mysql_database;charset=utf8","$config_mysql_user","$config_mysql_pass");
    $pdo ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();
    $stmt= $pdo->prepare($Statement);
    
    //Make it so!
    $stmt->execute($QueryData);
    
   
    //Get Data: 
    $result = $stmt->fetchAll();
    $pdo->commit();
    
    
    //closeDB and end function
    $pdo = null;
    return $result;

}


function WriteMySQLPrepped($QueryData, $Statement, $DB,  $UID){
    
    $config_mysql_host = "localhost"; //Your host name
    //require '/var/www/html/minehjelpere/vakter/db_info_rw.php';
    require getcwd().'/db_info.php';
    $config_mysql_database = $DB;

    $pdo = new PDO("mysql:host=$config_mysql_host;dbname=$config_mysql_database","$config_mysql_user","$config_mysql_pass");
    $pdo ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt= $pdo->prepare($Statement);
    $stmt->execute($QueryData);

    //closeDB and end function
    
   // $pdo -> commit();
    $pdo = null;
    
    //error_log(print_r($pdo->errorInfo(), true),0);
    //error_log(print_r($stmt->errorInfo(), true),0);
    

    //log  dbquery to db-log (if it not itself a log)
    $Sanitation = addslashes($Statement);
    $Loggable_Querydata = serialize($QueryData);
    if (!preg_match('/UpdateLog/', $Sanitation)){
        $Datestamp = date("Y-m-d H:i:s", time());
        $Statement_logger = "INSERT INTO `UpdateLog` (`ID`, `query`, `Dato`, `UserID`, `QueryData`) VALUES (NULL, :query, :Dato, :UserID, :QueryData);";
        $QueryData_logger = array("query" => $Sanitation, "Dato" => $Datestamp, "UserID" => $UID, "QueryData" => $Loggable_Querydata);
       
        $pdo = new PDO("mysql:host=$config_mysql_host;dbname=$config_mysql_database","$config_mysql_user","$config_mysql_pass");
        $pdo ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt= $pdo->prepare($Statement_logger);
        $stmt->execute($QueryData_logger);
        
        //$pdo -> commit();
        $pdo = null;
    }
    
    return $result;
    
    
}
?>