<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require __DIR__ . '/../Header.php';

$inputFileName = __DIR__ . '/ftpUpload/employeeaccesshawkinge.xls';	
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xls");
$spreadsheet = $reader->load($inputFileName);
$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xls");


$inputFileName = "resave.xlsx";
$writer->save($inputFileName);

$helper->log('Loading file ' . pathinfo($inputFileName, PATHINFO_BASENAME) . ' using IOFactory to identify the format');
$spreadsheet = IOFactory::load($inputFileName);
$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

$workDetails = [];
$count = 0;
foreach ($sheetData as $key =>$sheet) { 
	if($count > 10){
		$sheet = array_filter($sheet);

		foreach (array_chunk($sheet, 5, true) as  $value) {
    		array_push($workDetails, array(
							"Surname"			=>!is_null(explode(",",$value["C"])[0] && isset(explode(",",$value["C"])[0])) ? trim(explode(",",$value["C"])[0]) : "",
							"Forename"			=>!is_null(explode(",",$value["C"])[1] && isset(explode(",",$value["C"])[1])) ? trim(explode(",",$value["C"])[1]) : "",
							"Badge" 			=>isset($value["G"]) ? $value["G"] : "", 
							"Clock" 			=>isset($value["I"]) ? $value["I"] : "", 
							"Access Level"		=>isset($value["L"]) ? $value["L"] : "", 
							"Terminal Group" 	=>isset($value["O"]) ? $value["O"] : ""
							));
						}	
					}	
   
	$count ++;
}


$filteredArray = removeOldEmployees($workDetails);

$inserts = [];
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

//$hashPassword = md5('125Password');
$hashPassword = password_hash("125Password", PASSWORD_DEFAULT);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}else{
	echo "SQL import....";
	// prepare and bind
		$stmt = $conn->prepare("INSERT IGNORE INTO GcUsers (Clock, Badge, firstname, surname, hash) VALUES (?, ?, ?, ?, ?)");
		if($stmt){
			$stmt->bind_param("iisss",$clock, $badge, $firstname, $lastname, $password);

		}else{
			exit("There is a problem preparing mysql statement");
		}
		

	foreach ($filteredArray as $user) 
		{
			$clock 		= str_replace(',', '', $user["Clock"]);
			$badge 		= $user["Badge"];
			$firstname 	= $user['Forename'];
			$lastname	= $user['Surname'];
			$password   = $hashPassword;
			$inserts[] = $stmt->execute();

		}
		echo "<br />";
		echo  count($inserts) . " New records successfully created..";


	$stmt->close();

	$stmt = $conn->prepare("INSERT IGNORE INTO RoleJunction (UserId, RoleId) VALUES (?, ?)");
		if($stmt){
			$stmt->bind_param("ii",$clock, $roleId);
		}else{
			exit("There is a problem preparing mysql statement");
		}

		$sql = "SELECT * FROM GcUsers";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
   			// output data of each row
		    while($row = $result->fetch_assoc()) {

		        $clock 			= $row["Id"];
				$roleId 		= 3;
				$idInserts[] 	= $stmt->execute();
		    }
		} else {
			echo "<br />";
		    echo "0 results";
		}

		echo "<br />";
		echo  count($inserts) . " Id's inserted ok..";

$stmt->close();
$conn->close();
}

