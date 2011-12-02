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
 * @see Zend_Service_WindowsAzure_Diagnostics_LogLevel
 */
#require_once 'Zend/Service/WindowsAzure/Diagnostics/LogLevel.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Diagnostics
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property	int		BufferQuotaInMB						Buffer quota in MB
 * @property	int		ScheduledTransferPeriodInMinutes	Scheduled transfer period in minutes
 * @property	string	ScheduledTransferLogLevelFilter		Scheduled transfer log level filter
 * @property	array	Subscriptions						Subscriptions
 */
class Zend_Service_WindowsAzure_Diagnostics_ConfigurationWindowsEventLog
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
            'scheduledtransferloglevelfilter'	=> $scheduledTransferLogLevelFilter,
        	'subscriptions'						=> array()
        );
    }
    
	/**
	 * Add subscription
	 * 
 	 * @param	string	$filter	Event log filter
	 */
    public function addSubscription($filter)
    {
    	$this->_data['subscriptions'][$filter] = $filter;
    }
    
	/**
	 * Remove subscription
	 * 
 	 * @param	string	$filter	Event log filter
	 */
    public function removeSubscription($filter)
    {
    	if (isset($this->_data['subscriptions'][$filter])) {
    		unset($this->_data['subscriptions'][$filter]);
    	}
    }
}