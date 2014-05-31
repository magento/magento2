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
 * @package    Zend_Service_WindowsAzure
 * @subpackage Diagnostics
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_WindowsAzure_Diagnostics_Exception
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/Exception.php';

/**
 * @see Zend_Service_WindowsAzure_Diagnostics_ConfigurationObjectBaseAbstract
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/ConfigurationObjectBaseAbstract.php';

/**
 * @see Zend_Service_WindowsAzure_Diagnostics_ConfigurationDataSources
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/ConfigurationDataSources.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Diagnostics
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property Zend_Service_WindowsAzure_Diagnostics_ConfigurationDataSources	DataSources	Data sources
 */
class Zend_Service_WindowsAzure_Diagnostics_ConfigurationInstance
	extends Zend_Service_WindowsAzure_Diagnostics_ConfigurationObjectBaseAbstract
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
        $this->_data = array(
            'datasources'	=> new Zend_Service_WindowsAzure_Diagnostics_ConfigurationDataSources()
        );
	}

	/**
	 * Load configuration XML
	 * 
	 * @param string $configurationXml Configuration XML
	 */
	public function loadXml($configurationXml)
	{
		// Convert to SimpleXMLElement
		$configurationXml = simplexml_load_string($configurationXml);
	
		// Assign general settings
		$this->DataSources->OverallQuotaInMB = (int)$configurationXml->DataSources->OverallQuotaInMB;

		// Assign Logs settings	
		$this->DataSources->Logs->BufferQuotaInMB = (int)$configurationXml->DataSources->Logs->BufferQuotaInMB;
		$this->DataSources->Logs->ScheduledTransferPeriodInMinutes = (int)$configurationXml->DataSources->Logs->ScheduledTransferPeriodInMinutes;
		$this->DataSources->Logs->ScheduledTransferLogLevelFilter = (string)$configurationXml->DataSources->Logs->ScheduledTransferLogLevelFilter;

		// Assign DiagnosticInfrastructureLogs settings
		$this->DataSources->DiagnosticInfrastructureLogs->BufferQuotaInMB = (int)$configurationXml->DataSources->DiagnosticInfrastructureLogs->BufferQuotaInMB;
		$this->DataSources->DiagnosticInfrastructureLogs->ScheduledTransferPeriodInMinutes = (int)$configurationXml->DataSources->DiagnosticInfrastructureLogs->ScheduledTransferPeriodInMinutes;
		$this->DataSources->DiagnosticInfrastructureLogs->ScheduledTransferLogLevelFilter = (string)$configurationXml->DataSources->DiagnosticInfrastructureLogs->ScheduledTransferLogLevelFilter;

		// Assign PerformanceCounters settings
		$this->DataSources->PerformanceCounters->BufferQuotaInMB = (int)$configurationXml->DataSources->PerformanceCounters->BufferQuotaInMB;
		$this->DataSources->PerformanceCounters->ScheduledTransferPeriodInMinutes = (int)$configurationXml->DataSources->PerformanceCounters->ScheduledTransferPeriodInMinutes;
		if ($configurationXml->DataSources->PerformanceCounters->Subscriptions
			&& $configurationXml->DataSources->PerformanceCounters->Subscriptions->PerformanceCounterConfiguration) {
			$subscriptions = $configurationXml->DataSources->PerformanceCounters->Subscriptions;
			if (count($subscriptions->PerformanceCounterConfiguration) > 1) {
				$subscriptions = $subscriptions->PerformanceCounterConfiguration;
			} else {
				$subscriptions = array($subscriptions->PerformanceCounterConfiguration);
			}
			foreach ($subscriptions as $subscription) {
				$this->DataSources->PerformanceCounters->addSubscription((string)$subscription->CounterSpecifier, (int)$subscription->SampleRateInSeconds);
			}
		}
				
		// Assign WindowsEventLog settings
		$this->DataSources->WindowsEventLog->BufferQuotaInMB = (int)$configurationXml->DataSources->WindowsEventLog->BufferQuotaInMB;
		$this->DataSources->WindowsEventLog->ScheduledTransferPeriodInMinutes = (int)$configurationXml->DataSources->WindowsEventLog->ScheduledTransferPeriodInMinutes;
		$this->DataSources->WindowsEventLog->ScheduledTransferLogLevelFilter = (string)$configurationXml->DataSources->WindowsEventLog->ScheduledTransferLogLevelFilter;
		if ($configurationXml->DataSources->WindowsEventLog->Subscriptions
			&& $configurationXml->DataSources->WindowsEventLog->Subscriptions->string) {
			$subscriptions = $configurationXml->DataSources->WindowsEventLog->Subscriptions;
			if (count($subscriptions->string) > 1) {
				$subscriptions = $subscriptions->string;
			} else {
				$subscriptions = array($subscriptions->string);
			}
			foreach ($subscriptions as $subscription) {
				$this->DataSources->WindowsEventLog->addSubscription((string)$subscription);
			}
		}
		
		// Assign Directories settings
		$this->DataSources->Directories->BufferQuotaInMB = (int)$configurationXml->DataSources->Directories->BufferQuotaInMB;
		$this->DataSources->Directories->ScheduledTransferPeriodInMinutes = (int)$configurationXml->DataSources->Directories->ScheduledTransferPeriodInMinutes;
		if ($configurationXml->DataSources->Directories->Subscriptions
			&& $configurationXml->DataSources->Directories->Subscriptions->DirectoryConfiguration) {
			$subscriptions = $configurationXml->DataSources->WindowsEventLog->Subscriptions;
			if (count($subscriptions->DirectoryConfiguration) > 1) {
				$subscriptions = $subscriptions->DirectoryConfiguration;
			} else {
				$subscriptions = array($subscriptions->DirectoryConfiguration);
			}
			foreach ($subscriptions as $subscription) {
				$this->DataSources->Directories->addSubscription((string)$subscription->Path, (string)$subscription->Container, (int)$subscription->DirectoryQuotaInMB);
			}
		}
	}
	
	/**
	 * Create configuration XML
	 * 
	 * @return string
	 */
	public function toXml()
	{
		// Return value
		$returnValue = array();
		
		// Build XML
		$returnValue[] = '<?xml version="1.0"?>';
		$returnValue[] = '<ConfigRequest xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">';
		
		// Add data sources
		$returnValue[] = '  <DataSources>';
		
		$returnValue[] = '    <OverallQuotaInMB>' . $this->DataSources->OverallQuotaInMB . '</OverallQuotaInMB>';
		
		$returnValue[] = '    <Logs>';
		$returnValue[] = '      <BufferQuotaInMB>' . $this->DataSources->Logs->BufferQuotaInMB . '</BufferQuotaInMB>';
		$returnValue[] = '      <ScheduledTransferPeriodInMinutes>' . $this->DataSources->Logs->ScheduledTransferPeriodInMinutes . '</ScheduledTransferPeriodInMinutes>';
		$returnValue[] = '      <ScheduledTransferLogLevelFilter>' . $this->DataSources->Logs->ScheduledTransferLogLevelFilter . '</ScheduledTransferLogLevelFilter>';
		$returnValue[] = '    </Logs>';
		
		$returnValue[] = '    <DiagnosticInfrastructureLogs>';
		$returnValue[] = '      <BufferQuotaInMB>' . $this->DataSources->DiagnosticInfrastructureLogs->BufferQuotaInMB . '</BufferQuotaInMB>';
		$returnValue[] = '      <ScheduledTransferPeriodInMinutes>' . $this->DataSources->DiagnosticInfrastructureLogs->ScheduledTransferPeriodInMinutes . '</ScheduledTransferPeriodInMinutes>';
		$returnValue[] = '      <ScheduledTransferLogLevelFilter>' . $this->DataSources->DiagnosticInfrastructureLogs->ScheduledTransferLogLevelFilter . '</ScheduledTransferLogLevelFilter>';
		$returnValue[] = '    </DiagnosticInfrastructureLogs>';
		
		$returnValue[] = '    <PerformanceCounters>';
		$returnValue[] = '      <BufferQuotaInMB>' . $this->DataSources->PerformanceCounters->BufferQuotaInMB . '</BufferQuotaInMB>';
		$returnValue[] = '      <ScheduledTransferPeriodInMinutes>' . $this->DataSources->PerformanceCounters->ScheduledTransferPeriodInMinutes . '</ScheduledTransferPeriodInMinutes>';
		if (count($this->DataSources->PerformanceCounters->Subscriptions) == 0) {
			$returnValue[] = '      <Subscriptions />';
		} else {
			$returnValue[] = '      <Subscriptions>';
			foreach ($this->DataSources->PerformanceCounters->Subscriptions as $subscription) {
				$returnValue[] = '        <PerformanceCounterConfiguration>';
				$returnValue[] = '          <CounterSpecifier>' . $subscription->CounterSpecifier . '</CounterSpecifier>';
				$returnValue[] = '          <SampleRateInSeconds>' . $subscription->SampleRateInSeconds . '</SampleRateInSeconds>';
				$returnValue[] = '        </PerformanceCounterConfiguration>';
			}
			$returnValue[] = '      </Subscriptions>';
		}
		$returnValue[] = '    </PerformanceCounters>';
		
		$returnValue[] = '    <WindowsEventLog>';
		$returnValue[] = '      <BufferQuotaInMB>' . $this->DataSources->WindowsEventLog->BufferQuotaInMB . '</BufferQuotaInMB>';
		$returnValue[] = '      <ScheduledTransferPeriodInMinutes>' . $this->DataSources->WindowsEventLog->ScheduledTransferPeriodInMinutes . '</ScheduledTransferPeriodInMinutes>';
			if (count($this->DataSources->WindowsEventLog->Subscriptions) == 0) {
			$returnValue[] = '      <Subscriptions />';
		} else {
			$returnValue[] = '      <Subscriptions>';
			foreach ($this->DataSources->WindowsEventLog->Subscriptions as $subscription) {
				$returnValue[] = '      <string>' . $subscription . '</string>';
			}
			$returnValue[] = '      </Subscriptions>';
		}
		$returnValue[] = '      <ScheduledTransferLogLevelFilter>' . $this->DataSources->WindowsEventLog->ScheduledTransferLogLevelFilter . '</ScheduledTransferLogLevelFilter>';
		$returnValue[] = '    </WindowsEventLog>';
		
		$returnValue[] = '    <Directories>';
		$returnValue[] = '      <BufferQuotaInMB>' . $this->DataSources->Directories->BufferQuotaInMB . '</BufferQuotaInMB>';
		$returnValue[] = '      <ScheduledTransferPeriodInMinutes>' . $this->DataSources->Directories->ScheduledTransferPeriodInMinutes . '</ScheduledTransferPeriodInMinutes>';
		if (count($this->DataSources->Directories->Subscriptions) == 0) {
			$returnValue[] = '      <Subscriptions />';
		} else {
			$returnValue[] = '      <Subscriptions>';
			foreach ($this->DataSources->Directories->Subscriptions as $subscription) {
				$returnValue[] = '        <DirectoryConfiguration>';
				$returnValue[] = '          <Path>' . $subscription->Path . '</Path>';
				$returnValue[] = '          <Container>' . $subscription->Container . '</Container>';
				$returnValue[] = '          <DirectoryQuotaInMB>' . $subscription->DirectoryQuotaInMB . '</DirectoryQuotaInMB>';
				$returnValue[] = '        </DirectoryConfiguration>';
			}
			$returnValue[] = '      </Subscriptions>';
		}
		$returnValue[] = '    </Directories>';
		
		$returnValue[] = '  </DataSources>';
		$returnValue[] = '</ConfigRequest>';
		
		// Return
		return implode("\r\n", $returnValue);
	}
}