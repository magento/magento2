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
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Diagnostics
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property	string	Path					Path
 * @property	string	Container				Container
 * @property	int		DirectoryQuotaInMB		Directory quota in MB
 */
class Zend_Service_WindowsAzure_Diagnostics_DirectoryConfigurationSubscription
	extends Zend_Service_WindowsAzure_Diagnostics_ConfigurationObjectBaseAbstract
{
    /**
     * Constructor
     * 
	 * @param	string	$path					Path
	 * @param	string	$container				Container
	 * @param	int		$directoryQuotaInMB		Directory quota in MB
	 */
    public function __construct($path, $container, $directoryQuotaInMB = 1024) 
    {	        
        $this->_data = array(
            'path'      			=> $path,
            'container' 			=> $container,
        	'directoryquotainmb' 	=> $directoryQuotaInMB
        );
    }
}