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

/**
* @see Zend_Service_Console_Command
*/
#require_once 'Zend/Service/Console/Command.php';

/**
* @see Zend_Service_WindowsAzure_Management_Client
*/
#require_once 'Zend/Service/WindowsAzure/Management/Client.php';

/**
 * Asynchronous Operation commands
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure_CommandLine
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @command-handler getasynchronousoperation
 * @command-handler-description Windows Azure Asynchronous Operation commands
 * @command-handler-header Windows Azure SDK for PHP
 * @command-handler-header Copyright (c) 2009 - 2011, RealDolmen (http://www.realdolmen.com)
 * @command-handler-footer Note: Parameters that are common across all commands can be stored
 * @command-handler-footer in two dedicated environment variables.
 * @command-handler-footer - SubscriptionId: The Windows Azure Subscription Id to operate on.
 * @command-handler-footer - Certificate The Windows Azure .cer Management Certificate.
 * @command-handler-footer
 * @command-handler-footer All commands support the --ConfigurationFile or -F parameter.
 * @command-handler-footer The parameter file is a simple INI file carrying one parameter
 * @command-handler-footer value per line. It accepts the same parameters as one can
 * @command-handler-footer use from the command line command.
 */
class Zend_Service_WindowsAzure_CommandLine_GetAsynchronousOperation
	extends Zend_Service_Console_Command
{
	/**
	 * Get information for a specific asynchronous request.
	 *
	 * @command-name GetInfo
	 * @command-description Get information for a specific asynchronous request.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $requestId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --RequestId|-r Required. The value returned by a call that starts an asynchronous operation to monitor.
	 * @command-example Get information for a specific asynchronous operation:
	 * @command-example GetInfo -sid:"<your_subscription_id>" -cert:"mycert.pem" -r:"dab87a4b70e94a36805f5af2d20fc593"
	 */
	public function getInfoCommand($subscriptionId, $certificate, $certificatePassphrase, $requestId)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->getOperationStatus($requestId);

		$this->_displayObjectInformation($result, array('ID', 'Status', 'ErrorMessage'));
	}

	/**
	 * Wait for a specific asynchronous request to complete.
	 *
	 * @command-name WaitFor
	 * @command-description Wait for a specific asynchronous request to complete.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $requestId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --RequestId|-r Required. The value returned by a call that starts an asynchronous operation to monitor.
	 * @command-parameter-for $interval Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Interval|-i Optional. The interval between two status checks (in milliseconds).
	 * @command-example Wait for a specific asynchronous operation:
	 * @command-example WaitFor -sid:"<your_subscription_id>" -cert:"mycert.pem" -r:"dab87a4b70e94a36805f5af2d20fc593"
	 */
	public function waitForCommand($subscriptionId, $certificate, $certificatePassphrase, $requestId, $interval = 250)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$client->waitForOperation($requestId, $interval);
	}
}

Zend_Service_Console_Command::bootstrap($_SERVER['argv']);
