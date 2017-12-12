<?php

ini_set('display_errors','1');
error_reporting(E_ALL);

/**
 * Path to Signature Service
 * DEFAULT: http://localhost:8080/pdf-as-web/api/v1/sign
 */
define('SIGNATURE_SERVER_URL','http://localhost:8080/pdf-as-web/api/v1/sign');

/**
 * Disables SSL Verification (only for testing!)
 * DEFAULT: true
 */
define('SIGNATURE_SERVER_VERIFY_SSL',true);

/**
 * Log Folder
 * DEFAULT: /var/signature/log/
 */
define('SIGNATURE_LOG_PATH','/var/signature/log/');

/**
 * Archive Folder
 * DEFAULT: /var/signature/archive/
 */
define('SIGNATURE_ARCHIVE_PATH','/var/signature/archive/');

/**
 * Available Profiles
 */
$SIGNATURE_PROFILES = array(
	'AMTSSIGNATURBLOCK_DE' => 'Signaturblock DE',
	'AMTSSIGNATURBLOCK_DE_SMALL' => 'Signaturblock DE Small'
);
