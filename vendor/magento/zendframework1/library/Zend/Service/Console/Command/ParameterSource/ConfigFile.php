<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service_Console
 * @subpackage Exception
 * @version    $Id$
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @copyright  Copyright (c) 2009 - 2011, RealDolmen (http://www.realdolmen.com)
 * @license    http://phpazure.codeplex.com/license
 */

/**
* @see Zend_Service_Console_Command_ParameterSource_ParameterSourceInterface
*/
#require_once 'Zend/Service/Console/Command/ParameterSource/ParameterSourceInterface.php';

/**
 * @category   Zend
 * @package    Zend_Service_Console
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @copyright  Copyright (c) 2009 - 2011, RealDolmen (http://www.realdolmen.com)
 * @license    http://phpazure.codeplex.com/license
 */
class Zend_Service_Console_Command_ParameterSource_ConfigFile
	implements Zend_Service_Console_Command_ParameterSource_ParameterSourceInterface
{
	/**
	 * Get value for a named parameter.
	 *
	 * @param mixed $parameter Parameter to get a value for
	 * @param array $argv Argument values passed to the script when run in console.
	 * @return mixed
	 */
	public function getValueForParameter($parameter, $argv = array())
	{
		// Configuration file path
		$configurationFilePath = null;

		// Check if a path to a configuration file is specified
		foreach ($argv as $parameterInput) {
			$parameterInput = explode('=', $parameterInput, 2);

			if (strtolower($parameterInput[0]) == '--configfile' || strtolower($parameterInput[0]) == '-f') {
				if (!isset($parameterInput[1])) {
					#require_once 'Zend/Service/Console/Exception.php';
					throw new Zend_Service_Console_Exception("No path to a configuration file is given. Specify the path using the --ConfigFile or -F switch.");
				}
				$configurationFilePath = $parameterInput[1];
				break;
			}
		}

		// Value given?
		if (is_null($configurationFilePath)) {
			return null;
		}
		if (!file_exists($configurationFilePath)) {
			#require_once 'Zend/Service/Console/Exception.php';
			throw new Zend_Service_Console_Exception("Invalid configuration file given. Specify the correct path using the --ConfigFile or -F switch.");
		}

		// Parse values
		$iniValues = parse_ini_file($configurationFilePath);

		// Default value
		$parameterValue = null;

		// Loop aliases
		foreach ($parameter->aliases as $alias) {
			if (array_key_exists($alias, $iniValues)) {
				$parameterValue = $iniValues[$alias]; break;
			} else if (array_key_exists(strtolower($alias), $iniValues)) {
				$parameterValue = $iniValues[strtolower($alias)]; break;
			} else if (array_key_exists(str_replace('-', '', $alias), $iniValues)) {
				$parameterValue = $iniValues[str_replace('-', '', $alias)]; break;
			} else if (array_key_exists(strtolower(str_replace('-', '', $alias)), $iniValues)) {
				$parameterValue = $iniValues[strtolower(str_replace('-', '', $alias))]; break;
			}
		}

		if (strtolower($parameterValue) == 'true') {
			$parameterValue = true;
		} else if (strtolower($parameterValue) == 'false') {
			$parameterValue = false;
		}

		// Done!
		return $parameterValue;
	}
}
