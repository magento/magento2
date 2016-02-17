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
 * @see Zend_Service_WindowsAzure_Management_ServiceEntityAbstract
 */
#require_once 'Zend/Service/WindowsAzure/Management/ServiceEntityAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Management
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property string $Name              Indicates which operating system family this version belongs to. A value of 1 corresponds to the Windows Azure guest operating system that is substantially compatible with Windows Server 2008 SP2. A value of 2 corresponds to the Windows Azure guest operating system that is substantially compatible with Windows Server 2008 R2.
 * @property string $Label             A label for the operating system version.
 * @property array  $OperatingSystems  A list of operating systems available under this operating system family.
 */
class Zend_Service_WindowsAzure_Management_OperatingSystemFamilyInstance
	extends Zend_Service_WindowsAzure_Management_ServiceEntityAbstract
{
    /**
     * Constructor
     *
     * @param string $name              Indicates which operating system family this version belongs to. A value of 1 corresponds to the Windows Azure guest operating system that is substantially compatible with Windows Server 2008 SP2. A value of 2 corresponds to the Windows Azure guest operating system that is substantially compatible with Windows Server 2008 R2.
     * @param string $label             A label for the operating system version.
     * @param array  $operatingSystems  A list of operating systems available under this operating system family.
	 */
    public function __construct($name, $label, $operatingSystems = array())
    {
        $this->_data = array(
            'name'              => $name,
            'label'             => base64_decode($label),
            'operatingsystems'  => $operatingSystems
        );
    }
}
