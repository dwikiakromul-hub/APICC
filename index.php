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
            echo "selamat datang di API BPBD Prov DKI Jakarta";
        break;

        //get data tiket pertahun
        case "ticket-data":
            $header = apache_request_headers();
            $konten = trim(file_get_contents("php://input"));
            $decode = json_decode($konten, true);
            if($url[1]!="" && $url[2]!="" ){
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
                where Ds_transaction.trans_createdate like '%$url[1]%' and type_casename = '$url[2]'
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
            echo json_encode($json,true);
            break;


            // get data tiket hari ini
            case "liveTicket-1d":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
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
                    where Ds_history.history_createdate >= DATEADD(day, -1, GETDATE()) and type_casename = '$url[1]'
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
                }else{
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
                    where Ds_history.history_createdate >= DATEADD(day, -1, GETDATE())
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
                }
                echo json_encode($json,true);
                break;


            // get data tiket 3 jam terakhir
            case "liveTicket":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
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
                    where Ds_transaction.trans_createdate >= DATEADD(HOUR, -3, GETDATE()) and type_casename = '$url[1]'
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
                }else{
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
                    where Ds_transaction.trans_createdate >= DATEADD(HOUR, -3, GETDATE())
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
                }
                echo json_encode($json,true);
                break;
    
            //get data tiket pertahun
            case "call-data":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
                if($url[1]!="" && $url[2]!=""){
                    $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                    FROM Ds_history
                    JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                    where Ds_history.history_createdate like '%$url[1]%' and status_name = '$url[2]'
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
                }else{
                    $json=array("Metadata"=>402);
                }
                echo json_encode($json,true);
                break;


            //get data tiket hari ini
            case "liveCall-1d":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
                if($url[1]!=""){
                    $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                    FROM Ds_history
                    JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                    where Ds_history.history_createdate >= DATEADD(day, -1, GETDATE()) and status_name = '$url[1]'
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
                }else{
                    $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                    FROM Ds_history
                    JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                    where Ds_history.history_createdate >= DATEADD(day, -1, GETDATE())
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
                }
                echo json_encode($json,true);
                break;

            //get data tiket 3jam terakhir
            case "liveCall":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
                if($url[1]!=""){
                    $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                    FROM Ds_history
                    JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                    where Ds_history.history_createdate >= DATEADD(HOUR, -3, GETDATE()) and status_name = '$url[1]'
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
                }else{
                    $sql = " SELECT Ds_history.ticket_code, Ds_history.history_inumber, Ds_Status.status_name, Ds_history.history_createdate
                    FROM Ds_history
                    JOIN Ds_Status ON Ds_history.status_id = Ds_Status.status_id
                    where Ds_history.history_createdate >= DATEADD(HOUR, -3, GETDATE())
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
                }
                echo json_encode($json,true);
                break;
    }
}
