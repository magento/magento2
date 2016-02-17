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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_WindowsAzure_Diagnostics_ConfigurationObjectBaseAbstract
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/ConfigurationObjectBaseAbstract.php';

/**
 * @see Zend_Service_WindowsAzure_Diagnostics_LogLevel
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/LogLevel.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Diagnostics
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property	int		BufferQuotaInMB						Buffer quota in MB
 * @property	int		ScheduledTransferPeriodInMinutes	Scheduled transfer period in minutes
 * @property	string	ScheduledTransferLogLevelFilter		Scheduled transfer log level filter
 */
class Zend_Service_WindowsAzure_Diagnostics_ConfigurationLogs
	extends Zend_Service_WindowsAzure_Diagnostics_ConfigurationObjectBaseAbstract
{
    /**
     * Constructor
     *
	 * @param	int		$bufferQuotaInMB					Buffer quota in MB
	 * @param	int		$scheduledTransferPeriodInMinutes	Scheduled transfer period in minutes
	 * @param	string	$scheduledTransferLogLevelFilter	Scheduled transfer log level filter
	 */
    public function __construct($bufferQuotaInMB = 0, $scheduledTransferPeriodInMinutes = 0, $scheduledTransferLogLevelFilter = Zend_Service_WindowsAzure_Diagnostics_LogLevel::UNDEFINED)
    {
        $this->_data = array(
            'bufferquotainmb'        			=> $bufferQuotaInMB,
            'scheduledtransferperiodinminutes' 	=> $scheduledTransferPeriodInMinutes,
            'scheduledtransferloglevelfilter'	=> $scheduledTransferLogLevelFilter
        );
    }
}
