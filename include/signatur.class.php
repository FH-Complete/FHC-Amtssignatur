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
/**
 * Class to Communitcate with the signing Server,
 * Log and Archive the Documents
 */
class signatur
{
	public $signed_document_b64;
	public $errormsg;
	public $filename_signed;
	private $sign_user;
	private $sign_type;

	/**
	 * Constructor
	 * @param $user Username of the final User who wants to sign the document.
	 * @param $type Request source with User api/fhcomplete | manual/oesi.
	 */
	public function __construct($user, $type)
	{
		$this->sign_user = $user;
		$this->sign_type = $type;
	}

	/**
	 * Creates Signature
	 * @param string $content base64 encoded Document
	 * @param string $profile Name of the Signature Profile
	 * @return boolean true if success, false in error
	 */
	public function sign($content, $profile)
	{
		$data = new stdClass();
		$data->input = $content;
		$data->connector = 'jks';
		$data->profile = $profile;
		$data->requestID = uniqid();

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, SIGNATURE_SERVER_URL);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 7);
		curl_setopt($ch, CURLOPT_USERAGENT , "FH-Complete");

		if(defined('SIGNATURE_SERVER_VERIFY_SSL') && SIGNATURE_SERVER_VERIFY_SSL === false)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		$data_string = json_encode($data,JSON_FORCE_OBJECT);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
		);

		$result = curl_exec($ch);
		if(curl_errno($ch))
		{
			curl_close($ch);
			$this->errormsg = 'Signaturserver ist derzeit nicht erreichbar';
			$returnvalue = false;
		}
		else
		{
			curl_close($ch);

			if($decoded = json_decode($result))
			{
				$this->signed_document_b64 = $decoded->output;
				$returnvalue = true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Aufbringen der Signatur:'.print_r($result, true);
				$returnvalue = false;
			}
		}

		if($returnvalue === true)
		{
			$this->archive();
		}
		$this->log($this->errormsg);
		return $returnvalue;
	}

	/**
	 * Creates a Log Entry
	 * @param $user
	 * @param $document
	 * @param $errormsg
	 * @return boolean true if ok, false on error
	 */
	public function log($errormsg = null)
	{
		$file = SIGNATURE_LOG_PATH.'log_'.date('Y.m').'.log';

		$message = "\n".date('Y-m-d H:i:s').': ';
		$message .= $this->sign_user.' ';
		$message .= $this->sign_type.' ';
		$message .= $this->filename_signed;

		if (is_null($errormsg))
			$message .= ' success';
		else
			$message .= ' '.$errormsg;

		if (file_put_contents($file, $message, FILE_APPEND) !== false)
			return true;
		else
			return false;
	}

	/**
	 * Saves the signed Document in the filesystem
	 *
	 * @return booelan true if ok, false on error
	 */
	public function archive()
	{
		$path = date('Y-m').'/';
		if (!file_exists(SIGNATURE_ARCHIVE_PATH.$path))
			mkdir(SIGNATURE_ARCHIVE_PATH.$path);

		$this->filename_signed = $path.date('Y-m-d_H:i:s').'_'.uniqid().'.pdf';
		$ret = file_put_contents(
			SIGNATURE_ARCHIVE_PATH.$this->filename_signed,
			base64_decode($this->signed_document_b64)
		);

		if ($ret !== false)
			return true;
		else
			return false;
	}
}
