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
 * Certificate commands
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure_CommandLine
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @command-handler certificate
 * @command-handler-description Windows Azure Certificate commands
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
class Zend_Service_WindowsAzure_CommandLine_Certificate
	extends Zend_Service_Console_Command
{
	/**
	 * List certificates for a specified hosted service in a specified subscription.
	 *
	 * @command-name List
	 * @command-description List certificates for a specified hosted service in a specified subscription.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $serviceName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --ServiceName|-sn Required. The name of the hosted service.
	 * @command-example List certificates for service name "phptest":
	 * @command-example List -sid:"<your_subscription_id>" -cert:"mycert.pem" -sn:"phptest"
	 */
	public function listCertificatesCommand($subscriptionId, $certificate, $certificatePassphrase, $serviceName)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->listCertificates($serviceName);

		if (count($result) == 0) {
			echo 'No data to display.';
		}
		foreach ($result as $object) {
			$this->_displayObjectInformation($object, array('Thumbprint', 'CertificateUrl', 'ThumbprintAlgorithm'));
		}
	}

	/**
	 * Add a certificate for a specified hosted service in a specified subscription.
	 *
	 * @command-name Add
	 * @command-description Add a certificate for a specified hosted service in a specified subscription.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $serviceName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --ServiceName|-sn Required. The name of the hosted service.
	 * @command-parameter-for $certificateLocation Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --CertificateLocation Required. Path to the .pfx certificate to be added.
	 * @command-parameter-for $certificatePassword Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --CertificatePassword Required. The password for the certificate that will be added.
	 * @command-parameter-for $waitForOperation Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --WaitFor|-w Optional. Wait for the operation to complete?
	 * @command-example Add certificates for service name "phptest":
	 * @command-example Add -sid:"<your_subscription_id>" -cert:"mycert.pem" -sn:"phptest" --CertificateLocation:"cert.pfx" --CertificatePassword:"certpassword"
	 */
	public function addCertificateCommand($subscriptionId, $certificate, $certificatePassphrase, $serviceName, $certificateLocation, $certificatePassword, $waitForOperation = false)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$client->addCertificate($serviceName, $certificateLocation, $certificatePassword, 'pfx');
		if ($waitForOperation) {
			$client->waitForOperation();
		}
		echo $client->getLastRequestId();
	}

	/**
	 * Gets a certificate from a specified hosted service in a specified subscription.
	 *
	 * @command-name Get
	 * @command-description Gets a certificate from a specified hosted service in a specified subscription.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $serviceName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --ServiceName|-sn Required. The name of the hosted service.
	 * @command-parameter-for $thumbprint Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --CertificateThumbprint Required. The certificate thumbprint for which to retrieve the certificate.
	 * @command-parameter-for $algorithm Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --CertificateAlgorithm Required. The certificate's algorithm.
	 * @command-example Get certificate for service name "phptest":
	 * @command-example Get -sid:"<your_subscription_id>" -cert:"mycert.pem" -sn:"phptest" --CertificateThumbprint:"<thumbprint>" --CertificateAlgorithm:"sha1"
	 */
	public function getCertificateCommand($subscriptionId, $certificate, $certificatePassphrase, $serviceName, $thumbprint, $algorithm = "sha1")
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->getCertificate($serviceName, $algorithm, $thumbprint);

		$this->_displayObjectInformation($result, array('Thumbprint', 'CertificateUrl', 'ThumbprintAlgorithm'));
	}

	/**
	 * Gets a certificate property from a specified hosted service in a specified subscription.
	 *
	 * @command-name GetProperty
	 * @command-description Gets a certificate property from a specified hosted service in a specified subscription.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $serviceName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --ServiceName|-sn Required. The name of the hosted service.
	 * @command-parameter-for $thumbprint Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --CertificateThumbprint Required. The certificate thumbprint for which to retrieve the certificate.
	 * @command-parameter-for $algorithm Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --CertificateAlgorithm Required. The certificate's algorithm.
	 * @command-parameter-for $property Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --Property|-prop Required. The property to retrieve for the certificate.
	 * @command-example Get certificate for service name "phptest":
	 * @command-example Get -sid:"<your_subscription_id>" -cert:"mycert.pem" -sn:"phptest" --CertificateThumbprint:"<thumbprint>" --CertificateAlgorithm:"sha1"
	 */
	public function getCertificatePropertyCommand($subscriptionId, $certificate, $certificatePassphrase, $serviceName, $thumbprint, $algorithm = "sha1", $property)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$result = $client->getCertificate($serviceName, $algorithm, $thumbprint);

		printf("%s\r\n", $result->$property);
	}

	/**
	 * Deletes a certificate from a specified hosted service in a specified subscription.
	 *
	 * @command-name Delete
	 * @command-description Deletes a certificate from a specified hosted service in a specified subscription.
	 * @command-parameter-for $subscriptionId Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --SubscriptionId|-sid Required. This is the Windows Azure Subscription Id to operate on.
	 * @command-parameter-for $certificate Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --Certificate|-cert Required. This is the .pem certificate that user has uploaded to Windows Azure subscription as Management Certificate.
	 * @command-parameter-for $certificatePassphrase Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Prompt --Passphrase|-p Required. The certificate passphrase. If not specified, a prompt will be displayed.
	 * @command-parameter-for $serviceName Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile|Zend_Service_Console_Command_ParameterSource_Env --ServiceName|-sn Required. The name of the hosted service.
	 * @command-parameter-for $thumbprint Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --CertificateThumbprint Required. The certificate thumbprint for which to retrieve the certificate.
	 * @command-parameter-for $algorithm Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --CertificateAlgorithm Required. The certificate's algorithm.
	 * @command-parameter-for $waitForOperation Zend_Service_Console_Command_ParameterSource_Argv|Zend_Service_Console_Command_ParameterSource_ConfigFile --WaitFor|-w Optional. Wait for the operation to complete?
	 * @command-example Get certificate for service name "phptest":
	 * @command-example Get -sid:"<your_subscription_id>" -cert:"mycert.pem" -sn:"phptest" --CertificateThumbprint:"<thumbprint>" --CertificateAlgorithm:"sha1"
	 */
	public function deleteCertificateCommand($subscriptionId, $certificate, $certificatePassphrase, $serviceName, $thumbprint, $algorithm = "sha1", $waitForOperation = false)
	{
		$client = new Zend_Service_WindowsAzure_Management_Client($subscriptionId, $certificate, $certificatePassphrase);
		$client->deleteCertificate($serviceName, $algorithm, $thumbprint);
		if ($waitForOperation) {
			$client->waitForOperation();
		}
		echo $client->getLastRequestId();
	}
}

Zend_Service_Console_Command::bootstrap($_SERVER['argv']);
