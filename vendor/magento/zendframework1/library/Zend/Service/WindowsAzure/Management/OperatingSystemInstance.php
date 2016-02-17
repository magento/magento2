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
 * @property string $Version         The operating system version. This value corresponds to the configuration value for specifying that your service is to run on a particular version of the Windows Azure guest operating system.
 * @property string $Label           A label for the operating system version.
 * @property string $IsDefault    	 Indicates whether this operating system version is the default version for a service that has not otherwise specified a particular version. The default operating system version is applied to services that are configured for auto-upgrade. An operating system family has exactly one default operating system version at any given time, for which the IsDefault element is set to true; for all other versions, IsDefault is set to false.
 * @property string $IsActive        Indicates whether this operating system version is currently active for running a service. If an operating system version is active, you can manually configure your service to run on that version.
 * @property string $Family          Indicates which operating system family this version belongs to. A value of 1 corresponds to the Windows Azure guest operating system that is substantially compatible with Windows Server 2008 SP2. A value of 2 corresponds to the Windows Azure guest operating system that is substantially compatible with Windows Server 2008 R2.
 * @property string $FamilyLabel     A label for the operating system family.
 */
class Zend_Service_WindowsAzure_Management_OperatingSystemInstance
	extends Zend_Service_WindowsAzure_Management_ServiceEntityAbstract
{
    /**
     * Constructor
     *
     * @param string $version         The operating system version. This value corresponds to the configuration value for specifying that your service is to run on a particular version of the Windows Azure guest operating system.
     * @param string $label           A label for the operating system version.
     * @param string $isDefault    	  Indicates whether this operating system version is the default version for a service that has not otherwise specified a particular version. The default operating system version is applied to services that are configured for auto-upgrade. An operating system family has exactly one default operating system version at any given time, for which the IsDefault element is set to true; for all other versions, IsDefault is set to false.
     * @param string $isActive        Indicates whether this operating system version is currently active for running a service. If an operating system version is active, you can manually configure your service to run on that version.
     * @param string $family          Indicates which operating system family this version belongs to. A value of 1 corresponds to the Windows Azure guest operating system that is substantially compatible with Windows Server 2008 SP2. A value of 2 corresponds to the Windows Azure guest operating system that is substantially compatible with Windows Server 2008 R2.
     * @param string $familyLabel     A label for the operating system family.
	 */
    public function __construct($version, $label, $isDefault, $isActive, $family, $familyLabel)
    {
        $this->_data = array(
            'version'        => $version,
            'label'          => base64_decode($label),
            'isdefault'      => $isDefault,
            'isactive'       => $isActive,
            'family'         => $family,
            'familylabel'    => base64_decode($familyLabel)
        );
    }
}
