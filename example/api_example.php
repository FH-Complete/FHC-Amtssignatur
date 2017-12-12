<?php
die('Do not call this Script - its an Example');

// Pfad zum Signaturservice
$url = 'https://signatur.example.com/api/sign';
// User für den Zugriff auf die API
$user = 'fhcomplete';
// Passwort für den Zugriff auf die API
$password = 'secret';
// Datei die signiert werden soll
$file = 'test.pdf';

// Load the File
$file_data = file_get_contents($file);

$data = new stdClass();
$data->document = base64_encode($file_data);

// Signatur Profil
$data->profile = 'FHTW_GROSS';

// Username des Endusers der die Signatur angefordert hat
$data->user = 'maxmustermann';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
curl_setopt($ch, CURLOPT_USERAGENT, "FH-Complete");

// SSL Zertifikatsprüfung deaktivieren
// Besser ist es das Zertifikat am Server zu installieren!
//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$data_string = json_encode($data,JSON_FORCE_OBJECT);

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Content-Type: application/json',
	'Content-Length:'.mb_strlen($data_string),
	'Authorization: Basic '.base64_encode($user.":".$password)
	)
);

$result = curl_exec($ch);
if (curl_errno($ch))
{
	curl_close($ch);
	echo 'Signaturserver ist derzeit nicht erreichbar';
	var_dump($result);
}
else
{
	curl_close($ch);
	$resultdata = json_decode($result);

	if (isset($resultdata->success) && $resultdata->success == 'true')
	{
		header("Content-type:application/pdf");
		header("Content-Disposition:attachment;filename=signed.pdf");
		echo base64_decode($resultdata->document);
	}
	else
	{
		echo $resultdata->errormsg;
	}
}
