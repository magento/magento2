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
 * @subpackage Management
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_SqlAzure_Management_ServiceEntityAbstract
 */
#require_once 'Zend/Service/SqlAzure/Management/ServiceEntityAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service_SqlAzure
 * @subpackage Management
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property string $Name               The name of the firewall rule.
 * @property string $StartIpAddress     The start IP address.
 * @property string $EndIpAddress       The end IP address.
 */
class Zend_Service_SqlAzure_Management_FirewallRuleInstance
	extends Zend_Service_SqlAzure_Management_ServiceEntityAbstract
{
    /**
     * Constructor
     *
     * @param string $name               The name of the firewall rule.
     * @param string $startIpAddress     The start IP address.
     * @param string $endIpAddress       The end IP address.
	 */
    public function __construct($name, $startIpAddress, $endIpAddress)
    {
        $this->_data = array(
            'name'               => $name,
            'startipaddress'     => $startIpAddress,
            'endipaddress'       => $endIpAddress
        );
    }
}
