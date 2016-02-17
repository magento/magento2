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
 * Storage commands
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure_CommandLine
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @command-handler storage
 * @command-handler-description Windows Azure Storage commands
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
class Zend_Service_WindowsAzure_CommandLine_Storage
	extends Zend_Service_Console_Command
{
	/**
	 * List storage accounts for a specified subscription.
	 *
	 * @command-name ListAccounts
	 * @command-description List storage accounts for a specified subscription.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-example List storage accounts for subscription:
	 * @command-example ListAccounts -sid:"<your_subscription_id>" -cert:"mycert.pem"
	 */
	public function listAccountsCommand($subscriptionId, $certificate, $certificatePassphrase)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->listStorageAccounts();

		if (count($result) == 0) {
			echo 'No data to display.';
		}
		foreach ($result as $object) {
			$this->_displayObjectInformation($object, array('ServiceName', 'Url'));
		}
	}

	/**
	 * Get storage account properties.
	 *
	 * @command-name GetProperties
	 * @command-description Get storage account properties.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $accountName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --AccountName Required. The storage account name to operate on.
	 * @command-example Get storage account properties for account "phptest":
	 * @command-example GetProperties -sid:"<your_subscription_id>" -cert:"mycert.pem"
	 * @command-example --AccountName:"phptest"
	 */
	public function getPropertiesCommand($subscriptionId, $certificate, $certificatePassphrase, $accountName)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->getStorageAccountProperties($accountName);

		$this->_displayObjectInformation($result, array('ServiceName', 'Label', 'AffinityGroup', 'Location'));
	}

	/**
	 * Get storage account property.
	 *
	 * @command-name GetProperty
	 * @command-description Get storage account property.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $accountName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --AccountName Required. The storage account name to operate on.
	 * @command-parameter-for $property Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Property|-prop Required. The property to retrieve for the storage account.
	 * @command-example Get storage account property "Url" for account "phptest":
	 * @command-example GetProperty -sid:"<your_subscription_id>" -cert:"mycert.pem"
	 * @command-example --AccountName:"phptest" --Property:Url
	 */
	public function getPropertyCommand($subscriptionId, $certificate, $certificatePassphrase, $accountName, $property)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->getStorageAccountProperties($accountName);

		printf("%s\r\n", $result->$property);
	}

	/**
	 * Get storage account keys.
	 *
	 * @command-name GetKeys
	 * @command-description Get storage account keys.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $accountName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --AccountName Required. The storage account name to operate on.
	 * @command-example Get storage account keys for account "phptest":
	 * @command-example GetKeys -sid:"<your_subscription_id>" -cert:"mycert.pem"
	 * @command-example --AccountName:"phptest"
	 */
	public function getKeysCommand($subscriptionId, $certificate, $certificatePassphrase, $accountName)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->getStorageAccountKeys($accountName);

		$this->_displayObjectInformation((object)array('Key' => 'primary', 'Value' => $result[0]), array('Key', 'Value'));
		$this->_displayObjectInformation((object)array('Key' => 'secondary', 'Value' => $result[1]), array('Key', 'Value'));
	}

	/**
	 * Get storage account key.
	 *
	 * @command-name GetKey
	 * @command-description Get storage account key.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $accountName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --AccountName Required. The storage account name to operate on.
	 * @command-parameter-for $key Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Key|-k Optional. Specifies the key to regenerate (primary|secondary). If omitted, primary key is used as the default.
	 * @command-example Get primary storage account key for account "phptest":
	 * @command-example GetKey -sid:"<your_subscription_id>" -cert:"mycert.pem"
	 * @command-example --AccountName:"phptest" -Key:primary
	 */
	public function getKeyCommand($subscriptionId, $certificate, $certificatePassphrase, $accountName, $key = 'primary')
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->getStorageAccountKeys($accountName);

		if (strtolower($key) == 'secondary') {
			printf("%s\r\n", $result[1]);
		}
		printf("%s\r\n", $result[0]);
	}

	/**
	 * Regenerate storage account keys.
	 *
	 * @command-name RegenerateKeys
	 * @command-description Regenerate storage account keys.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $accountName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --AccountName Required. The storage account name to operate on.
	 * @command-parameter-for $key Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Key|-k Optional. Specifies the key to regenerate (primary|secondary). If omitted, primary key is used as the default.
	 * @command-parameter-for $waitForOperation Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --WaitFor|-w Optional. Wait for the operation to complete?
	 * @command-example Regenerate secondary key for account "phptest":
	 * @command-example RegenerateKeys -sid:"<your_subscription_id>" -cert:"mycert.pem"
	 * @command-example --AccountName:"phptest" -Key:secondary
	 */
	public function regenerateKeysCommand($subscriptionId, $certificate, $certificatePassphrase, $accountName, $key = 'primary', $waitForOperation = false)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$client->regenerateStorageAccountKey($accountName, $key);
		if ($waitForOperation) {
			$client->waitForOperation();
		}
		echo $client->getLastRequestId();
	}
}

Zend_Service_Console_Command::bootstrap($_SERVER['argv']);
