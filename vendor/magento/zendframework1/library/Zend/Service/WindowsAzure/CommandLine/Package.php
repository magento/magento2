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
 */

/** @see Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/**
* @see Zend_Service_Console_Command
*/
#require_once 'Zend/Service/Console/Command.php';

/**
 * Package commands
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure_CommandLine
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @command-handler package
 * @command-handler-description Windows Azure Package commands
 * @command-handler-header Windows Azure SDK for PHP
 * @command-handler-header Copyright (c) 2009 - 2011, RealDolmen (http://www.realdolmen.com)
 * @command-handler-footer
 * @command-handler-footer All commands support the --ConfigurationFile or -F parameter.
 * @command-handler-footer The parameter file is a simple INI file carrying one parameter
 * @command-handler-footer value per line. It accepts the same parameters as one can
 * @command-handler-footer use from the command line command.
 */
class Zend_Service_WindowsAzure_CommandLine_Package
	extends Zend_Service_Console_Command
{
	/**
	 * Scaffolds a Windows Azure project structure which can be customized before packaging.
	 *
	 * @command-name Scaffold
	 * @command-description Scaffolds a Windows Azure project structure which can be customized before packaging.
	 *
	 * @command-parameter-for $path Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Path|-p Required. The path to create the Windows Azure project structure.
	 * @command-parameter-for $scaffolder Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Scaffolder|-s Optional. The path to the scaffolder to use. Defaults to Scaffolders/DefaultScaffolder.phar
	 */
	public function scaffoldCommand($path, $scaffolder, $argv)
	{
		// Default parameter value
		if ($scaffolder == '') {
			$scaffolder = dirname(__FILE__) . '/Scaffolders/DefaultScaffolder.phar';
		}
		$scaffolder = realpath($scaffolder);

		// Verify scaffolder
		if (!is_file($scaffolder)) {
			#require_once 'Zend/Service/Console/Exception.php';
			throw new Zend_Service_Console_Exception('Could not locate the given scaffolder: ' . $scaffolder);
		}

		// Include scaffolder
		$archive = new Phar($scaffolder);
		include $scaffolder;
		if (!class_exists('Scaffolder')) {
			#require_once 'Zend/Service/Console/Exception.php';
			throw new Zend_Service_Console_Exception('Could not locate a class named Scaffolder in the given scaffolder: ' . $scaffolder . '. Make sure the scaffolder package contains a file named index.php and contains a class named Scaffolder.');
		}

		// Cleanup $argv
		$options = array();
		foreach ($argv as $arg) {
			list($key, $value) = explode(':', $arg, 2);
			while (substr($key, 0, 1) == '-') {
				$key = substr($key, 1);
			}
			$options[$key] = $value;
		}

		// Run scaffolder
		$scaffolderInstance = new Scaffolder();
		$scaffolderInstance->invoke($archive, $path, $options);
	}


	/**
	 * Packages a Windows Azure project structure.
	 *
	 * @command-name Create
	 * @command-description Packages a Windows Azure project structure.
	 *
	 * @command-parameter-for $path Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Path|-p Required. The path to package.
	 * @command-parameter-for $runDevFabric Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --RunDevFabric|-dev Required. Switch. Run and deploy to the Windows Azure development fabric.
	 * @command-parameter-for $outputPath Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --OutputPath|-out Optional. The output path for the resulting package.
	 */
	public function createPackageCommand($path, $runDevFabric, $outputPath)
	{
		// Create output paths
		if ($outputPath == '') {
			$outputPath = realpath($path . '/../');
		}
		$packageOut = $outputPath . '/' . basename($path) . '.cspkg';

		// Find Windows Azure SDK bin folder
		$windowsAzureSdkFolderCandidates = array_merge(
			isset($_SERVER['ProgramFiles']) ? glob($_SERVER['ProgramFiles'] . '\Windows Azure SDK\*\bin', GLOB_NOSORT) : array(),
			isset($_SERVER['ProgramFiles']) ? glob($_SERVER['ProgramFiles(x86)'] . '\Windows Azure SDK\*\bin', GLOB_NOSORT) : array(),
			isset($_SERVER['ProgramFiles']) ? glob($_SERVER['ProgramW6432'] . '\Windows Azure SDK\*\bin', GLOB_NOSORT) : array()
		);
		if (count($windowsAzureSdkFolderCandidates) == 0) {
			throw new Zend_Service_Console_Exception('Could not locate Windows Azure SDK for PHP.');
		}
		$cspack = '"' . $windowsAzureSdkFolderCandidates[0] . '\cspack.exe' . '"';
		$csrun = '"' . $windowsAzureSdkFolderCandidates[0] . '\csrun.exe' . '"';

		// Open the ServiceDefinition.csdef file and check for role paths
		$serviceDefinitionFile = $path . '/ServiceDefinition.csdef';
		if (!file_exists($serviceDefinitionFile)) {
			#require_once 'Zend/Service/Console/Exception.php';
			throw new Zend_Service_Console_Exception('Could not locate ServiceDefinition.csdef at ' . $serviceDefinitionFile . '.');
		}
		$serviceDefinition = Zend_Xml_Security::scanFile($serviceDefinitionFile);
		$xmlRoles = array();
		if ($serviceDefinition->WebRole) {
			if (count($serviceDefinition->WebRole) > 1) {
	    		$xmlRoles = array_merge($xmlRoles, $serviceDefinition->WebRole);
			} else {
	    		$xmlRoles = array_merge($xmlRoles, array($serviceDefinition->WebRole));
	    	}
		}
		if ($serviceDefinition->WorkerRole) {
			if (count($serviceDefinition->WorkerRole) > 1) {
	    		$xmlRoles = array_merge($xmlRoles, $serviceDefinition->WorkerRole);
			} else {
	    		$xmlRoles = array_merge($xmlRoles, array($serviceDefinition->WorkerRole));
	    	}
		}

		// Build '/role:' command parameter
		$roleArgs = array();
		foreach ($xmlRoles as $xmlRole) {
			if ($xmlRole["name"]) {
				$roleArgs[] = '/role:' . $xmlRole["name"] . ';' . realpath($path . '/' . $xmlRole["name"]);
			}
		}

		// Build command
		$command = $cspack;
		$args = array(
			$path . '\ServiceDefinition.csdef',
			implode(' ', $roleArgs),
			'/out:' . $packageOut
		);
		if ($runDevFabric) {
			$args[] = '/copyOnly';
		}
		passthru($command . ' ' . implode(' ', $args));

		// Can we copy a configuration file?
		$serviceConfigurationFile = $path . '/ServiceConfiguration.cscfg';
		$serviceConfigurationFileOut = $outputPath . '/ServiceConfiguration.cscfg';
		if (file_exists($serviceConfigurationFile) && !file_exists($serviceConfigurationFileOut)) {
			copy($serviceConfigurationFile, $serviceConfigurationFileOut);
		}

		// Do we have to start the development fabric?
		if ($runDevFabric) {
			passthru($csrun . ' /devstore:start /devfabric:start');
			passthru($csrun . ' /removeAll');
			passthru($csrun . ' /run:"' . $packageOut . ';' . $serviceConfigurationFileOut . '" /launchBrowser');
		}
	}

	/**
	 * Creates a scaffolder from a given path.
	 *
	 * @command-name CreateScaffolder
	 * @command-description Creates a scaffolder from a given path.
	 *
	 * @command-parameter-for $rootPath Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Path|-p Required. The path to package into a scaffolder.
	 * @command-parameter-for $scaffolderFile Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --OutFile|-out Required. The filename of the scaffolder.
	 */
	public function createScaffolderCommand($rootPath, $scaffolderFile)
	{
		$archive = new Phar($scaffolderFile);
		$archive->buildFromIterator(
			new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(realpath($rootPath))),
		realpath($rootPath));
	}
}
Zend_Service_Console_Command::bootstrap($_SERVER['argv']);
