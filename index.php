<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

date_default_timezone_set('Asia/Jakarta');

include "koneksi.php";
$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn === false ) {
    echo "Koneksi Gagal</br>";
    die;
}

$method = $_SERVER['REQUEST_METHOD'];
$url = isset($_GET['url']) ? $_GET['url'] : '/';
$url = explode("/", $url);

if ($method == 'GET') {
    switch ($url[0]) {

        default:
            echo "Selamat Datang di API BPBD Prov DKI Jakarta Call Center";
            
        break;

        case "auth":
            $konten = trim(file_get_contents("php://input"));
            $decode = json_decode($konten, true);
            $response = array();
            if ($decode['username'] == 'BPBDCC' && $decode['password'] == 'salamTangguh!!') {
                $response = array(
                    'response' => array(
                        'token' => getToken()
                    ),
                    'metadata' => array(
                        'message' => 'Ok',
                        'code' => 200
                    )
                );
            } else {
                $response = array(
                    'metadata' => array(
                        'message' => 'Access denied',
                        'code' => 401
                    )
                );
            }
            echo json_encode(array("response" => $response));

            break;

        //get data tiket pertahun Emergency
        case "ticket-data-1":
            $header = apache_request_headers();
            $konten = trim(file_get_contents("php://input"));
            $decode = json_decode($konten, true);
            if ($header['x-token'] == getToken()) {
                if($url[1]!=""){
                    $sql = "SELECT Ds_transaction.ticket_code, Ds_history.history_fullname,Ds_history.history_inumber, Ds_transaction.trans_desc, 
                    Ds_transaction.trans_addr,DsCase_Subcategory.subcategory_casename, Ds_kecamatan.kecamatan_name,Ds_city.city_name,
                    Ds_transaction.trans_createdate, Ds_transaction.trans_victimstat, DS_CaseType.type_casename
                    FROM Ds_transaction
                    JOIN Ds_history ON Ds_transaction.ticket_code = Ds_history.ticket_code
                    JOIN DsCase_Subcategory ON Ds_transaction.subcategory_caseid = DsCase_Subcategory.subcategory_caseid
                    JOIN DsCase_category ON DsCase_Subcategory.subcategory_casecategoryid = DsCase_category.category_caseid
                    JOIN DS_CaseType ON DsCase_category.category_casetypeid = DS_CaseType.type_caseid
                    JOIN Ds_kecamatan ON Ds_transaction.kecamatan_id = Ds_kecamatan.kecamatan_id
                    JOIN Ds_city ON Ds_kecamatan.city_id = Ds_city.city_id
                    where YEAR(Ds_transaction.trans_createdate)='$url[1]' and type_casename = 'Emergency'
                    order by trans_createdate desc";
                    $result = sqlsrv_query( $conn, $sql);
                    if( $result === false ) {
                        echo "Error in executing query.</br>";
                        die;
                    }
                    $json = [];
                    $i = 
                    1;
                    do {
                        while ($row = sqlsrv_fetch_array($result)) {
                            $json[] = [
                                'Nama Pelapor'=> substr($row ["history_fullname"],0,3).'xxxx',
                                'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxxx',
                                'Masalah' => substr($row ["trans_desc"],0,10).'xxxx',
                                'Alamat Lengkap' => $row["trans_addr"],
                                'kategori' => $row["subcategory_casename"],
                                'Kecamatan' => $row["kecamatan_name"],
                                'Kota' => $row ["city_name"],
                                'Tanggal' => $row ["trans_createdate"] -> format('d-m-Y'),
                                'Jam' => $row ["trans_createdate"] -> format('H:i:s'),
                                'Status' => $row["type_casename"]
                                ];
                                $i = $i + 1;
                        }
                    } while (sqlsrv_next_result($result));
                }else {
                    $json=array("Metadata"=>402);
                }
            } else {
                $json = array(
                    'metadata' => array(
                        'message' => 'Access denied',
                        'code' => 401
                    )
                );
            }
            echo json_encode($json,true);
            break;

            //get data tiket pertahun Non Emergency
        case "ticket-data-2":
            $header = apache_request_headers();
            $konten = trim(file_get_contents("php://input"));
            $decode = json_decode($konten, true);
            if ($header['x-token'] == getToken()) {
            if($url[1]!=""){
                $sql = "SELECT Ds_transaction.ticket_code, Ds_history.history_fullname,Ds_history.history_inumber, Ds_transaction.trans_desc, 
                Ds_transaction.trans_addr,DsCase_Subcategory.subcategory_casename, Ds_kecamatan.kecamatan_name,Ds_city.city_name,
                Ds_transaction.trans_createdate, Ds_transaction.trans_victimstat, DS_CaseType.type_casename
                FROM Ds_transaction
                JOIN Ds_history ON Ds_transaction.ticket_code = Ds_history.ticket_code
                JOIN DsCase_Subcategory ON Ds_transaction.subcategory_caseid = DsCase_Subcategory.subcategory_caseid
                JOIN DsCase_category ON DsCase_Subcategory.subcategory_casecategoryid = DsCase_category.category_caseid
                JOIN DS_CaseType ON DsCase_category.category_casetypeid = DS_CaseType.type_caseid
                JOIN Ds_kecamatan ON Ds_transaction.kecamatan_id = Ds_kecamatan.kecamatan_id
                JOIN Ds_city ON Ds_kecamatan.city_id = Ds_city.city_id
                where YEAR(Ds_transaction.trans_createdate)='$url[1]' and type_casename = 'Non Emergency'
                order by trans_createdate desc";
                $result = sqlsrv_query( $conn, $sql);
                if( $result === false ) {
                    echo "Error in executing query.</br>";
                    die;
                }
                $json = [];
                $i = 
                1;
                do {
                    while ($row = sqlsrv_fetch_array($result)) {
                        $json[] = [
                            'Nama Pelapor'=> substr($row ["history_fullname"],0,3).'xxxx',
                            'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxxx',
                            'Masalah' => substr($row ["trans_desc"],0,10).'xxxx',
                            'Alamat Lengkap' => $row["trans_addr"],
                            'kategori' => $row["subcategory_casename"],
                            'Kecamatan' => $row["kecamatan_name"],
                            'Kota' => $row ["city_name"],
                            'Tanggal' => $row ["trans_createdate"] -> format('d-m-Y'),
                            'Jam' => $row ["trans_createdate"] -> format('H:i:s'),
                            'Status' => $row["type_casename"]
                            ];
                            $i = $i + 1;
                    }
                } while (sqlsrv_next_result($result));
            }else {
                $json=array("Metadata"=>402);
                }
            } else {
                $json = array(
                    'metadata' => array(
                        'message' => 'Access denied',
                        'code' => 401
                    )
                );
            }
            echo json_encode($json,true);
            break;


            // get data tiket hari ini Emergency
            case "liveTicket-1d-1":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
                if ($header['x-token'] == getToken()) {
                    $sql = "SELECT Ds_transaction.ticket_code, Ds_history.history_fullname,Ds_history.history_inumber, Ds_transaction.trans_desc, 
                    Ds_transaction.trans_addr,DsCase_Subcategory.subcategory_casename, Ds_kecamatan.kecamatan_name,Ds_city.city_name,
                    Ds_transaction.trans_createdate, Ds_transaction.trans_victimstat, DS_CaseType.type_casename
                    FROM Ds_transaction
                    JOIN Ds_history ON Ds_transaction.ticket_code = Ds_history.ticket_code
                    JOIN DsCase_Subcategory ON Ds_transaction.subcategory_caseid = DsCase_Subcategory.subcategory_caseid
                    JOIN DsCase_category ON DsCase_Subcategory.subcategory_casecategoryid = DsCase_category.category_caseid
                    JOIN DS_CaseType ON DsCase_category.category_casetypeid = DS_CaseType.type_caseid
                    JOIN Ds_kecamatan ON Ds_transaction.kecamatan_id = Ds_kecamatan.kecamatan_id
                    JOIN Ds_city ON Ds_kecamatan.city_id = Ds_city.city_id
                    where Ds_history.history_createdate >= DATEADD(day, -1, GETDATE()) and type_casename = 'Emergency'
                    order by trans_createdate desc";

                    $result = sqlsrv_query( $conn, $sql);
                    if( $result === false ) {
                        echo "Error in executing query.</br>";
                        die;
                    }
                    $json = [];
                    $i = 1;
                    do {
                        while ($row = sqlsrv_fetch_array($result)) {
                            $json[] = [
                                'Nama Pelapor'=> substr($row ["history_fullname"],0,3).'xxxx',
                                'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxxx',
                                'Masalah' => substr($row ["trans_desc"],0,10).'xxxx',
                                'Alamat Lengkap' => $row["trans_addr"],
                                'kategori' => $row["subcategory_casename"],
                                'Kecamatan' => $row["kecamatan_name"],
                                'Kota' => $row ["city_name"],
                                'Tanggal' => $row ["trans_createdate"] -> format('d-m-Y'),
                                'Jam' => $row ["trans_createdate"] -> format('H:i:s'),
                                'Status' => $row["type_casename"]
                                ];
                                $i = $i + 1;
                        }
                    } while (sqlsrv_next_result($result));
                }else {
                    $json = array(
                        'metadata' => array(
                            'message' => 'Access denied',
                            'code' => 401
                        )
                    );
                } 
                echo json_encode($json,true);
                break;

                 // get data tiket hari ini Non Emergency
            case "liveTicket-1d-2":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
                if ($header['x-token'] == getToken()) {
                    $sql = "SELECT Ds_transaction.ticket_code, Ds_history.history_fullname,Ds_history.history_inumber, Ds_transaction.trans_desc, 
                    Ds_transaction.trans_addr,DsCase_Subcategory.subcategory_casename, Ds_kecamatan.kecamatan_name,Ds_city.city_name,
                    Ds_transaction.trans_createdate, Ds_transaction.trans_victimstat, DS_CaseType.type_casename
                    FROM Ds_transaction
                    JOIN Ds_history ON Ds_transaction.ticket_code = Ds_history.ticket_code
                    JOIN DsCase_Subcategory ON Ds_transaction.subcategory_caseid = DsCase_Subcategory.subcategory_caseid
                    JOIN DsCase_category ON DsCase_Subcategory.subcategory_casecategoryid = DsCase_category.category_caseid
                    JOIN DS_CaseType ON DsCase_category.category_casetypeid = DS_CaseType.type_caseid
                    JOIN Ds_kecamatan ON Ds_transaction.kecamatan_id = Ds_kecamatan.kecamatan_id
                    JOIN Ds_city ON Ds_kecamatan.city_id = Ds_city.city_id
                    where Ds_history.history_createdate >= DATEADD(day, -1, GETDATE()) and type_casename = 'Non Emergency'
                    order by trans_createdate desc";

                    $result = sqlsrv_query( $conn, $sql);
                    if( $result === false ) {
                        echo "Error in executing query.</br>";
                        die;
                    }
                    $json = [];
                    $i = 1;
                    do {
                        while ($row = sqlsrv_fetch_array($result)) {
                            $json[] = [
                                'Nama Pelapor'=> substr($row ["history_fullname"],0,3).'xxxx',
                                'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxxx',
                                'Masalah' => substr($row ["trans_desc"],0,10).'xxxx',
                                'Alamat Lengkap' => $row["trans_addr"],
                                'kategori' => $row["subcategory_casename"],
                                'Kecamatan' => $row["kecamatan_name"],
                                'Kota' => $row ["city_name"],
                                'Tanggal' => $row ["trans_createdate"] -> format('d-m-Y'),
                                'Jam' => $row ["trans_createdate"] -> format('H:i:s'),
                                'Status' => $row["type_casename"]
                                ];
                                $i = $i + 1;
                        }
                    } while (sqlsrv_next_result($result));
                }else {
                    $json = array(
                        'metadata' => array(
                            'message' => 'Access denied',
                            'code' => 401
                        )
                    );
                } 
                echo json_encode($json,true);
                break;


            // get data tiket 3 jam terakhir Emergency
            case "liveTicket-1":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
                if ($header['x-token'] == getToken()) {
                    $sql = "SELECT Ds_transaction.ticket_code, Ds_history.history_fullname,Ds_history.history_inumber, Ds_transaction.trans_desc, 
                    Ds_transaction.trans_addr,DsCase_Subcategory.subcategory_casename, Ds_kecamatan.kecamatan_name,Ds_city.city_name,
                    Ds_transaction.trans_createdate, Ds_transaction.trans_victimstat, DS_CaseType.type_casename
                    FROM Ds_transaction
                    JOIN Ds_history ON Ds_transaction.ticket_code = Ds_history.ticket_code
                    JOIN DsCase_Subcategory ON Ds_transaction.subcategory_caseid = DsCase_Subcategory.subcategory_caseid
                    JOIN DsCase_category ON DsCase_Subcategory.subcategory_casecategoryid = DsCase_category.category_caseid
                    JOIN DS_CaseType ON DsCase_category.category_casetypeid = DS_CaseType.type_caseid
                    JOIN Ds_kecamatan ON Ds_transaction.kecamatan_id = Ds_kecamatan.kecamatan_id
                    JOIN Ds_city ON Ds_kecamatan.city_id = Ds_city.city_id
                    where Ds_transaction.trans_createdate >= DATEADD(HOUR, -3, GETDATE()) and type_casename = 'Emergency'
                    order by trans_createdate desc";

                    $result = sqlsrv_query( $conn, $sql);
                    if( $result === false ) {
                        echo "Error in executing query.</br>";
                        die;
                    }
                    $json = [];
                    $i = 1;
                    do {
                        while ($row = sqlsrv_fetch_array($result)) {
                            $json[] = [
                                'Nama Pelapor'=> substr($row ["history_fullname"],0,3).'xxxx',
                                'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxxx',
                                'Masalah' => substr($row ["trans_desc"],0,10).'xxxx',
                                'Alamat Lengkap' => $row["trans_addr"],
                                'kategori' => $row["subcategory_casename"],
                                'Kecamatan' => $row["kecamatan_name"],
                                'Kota' => $row ["city_name"],
                                'Tanggal' => $row ["trans_createdate"] -> format('d-m-Y'),
                                'Jam' => $row ["trans_createdate"] -> format('H:i:s'),
                                'Status' => $row["type_casename"]
                                ];
                                $i = $i + 1;
                        }
                    } while (sqlsrv_next_result($result));
                }else {
                    $json = array(
                        'metadata' => array(
                            'message' => 'Access denied',
                            'code' => 401
                        )
                    );
                } 
                echo json_encode($json,true);
                break;

                // get data tiket 3 jam terakhir Non Emergency
            case "liveTicket-2":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
                if ($header['x-token'] == getToken()) {
                    $sql = "SELECT Ds_transaction.ticket_code, Ds_history.history_fullname,Ds_history.history_inumber, Ds_transaction.trans_desc, 
                    Ds_transaction.trans_addr,DsCase_Subcategory.subcategory_casename, Ds_kecamatan.kecamatan_name,Ds_city.city_name,
                    Ds_transaction.trans_createdate, Ds_transaction.trans_victimstat, DS_CaseType.type_casename
                    FROM Ds_transaction
                    JOIN Ds_history ON Ds_transaction.ticket_code = Ds_history.ticket_code
                    JOIN DsCase_Subcategory ON Ds_transaction.subcategory_caseid = DsCase_Subcategory.subcategory_caseid
                    JOIN DsCase_category ON DsCase_Subcategory.subcategory_casecategoryid = DsCase_category.category_caseid
                    JOIN DS_CaseType ON DsCase_category.category_casetypeid = DS_CaseType.type_caseid
                    JOIN Ds_kecamatan ON Ds_transaction.kecamatan_id = Ds_kecamatan.kecamatan_id
                    JOIN Ds_city ON Ds_kecamatan.city_id = Ds_city.city_id
                    where Ds_transaction.trans_createdate >= DATEADD(HOUR, -3, GETDATE()) and type_casename = 'Non Emergency'
                    order by trans_createdate desc";

                    $result = sqlsrv_query( $conn, $sql);
                    if( $result === false ) {
                        echo "Error in executing query.</br>";
                        die;
                    }
                    $json = [];
                    $i = 1;
                    do {
                        while ($row = sqlsrv_fetch_array($result)) {
                            $json[] = [
                                'Nama Pelapor'=> substr($row ["history_fullname"],0,3).'xxxx',
                                'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxxx',
                                'Masalah' => substr($row ["trans_desc"],0,10).'xxxx',
                                'Alamat Lengkap' => $row["trans_addr"],
                                'kategori' => $row["subcategory_casename"],
                                'Kecamatan' => $row["kecamatan_name"],
                                'Kota' => $row ["city_name"],
                                'Tanggal' => $row ["trans_createdate"] -> format('d-m-Y'),
                                'Jam' => $row ["trans_createdate"] -> format('H:i:s'),
                                'Status' => $row["type_casename"]
                                ];
                                $i = $i + 1;
                        }
                    } while (sqlsrv_next_result($result));
                }else {
                    $json = array(
                        'metadata' => array(
                            'message' => 'Access denied',
                            'code' => 401
                        )
                    );
                } 
                echo json_encode($json,true);
                break;
    
            //get data call pertahun succesfully
            case "call-data-1":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
                if ($header['x-token'] == getToken()) {
                if($url[1]!=""){
                    $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                    FROM Ds_history
                    JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                    where YEAR(Ds_history.history_createdate)='$url[1]' and status_name = 'Successfully Call'
                    order by history_createdate desc";
                    $result = sqlsrv_query( $conn, $sql);
                    if( $result === false ) {
                        echo "Error in executing query.</br>";
                        die;
                    }
                    $json = [];
                    $i = 1;
                    do {
                        while ($row = sqlsrv_fetch_array($result)) {
                            $json[] = [
                                'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                                'Tanggal' => $row["history_createdate"]-> format('d-m-Y'),
                                'Jam' => $row["history_createdate"]-> format('H:i:s'),
                                'Status Panggilan' => $row["status_name"]
                                ];
                                $i = $i + 1;
                        }
                    } while (sqlsrv_next_result($result));
                }else {
                    $json=array("Metadata"=>402);
                }
            } else {
                $json = array(
                    'metadata' => array(
                        'message' => 'Access denied',
                        'code' => 401
                    )
                );
            }
            echo json_encode($json,true);
            break;

                 //get data call pertahun Drop Call
                case "call-data-2":
                    $header = apache_request_headers();
                    $konten = trim(file_get_contents("php://input"));
                    $decode = json_decode($konten, true);
                    if ($header['x-token'] == getToken()) {
                    if($url[1]!=""){
                        $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                        FROM Ds_history
                        JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                        where YEAR(Ds_history.history_createdate)='$url[1]' and status_name = 'Drop Call'
                        order by history_createdate desc";
                        $result = sqlsrv_query( $conn, $sql);
                        if( $result === false ) {
                            echo "Error in executing query.</br>";
                            die;
                        }
                        $json = [];
                        $i = 1;
                        do {
                            while ($row = sqlsrv_fetch_array($result)) {
                                $json[] = [
                                    'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                                    'Tanggal' => $row["history_createdate"]-> format('d-m-Y'),
                                    'Jam' => $row["history_createdate"]-> format('H:i:s'),
                                    'Status Panggilan' => $row["status_name"]
                                    ];
                                    $i = $i + 1;
                            }
                        } while (sqlsrv_next_result($result));
                    }else {
                        $json=array("Metadata"=>402);
                    }
                } else {
                    $json = array(
                        'metadata' => array(
                            'message' => 'Access denied',
                            'code' => 401
                        )
                    );
                }
                echo json_encode($json,true);
                break;

                    //get data call pertahun Prank Call
                    case "call-data-3":
                        $header = apache_request_headers();
                        $konten = trim(file_get_contents("php://input"));
                        $decode = json_decode($konten, true);
                        if ($header['x-token'] == getToken()) {
                        if($url[1]!=""){
                            $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                            FROM Ds_history
                            JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                            where YEAR(Ds_history.history_createdate)='$url[1]' and status_name = 'Prank Call'
                            order by history_createdate desc";
                            $result = sqlsrv_query( $conn, $sql);
                            if( $result === false ) {
                                echo "Error in executing query.</br>";
                                die;
                            }
                            $json = [];
                            $i = 1;
                            do {
                                while ($row = sqlsrv_fetch_array($result)) {
                                    $json[] = [
                                        'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                                        'Tanggal' => $row["history_createdate"]-> format('d-m-Y'),
                                        'Jam' => $row["history_createdate"]-> format('H:i:s'),
                                        'Status Panggilan' => $row["status_name"]
                                        ];
                                        $i = $i + 1;
                                }
                            }while (sqlsrv_next_result($result));
                        }else {
                            $json=array("Metadata"=>402);
                        }
                    } else {
                        $json = array(
                            'metadata' => array(
                                'message' => 'Access denied',
                                'code' => 401
                            )
                        );
                    }
                    echo json_encode($json,true);
                    break;


            //get data Call hari ini succesfully
            case "liveCall-1d-1":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
                if ($header['x-token'] == getToken()) {
                    $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                    FROM Ds_history
                    JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                    where Ds_history.history_createdate >= DATEADD(day, -1, GETDATE()) and status_name = 'Successfully Call'
                    order by history_createdate desc";
                    $result = sqlsrv_query( $conn, $sql);
                    if( $result === false ) {
                        echo "Error in executing query.</br>";
                        die;
                    }
                    $json = [];
                    $i = 1;
                    do {
                        while ($row = sqlsrv_fetch_array($result)) {
                            $json[] = [
                                'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                                'Tanggal' => $row["history_createdate"]-> format('d-m-Y'),
                                'Jam' => $row["history_createdate"]-> format('H:i:s'),
                                'Status Panggilan' => $row["status_name"]
                                ];
                                $i = $i + 1;
                        }
                    } while (sqlsrv_next_result($result));
                }else {
                    $json = array(
                        'metadata' => array(
                            'message' => 'Access denied',
                            'code' => 401
                        )
                    );
                } 
                echo json_encode($json,true);
                break;

                 //get data Call hari ini Drop call
                case "liveCall-1d-2":
                    $header = apache_request_headers();
                    $konten = trim(file_get_contents("php://input"));
                    $decode = json_decode($konten, true);
                    if ($header['x-token'] == getToken()) {
                        $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                        FROM Ds_history
                        JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                        where Ds_history.history_createdate >= DATEADD(day, -1, GETDATE()) and status_name = 'Drop Call'
                        order by history_createdate desc";
                        $result = sqlsrv_query( $conn, $sql);
                        if( $result === false ) {
                            echo "Error in executing query.</br>";
                            die;
                        }
                        $json = [];
                        $i = 1;
                        do {
                            while ($row = sqlsrv_fetch_array($result)) {
                                $json[] = [
                                    'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                                    'Tanggal' => $row["history_createdate"]-> format('d-m-Y'),
                                    'Jam' => $row["history_createdate"]-> format('H:i:s'),
                                    'Status Panggilan' => $row["status_name"]
                                    ];
                                    $i = $i + 1;
                            }
                        } while (sqlsrv_next_result($result));
                    }else {
                        $json = array(
                            'metadata' => array(
                                'message' => 'Access denied',
                                'code' => 401
                            )
                        );
                    } 
                    echo json_encode($json,true);
                    break;

                     //get data Call hari ini Prank call
                    case "liveCall-1d-3":
                        $header = apache_request_headers();
                        $konten = trim(file_get_contents("php://input"));
                        $decode = json_decode($konten, true);
                        if ($header['x-token'] == getToken()) {
                            $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                            FROM Ds_history
                            JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                            where Ds_history.history_createdate >= DATEADD(day, -1, GETDATE()) and status_name = 'Prank Call'
                            order by history_createdate desc";
                            $result = sqlsrv_query( $conn, $sql);
                            if( $result === false ) {
                                echo "Error in executing query.</br>";
                                die;
                            }
                            $json = [];
                            $i = 1;
                            do {
                                while ($row = sqlsrv_fetch_array($result)) {
                                    $json[] = [
                                        'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                                        'Tanggal' => $row["history_createdate"]-> format('d-m-Y'),
                                        'Jam' => $row["history_createdate"]-> format('H:i:s'),
                                        'Status Panggilan' => $row["status_name"]
                                        ];
                                        $i = $i + 1;
                                }
                            } while (sqlsrv_next_result($result));
                        }else {
                            $json = array(
                                'metadata' => array(
                                    'message' => 'Access denied',
                                    'code' => 401
                                )
                            );
                        } 
                        echo json_encode($json,true);
                        break;

                        //get data tiket 3jam terakhir Succesfully
                        case "liveCall-1":
                            $header = apache_request_headers();
                            $konten = trim(file_get_contents("php://input"));
                            $decode = json_decode($konten, true);
                            if ($header['x-token'] == getToken()) {
                                $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                                FROM Ds_history
                                JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                                where Ds_history.history_createdate >= DATEADD(HOUR, -3, GETDATE()) and status_name = 'Successfully Call'
                                order by history_createdate desc";
                                $result = sqlsrv_query( $conn, $sql);
                                if( $result === false ) {
                                    echo "Error in executing query.</br>";
                                    die;
                                }
                                $json = [];
                                $i = 1;
                                do {
                                    while ($row = sqlsrv_fetch_array($result)) {
                                        $json[] = [
                                            'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                                            'Tanggal' => $row["history_createdate"]-> format('d-m-Y'),
                                            'Jam' => $row["history_createdate"]-> format('H:i:s'),
                                            'Status Panggilan' => $row["status_name"]
                                            ];
                                            $i = $i + 1;
                                    }
                                } while (sqlsrv_next_result($result));
                            }else {
                                $json = array(
                                    'metadata' => array(
                                        'message' => 'Access denied',
                                        'code' => 401
                                    )
                                );
                            } 
                            echo json_encode($json,true);
                            break;

                             //get data tiket 3jam terakhir Drop call
                        case "liveCall-2":
                            $header = apache_request_headers();
                            $konten = trim(file_get_contents("php://input"));
                            $decode = json_decode($konten, true);
                            if ($header['x-token'] == getToken()) {
                                $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                                FROM Ds_history
                                JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                                where Ds_history.history_createdate >= DATEADD(HOUR, -3, GETDATE()) and status_name = 'Drop Call'
                                order by history_createdate desc";
                                $result = sqlsrv_query( $conn, $sql);
                                if( $result === false ) {
                                    echo "Error in executing query.</br>";
                                    die;
                                }
                                $json = [];
                                $i = 1;
                                do {
                                    while ($row = sqlsrv_fetch_array($result)) {
                                        $json[] = [
                                            'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                                            'Tanggal' => $row["history_createdate"]-> format('d-m-Y'),
                                            'Jam' => $row["history_createdate"]-> format('H:i:s'),
                                            'Status Panggilan' => $row["status_name"]
                                            ];
                                            $i = $i + 1;
                                    }
                                } while (sqlsrv_next_result($result));
                            }else {
                                $json = array(
                                    'metadata' => array(
                                        'message' => 'Access denied',
                                        'code' => 401
                                    )
                                );
                            } 
                            echo json_encode($json,true);
                            break;

                               //get data tiket 3jam terakhir Prank call
                        case "liveCall-3":
                            $header = apache_request_headers();
                            $konten = trim(file_get_contents("php://input"));
                            $decode = json_decode($konten, true);
                            if ($header['x-token'] == getToken()) {
                                $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                                FROM Ds_history
                                JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                                where Ds_history.history_createdate >= DATEADD(HOUR, -3, GETDATE()) and status_name = 'Prank Call'
                                order by history_createdate desc";
                                $result = sqlsrv_query( $conn, $sql);
                                if( $result === false ) {
                                    echo "Error in executing query.</br>";
                                    die;
                                }
                                $json = [];
                                $i = 1;
                                do {
                                    while ($row = sqlsrv_fetch_array($result)) {
                                        $json[] = [
                                            'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                                            'Tanggal' => $row["history_createdate"]-> format('d-m-Y'),
                                            'Jam' => $row["history_createdate"]-> format('H:i:s'),
                                            'Status Panggilan' => $row["status_name"]
                                            ];
                                            $i = $i + 1;
                                    }
                                } while (sqlsrv_next_result($result));
                            }else {
                                $json = array(
                                    'metadata' => array(
                                        'message' => 'Access denied',
                                        'code' => 401
                                    )
                                );
                            } 
                            echo json_encode($json,true);
                            break;
    }
}else{
    echo "Selamat Datang di API BPBD Prov DKI Jakarta Call Center ";
    
}
