<?php
/* Copyright (C) 2017 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../config.inc.php');
require_once('../include/signatur.class.php');

if (isset($_GET['download']))
{
	if (preg_match("/^[0-9]{4}-[0-9]{2}\/[0-9a-z\-\_:]+\.pdf$/", $_GET['download']))
	{
		$filename = SIGNATURE_ARCHIVE_PATH.$_GET['download'];
		if (file_exists($filename))
		{
			header("Content-type:application/pdf");
			header("Content-Disposition:attachment;filename=signed.pdf");
			echo file_get_contents($filename);
			exit;
		}
		else
			die('invalid filename');
	}
	else
	{
		die('invalid filename');
	}
}
if (isset($_FILES['file']))
{
	if (isset($_FILES['file']['tmp_name']))
	{
		$pdf = file_get_contents($_FILES['file']['tmp_name']);

		$signatur = new signatur($_SERVER['PHP_AUTH_USER'], 'manual/'.$_SERVER['PHP_AUTH_USER']);
		if ($signatur->sign(base64_encode($pdf), $_POST['profile']))
		{
			header('Content-Type: application/json');
			echo json_encode(array('filename'=>$signatur->filename_signed));
			exit;
		}
		else
		{
			header($_SERVER["SERVER_PROTOCOL"]." 418 Im a Teapot");
			header('Content-Type: application/json');
			echo json_encode(array('error'=>$signatur->errormsg));
			exit;
		}
	}
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Amtssignatur</title>
	<!-- Bootstrap Core CSS -->
	<link href="../vendor/components/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="../css/amtssignatur.css" rel="stylesheet">
	<script src="../vendor/components/jquery/jquery.min.js"></script>
	<script src="../vendor/enyo/dropzone/dist/dropzone.js"></script>

	<script>
	var num_pdfs = 1;
	$( document ).ready(function()
	{
		Dropzone.options.myDropzone = {
		  init: function() {
			this.on("success", function(file, responseText) {
			if (responseText.filename)
			{
				var text = '<br><a href="index.php?download='+responseText.filename+'">';
				text = text+'<img height="20px" src="../images/document-pdf.png" />Dokument '+num_pdfs+'</a>';
				num_pdfs++;
			}
			else
			{
				var text = '<br>Failed to create document';
			}
			$( "#downloadzone" ).append(text);
			}),
			this.on("error", function(file, responseText) {
				if(responseText.error)
					$( "#downloadzone" ).append('<br>'+responseText.error);
				else
					$( "#downloadzone" ).append('<br>Failed:'+responseText);
			}),
			this.on("complete", function(file) {
				this.removeFile(file);
			})
		  }
		};
	});
	</script>
</head>
<body>
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Amtssignatur</a>
			</div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li>
						<a href="index.php">Dokumente signieren</a>
					</li>
					<li>
						<a target="_blank" href="https://www.signaturpruefung.gv.at">Dokumente prüfen</a>
					</li>
					<li>
						<a href="index.php?action=help">Hilfe</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	<div class="container">
	<?php
	if(isset($_GET['action']) && $_GET['action']=='help'):
	?>
	<h4>Manuelle Signatur</h4>
	Für die manuelle Signatur von Dokumenten ziehen Sie ein PDF in den vorgesehenen Bereich.
	Die Datei wird digital signiert und erscheint dann auf der rechten Seite zum Download.
	Beachten Sie das die Signatur eventuell nicht korrekt angezeigt wird
	wenn die PDF-Preview des Browsers verwendet wird.<br>
	<br>
	<h4>API</h4>
	Die Dokumente können über ein REST API signiert werden.<br>
	Dazu wird ein POST Request an die folgende URL geschickt:<br>
	URL: https://signatur.example.com/api/sign<br>
	<br>
	Zur Authentifizierung muss Username und Passwort im Authorization BASIC Header übergeben werden.<br>
	<br>
	Die folgenden JSON Parameter müssen übergeben werden:<br><br>
	document: base64 kodiertes Dokument<br>
	profile: Signaturprofil zB FHTW_GROSS<br>
	user: Enduser für den das Dokument signiert wird<br>

	<?php
	else:
	?>
	<form id="myDropzone" method="POST" enctype="multipart/form-data" action="index.php" class="dropzone">
	<select name="profile">
		<?php
		foreach($SIGNATURE_PROFILES as $profil => $name)
			echo '<option value="'.$profil.'">'.$name.'</option>';
		?>
	</select>
	</form>
	<div id="downloadzone">
	Download der signierten Dokumente:
	</div>
	<div style="clear:both">
	Zum Signieren das Dokument in den Bereich ziehen oder in den Bereich klicken um eine Datei auszuwählen
	</div>
	<?php
	endif;
	?>
	</div>
	<script src="../vendor/components/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
