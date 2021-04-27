<?php



header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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
        case "laporan-tiket":
            $header = apache_request_headers();
            $konten = trim(file_get_contents("php://input"));
            $decode = json_decode($konten, true);
            if($url[1]!=""){
                $sql = "SELECT Ds_transaction.ticket_code, Ds_history.history_fullname,Ds_history.history_inumber, Ds_transaction.trans_desc, Ds_transaction.trans_addr,DsCase_Subcategory.subcategory_casename, Ds_kecamatan.kecamatan_name,
                Ds_transaction.trans_createdate, Ds_transaction.trans_victimstat
                FROM Ds_transaction
                JOIN Ds_history ON Ds_transaction.ticket_code = Ds_history.ticket_code
                JOIN DsCase_Subcategory ON Ds_transaction.subcategory_caseid = DsCase_Subcategory.subcategory_caseid
                JOIN Ds_kecamatan ON Ds_transaction.kecamatan_id = Ds_kecamatan.kecamatan_id
                where Ds_transaction.trans_createdate like '%$url[1]%'
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
                            'Nama Pelapor'=> $row ["history_fullname"],
                            'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                            'Masalah' => $row["trans_desc"],
                            'Alamat Lengkap' => $row["trans_addr"],
                            'kategori' => $row["subcategory_casename"],
                            'Kecamatan' => $row["kecamatan_name"],
                            'Tanggal' => $row["trans_createdate"],
                            'Status' => $row["trans_victimstat"]
                            ];
                            $i = $i + 1;
                    }
                } while (sqlsrv_next_result($result));
            }else{
                $json=array("Metadata"=>402);
            }
            echo json_encode($json,true);
            break;

            // get data tiket hari ini
            case "tiket":
                $header = apache_request_headers();
                $konten = trim(file_get_contents("php://input"));
                $decode = json_decode($konten, true);
               
                    $sql = "SELECT Ds_transaction.ticket_code, Ds_history.history_fullname,Ds_history.history_inumber, Ds_transaction.trans_desc, Ds_transaction.trans_addr,DsCase_Subcategory.subcategory_casename, Ds_kecamatan.kecamatan_name,
                    Ds_transaction.trans_createdate, Ds_transaction.trans_victimstat
                    FROM Ds_transaction
                    JOIN Ds_history ON Ds_transaction.ticket_code = Ds_history.ticket_code
                    JOIN DsCase_Subcategory ON Ds_transaction.subcategory_caseid = DsCase_Subcategory.subcategory_caseid
                    JOIN Ds_kecamatan ON Ds_transaction.kecamatan_id = Ds_kecamatan.kecamatan_id
                    where Ds_transaction.trans_createdate >= DATEADD(day, -1, GETDATE())
                    order by trans_createdate desc";
    
                    $result = sqlsrv_query( $conn, $sql);
                    if( $result === false ) {
                        echo "Error in executing query.</br>";
                        die;
                    }
                    $json = [];
                    $i = 1;
                    $d = array("triger" => 3);
                    
                    do {
                        while ($row = sqlsrv_fetch_array($result)) {
                            $json[] = [
                                'Nama Pelapor'=> $row ["history_fullname"],
                                'Nomer Pelapor' => substr($row ["history_inumber"],0,7).'xxx',
                                'Masalah' => $row["trans_desc"],
                                'Alamat Lengkap' => $row["trans_addr"],
                                'kategori' => $row["subcategory_casename"],
                                'Kecamatan' => $row["kecamatan_name"],
                               
                                'Tanggal' => $row ["trans_createdate"],
                               

                                'Status' => $row["trans_victimstat"]
                                ];
                                $i = $i + 1;
                        }
                    } while (sqlsrv_next_result($result));
               
                echo json_encode($json,true);
                break;
    }
}
