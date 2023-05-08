<?php
/**
 *  Copyright (C) 2022 fhcomplete.net
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
 *
 */

require_once(dirname(__FILE__).'/../config.inc.php');
require_once(dirname(__FILE__).'/signatur.class.php');
require_once(dirname(__FILE__).'/Signature.php');

/**
 * Controller to handle Connection beween Rest Service and Signature Class
 */
class RestController
{
	const ERROR = 1;
	const SUCCESS = 0;

	// -------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Sign a Document
	 *
	 * @param object $data Object with document, profile and user.
	 * @return array with success an base64 encoded Document on success or errormessage on failure.
	 * @url POST /sign
	 */
	public function sign($data)
	{
		if (!isset($data->document))
		{
		         return $this->_error('Document missing');
		}
		if (!isset($data->profile))
		{
		         return $this->_error('Profile missing');
		}
		if (!isset($data->user))
		{
			return $this->_error('User missing');
		}

		$signatur = new signatur($data->user, 'api/'.$_SERVER['PHP_AUTH_USER']);
		if ($signatur->sign($data->document, $data->profile))
		{
			return $this->_success($signatur->signed_document_b64);
		}
		else
		{
			return $this->_error($signatur->errormsg);
		}
	}

	/**
	 * Lists the signatures inside the posted file
	 * @url POST /list
	 */
	public function list($data)
	{
		try
		{
			// Get the list of signatures, if fine then return a success
			return $this->_success(Signature::list($data));
		}
		catch(Exception $e)
		{
			// Otherwise return an error
			return $this->_error($e->getMessage());
		}
	}

	// -------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *
	 */
	private function _error($retval = null, $code = null)
	{
		return $this->_createReturnObject($code, self::ERROR, $retval);
	}

	/**
	 *
	 */
	private function _success($retval = null, $code = null)
	{
		return $this->_createReturnObject($code, self::SUCCESS, $retval);
	}

	/**
	 *
	 */
	private function _createReturnObject($code, $error, $retval)
	{
		$returnObject = new stdClass();
		$returnObject->code = $code;
		$returnObject->error = $error;
		$returnObject->retval = $retval;
		
		return $returnObject;
	}
}

