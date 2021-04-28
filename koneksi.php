
<?php
    $serverName="10.15.96.73";
    $uid = "sa";
    $pwd = "M1tracomm";
    $connectionInfo = array( "UID"=>$uid,
                             "PWD"=>$pwd,
                             "Database"=>"Dispatcher",
                             "CharacterSet"=>"UTF-8");
    $conn = sqlsrv_connect( $serverName, $connectionInfo);

    $user="BPBDCC";
    $pass="salamTangguh!!";

    function getToken() {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    
    // Create token payload as a JSON string
        $payload = json_encode(['username' => $user, 'password' => $pass]);
    
    // Encode Header to Base64Url String
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    
    // Encode Payload to Base64Url String
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'S4L4mT499uH', true);
    
    // Encode Signature to Base64Url String
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    
        return $jwt;
    }

    
    
?>


