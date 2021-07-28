<?php
define( "CLIENTAREA", true );

require( "../../../init.php" );
session_start();

require_once __DIR__ . '/lib/Curl.php';
require_once __DIR__ . '/lib/CaseInsensitiveArray.php';
require_once __DIR__ . '/lib/SolusVM.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use SolusVMPlus\SolusVM;

$vserverid = (int) $_GET["vserverid"];

SolusVM::loadLang();

$ca = new WHMCS_ClientArea();
if ( ! $ca->isLoggedIn() ) {
    echo $_LANG['solusvmplus_unauthorized'] . "</body>";
    exit();
}

$uid = $ca->getUserID();

$params = SolusVM::getParamsFromVserviceID( $vserverid, $uid );
if ( ! $params ) {
    $result = array(
        'status'        => 'error',
        'displaystatus' => $_LANG['solusvmplus_vserverNotFound'],
    );
    echo json_encode( $result );
    exit();
}

$solusvm = new SolusVM( $params );

$callArray = array( "vserverid" => $vserverid );

$solusvm->apiCall( 'vserver-infoall', $callArray );
$r = $solusvm->result;

$cparams = $solusvm->clientAreaCalculations( $r );

$solusvm->apiCall( 'vserver-info', $callArray );
$i = $solusvm->result;

$cparams2 = $solusvm->clientAreaCalculations2( $i );


$info = array_merge($cparams,$cparams2);

echo json_encode( $info );
