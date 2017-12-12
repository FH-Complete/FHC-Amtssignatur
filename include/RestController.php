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
  * Controller to handle Connection beween Rest Service and Signature Class
  */
require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/signatur.class.php');

class RestController
{
	/**
	 * Sign a Document
	 *
	 * @param object $data Object with document, profile and user.
	 * @return array with success an base64 encoded Document on success or errormessage on failure.
	 * @url POST /sign
	 */
	public function sign($data)
	{
		if(!isset($data->document))
		{
			return $this->error('Document missing');
		}
		if(!isset($data->profile))
		{
			return $this->error('Profile missing');
		}
		if(!isset($data->user))
		{
			return $this->error('User missing');
		}

		$signatur = new signatur($data->user, 'api/'.$_SERVER['PHP_AUTH_USER']);
		if($signatur->sign($data->document, $data->profile))
		{
			return array('success'=>'true', 'document'=>$signatur->signed_document_b64);
		}
		else
		{
			return $this->error($signatur->errormsg);
		}
	}

	private function error($msg)
	{
		return array('success'=>'false', 'errormsg'=>$msg);
	}
}
