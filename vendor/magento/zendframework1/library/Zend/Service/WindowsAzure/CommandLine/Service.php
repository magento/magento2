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
 * Service commands
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure_CommandLine
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @command-handler service
 * @command-handler-description Windows Azure Service commands
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
class Zend_Service_WindowsAzure_CommandLine_Service
	extends Zend_Service_Console_Command
{
	/**
	 * List hosted service accounts for a specified subscription.
	 *
	 * @command-name List
	 * @command-description List hosted service accounts for a specified subscription.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-example List hosted service accounts for subscription:
	 * @command-example List -sid:"<your_subscription_id>" -cert:"mycert.pem"
	 */
	public function listCommand($subscriptionId, $certificate, $certificatePassphrase)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->listHostedServices();

		if (count($result) == 0) {
			echo 'No data to display.';
		}
		foreach ($result as $object) {
			$this->_displayObjectInformation($object, array('ServiceName', 'Url'));
		}
	}

	/**
	 * Get hosted service account properties.
	 *
	 * @command-name GetProperties
	 * @command-description Get hosted service account properties.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $serviceName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Name Required. The hosted service account name to operate on.
	 * @command-example Get hosted service account properties for service "phptest":
	 * @command-example GetProperties -sid:"<your_subscription_id>" -cert:"mycert.pem"
	 * @command-example --Name:"phptest"
	 */
	public function getPropertiesCommand($subscriptionId, $certificate, $certificatePassphrase, $serviceName)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->getHostedServiceProperties($serviceName);

		$this->_displayObjectInformation($result, array('ServiceName', 'Label', 'AffinityGroup', 'Location'));
	}

	/**
	 * Get hosted service account property.
	 *
	 * @command-name GetProperty
	 * @command-description Get storage account property.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $serviceName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Name Required. The hosted service account name to operate on.
	 * @command-parameter-for $property Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Property|-prop Required. The property to retrieve for the hosted service account.
	 * @command-example Get hosted service account property "Url" for service "phptest":
	 * @command-example GetProperty -sid:"<your_subscription_id>" -cert:"mycert.pem"
	 * @command-example --Name:"phptest" --Property:Url
	 */
	public function getPropertyCommand($subscriptionId, $certificate, $certificatePassphrase, $serviceName, $property)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->getHostedServiceProperties($serviceName);

		printf("%s\r\n", $result->$property);
	}

	/**
	 * Create hosted service account.
	 *
	 * @command-name Create
	 * @command-description Create hosted service account.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $serviceName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Name Required. The hosted service account name.
	 * @command-parameter-for $label Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Label Required. A label for the hosted service.
	 * @command-parameter-for $description Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Description Optional. A description for the hosted service.
	 * @command-parameter-for $location Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Location Required if AffinityGroup is not specified. The location where the hosted service will be created.
	 * @command-parameter-for $affinityGroup Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --AffinityGroup Required if Location is not specified. The name of an existing affinity group associated with this subscription.
	 * @command-parameter-for $waitForOperation Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --WaitFor|-w Optional. Wait for the operation to complete?
	 * @command-example Create hosted service account in West Europe
	 * @command-example Create -p:"phpazure" --Name:"phptestsdk2" --Label:"phptestsdk2" --Location:"West Europe"
	 */
	public function createCommand($subscriptionId, $certificate, $certificatePassphrase, $serviceName, $label, $description, $location, $affinityGroup, $waitForOperation = false)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$client->createHostedService($serviceName, $label, $description, $location, $affinityGroup);
		if ($waitForOperation) {
			$client->waitForOperation();
		}
		echo $client->getLastRequestId();
	}

	/**
	 * Update hosted service account.
	 *
	 * @command-name Update
	 * @command-description Update hosted service account.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $serviceName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Name Required. The hosted service account name.
	 * @command-parameter-for $label Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Label Required. A label for the hosted service.
	 * @command-parameter-for $description Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Description Optional. A description for the hosted service.
	 * @command-parameter-for $waitForOperation Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --WaitFor|-w Optional. Wait for the operation to complete?
	 * @command-example Update hosted service
	 * @command-example Update -p:"phpazure" --Name:"phptestsdk2" --Label:"New label" --Description:"Some description"
	 */
	public function updateCommand($subscriptionId, $certificate, $certificatePassphrase, $serviceName, $label, $description, $waitForOperation = false)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$client->updateHostedService($serviceName, $label, $description);
		if ($waitForOperation) {
			$client->waitForOperation();
		}
		echo $client->getLastRequestId();
	}

	/**
	 * Delete hosted service account.
	 *
	 * @command-name Delete
	 * @command-description Delete hosted service account.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $serviceName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Name Required. The hosted service account name.
	 * @command-parameter-for $waitForOperation Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --WaitFor|-w Optional. Wait for the operation to complete?
	 * @command-example Delete hosted service
	 * @command-example Delete -p:"phpazure" --Name:"phptestsdk2"
	 */
	public function deleteCommand($subscriptionId, $certificate, $certificatePassphrase, $serviceName, $waitForOperation = false)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$client->deleteHostedService($serviceName);
		if ($waitForOperation) {
			$client->waitForOperation();
		}
		echo $client->getLastRequestId();
	}
}

Zend_Service_Console_Command::bootstrap($_SERVER['argv']);
