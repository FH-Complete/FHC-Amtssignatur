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
 */

/**
 *
 */
class Signature
{
	const ERR = 'err';
	const MSG = 'msg';

	const SIGN_HEADER = 'Signature #';

	/**
	 *
	 */
	public static function list($postedData)
	{
		$pdfSigOutput = array();
		$decodedFileContent = false;

		// Check if the parameters filename and content are provided
		if ($postedData == null || !isset($postedData->filename) || !isset($postedData->content))
		{
			throw new Exception('Missing parameters filename and/or content');
		}

		// Check that the parameters filename and content are valid
		if (trim($postedData->filename) == '' || trim($postedData->content) == '')
		{
			throw new Exception('Parameters filename and/or content are empty strings');
		}

		// Try to decode the base64 content
		$decodedFileContent = base64_decode($postedData->content);
		if ($decodedFileContent === false)
		{
			throw new Exception('The content parameter is not base64 encoded');
		}

		// Where to place the temporary file
		$inputFileName = sys_get_temp_dir().'/'.$postedData->filename;

		// Write the decoded content of the content parameter into the temporary file
		$resultWrite = file_put_contents($inputFileName, $decodedFileContent);
		if ($resultWrite === false)
		{
			throw new Exception('An error occurred while writing the temporary file');
		}

		// Get the pdf signatures info from the written file
		$resultExec = exec('/usr/bin/pdfsig '.$inputFileName, $pdfSigOutput);
		if ($resultExec === false)
		{
			throw new Exception('An error occurred while running pdfsig');
		}

		// Remove the temporary file
		$resultRm = unlink($inputFileName);
		if ($resultRm == false)
		{
			throw new Exception('An error occurred while deleting the temporary file');
		}

		array_shift($pdfSigOutput); // remove the first line
		$resultList = array(); // array of signatures
		$signArray = array(); // array of lines for a signatures

		// For each output line of the pdfsig command
		foreach ($pdfSigOutput as $line)
		{
			// If it is the header of the signature
			if (stripos($line, self::SIGN_HEADER) === 0)
			{
				// And there are line already copied
				if (is_array($signArray) && count($signArray) > 0)
				{
					$resultList[] = $signArray; // copy such lines as one element of the array of signatures
					$signArray = array(); // empty the array of lines for a signatures
				}
			}
			else // copy the lines trimming them and removing not useful chars
			{
				$signArray[] = substr(trim($line), 2);
			}
		}

		// Copy the last lines if they are there
		if (is_array($signArray) && count($signArray) > 0) $resultList[] = $signArray;

		return $resultList;
	}
}

