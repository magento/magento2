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
 * @property string $Name               The name of the server.
 * @property string $DnsName            The DNS name of the server.
 * @property string $AdministratorLogin The administrator login.
 * @property string $Location           The location of the server in Windows Azure.
 */
class Zend_Service_SqlAzure_Management_ServerInstance
	extends Zend_Service_SqlAzure_Management_ServiceEntityAbstract
{
    /**
     * Constructor
     *
     * @param string $name               The name of the server.
     * @param string $administratorLogin The administrator login.
     * @param string $location           The location of the server in Windows Azure.
	 */
    public function __construct($name, $administratorLogin, $location)
    {
        $this->_data = array(
            'name'               => $name,
            'dnsname'            => $name . '.database.windows.net',
            'administratorlogin' => $administratorLogin,
            'location'           => $location
        );
    }
}
