<?php
// Distributed by license from CardinalCommerce Corporation
/////////////////////////////////////////////////////////////////////////////////////////////
//  CardinalCommerce (http://www.cardinalcommerce.com)
//  CentinelErrors.php
//  Version 1.2 02/17/2005
//
//	Usage
//		The Error Numbers and Descriptions are centralized and referenced by the CentinelClient.php.
//
/////////////////////////////////////////////////////////////////////////////////////////////

defined('CENTINEL_ERROR_CODE_8000') ? : define("CENTINEL_ERROR_CODE_8000", "8000");
defined('CENTINEL_ERROR_CODE_8000_DESC') ?
    : define('CENTINEL_ERROR_CODE_8000_DESC', 'Protocol Not Recogonized, must be http:// or https://');
defined('CENTINEL_ERROR_CODE_8010') ? : define('CENTINEL_ERROR_CODE_8010', "8010");
defined('CENTINEL_ERROR_CODE_8010_DESC') ?
    : define('CENTINEL_ERROR_CODE_8010_DESC', 'Unable to Communicate with MAPS Server');
defined('CENTINEL_ERROR_CODE_8020') ? : define('CENTINEL_ERROR_CODE_8020', "8020");
defined('CENTINEL_ERROR_CODE_8020_DESC') ?
    : define('CENTINEL_ERROR_CODE_8020_DESC', 'Error Parsing XML Response');
defined('CENTINEL_ERROR_CODE_8030') ? : define('CENTINEL_ERROR_CODE_8030', "8030");
defined('CENTINEL_ERROR_CODE_8030_DESC') ?
    : define('CENTINEL_ERROR_CODE_8030_DESC', 'Communication Timeout Encountered');
