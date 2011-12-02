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
 * @see Zend_Service_WindowsAzure_Diagnostics_ConfigurationLogs
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/ConfigurationLogs.php';

/**
 * @see Zend_Service_WindowsAzure_Diagnostics_ConfigurationDiagnosticInfrastructureLogs
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/ConfigurationDiagnosticInfrastructureLogs.php';

/**
 * @see Zend_Service_WindowsAzure_Diagnostics_ConfigurationPerformanceCounters
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/ConfigurationPerformanceCounters.php';

/**
 * @see Zend_Service_WindowsAzure_Diagnostics_ConfigurationWindowsEventLog
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/ConfigurationWindowsEventLog.php';

/**
 * @see Zend_Service_WindowsAzure_Diagnostics_ConfigurationDirectories
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/ConfigurationDirectories.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Diagnostics
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property	int																				OverallQuotaInMB				Overall quota in MB
 * @property	Zend_Service_WindowsAzure_Diagnostics_ConfigurationLogs							Logs							Logs
 * @property	Zend_Service_WindowsAzure_Diagnostics_ConfigurationDiagnosticInfrastructureLogs	DiagnosticInfrastructureLogs	Diagnostic infrastructure logs
 * @property	Zend_Service_WindowsAzure_Diagnostics_ConfigurationPerformanceCounters				PerformanceCounters				Performance counters
 * @property	Zend_Service_WindowsAzure_Diagnostics_ConfigurationWindowsEventLog					WindowsEventLog					Windows Event Log
 * @property	Zend_Service_WindowsAzure_Diagnostics_ConfigurationDirectories						Directories						Directories
 */
class Zend_Service_WindowsAzure_Diagnostics_ConfigurationDataSources
	extends Zend_Service_WindowsAzure_Diagnostics_ConfigurationObjectBaseAbstract
{
    /**
     * Constructor
     * 
	 * @param	int	$overallQuotaInMB				Overall quota in MB
	 */
    public function __construct($overallQuotaInMB = 0) 
    {	        
        $this->_data = array(
            'overallquotainmb'        		=> $overallQuotaInMB,
            'logs'             				=> new Zend_Service_WindowsAzure_Diagnostics_ConfigurationLogs(),
            'diagnosticinfrastructurelogs'  => new Zend_Service_WindowsAzure_Diagnostics_ConfigurationDiagnosticInfrastructureLogs(),
            'performancecounters'     		=> new Zend_Service_WindowsAzure_Diagnostics_ConfigurationPerformanceCounters(),
            'windowseventlog'              	=> new Zend_Service_WindowsAzure_Diagnostics_ConfigurationWindowsEventLog(),
            'directories'             		=> new Zend_Service_WindowsAzure_Diagnostics_ConfigurationDirectories()
        );
    }
}