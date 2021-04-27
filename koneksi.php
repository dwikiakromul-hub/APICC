
<?php
    $serverName="10.15.96.73";
    $uid = "sa";
    $pwd = "M1tracomm";
    $connectionInfo = array( "UID"=>$uid,
                             "PWD"=>$pwd,
                             "Database"=>"Dispatcher",
                             "CharacterSet"=>"UTF-8");
    $conn = sqlsrv_connect( $serverName, $connectionInfo);
?>


