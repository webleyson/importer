<?php
/**
 * Header file.
 */

$servername = "";
$username = "";
$password = "";
$dbname = "";



function dd($args){
    echo "<pre>";
    var_dump($args);
    echo "</pre>";
    exit();
}


function removeOldEmployees($existingArray){

    foreach ($existingArray as $key => $value) {
        if(strpos($value["Forename"], '*') !== false || strlen($value["Forename"]) <=0 ){
            unset($existingArray[$key]);
        }
    }

    return $existingArray;
}

use PhpOffice\PhpSpreadsheet\Helper\Sample;

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
error_reporting(0); // Disable all errors.


require_once __DIR__ . '/../src/Bootstrap.php';

$helper = new Sample();

// Return to the caller script when runs by CLI
if ($helper->isCli()) {
    return;
}
?>

        <?php
        echo $helper->getPageHeading();
