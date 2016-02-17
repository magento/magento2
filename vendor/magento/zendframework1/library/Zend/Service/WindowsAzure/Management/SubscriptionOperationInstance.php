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
 * @property string $operationId The globally unique identifier (GUID) of the operation.
 * @property string $operationObjectId The target object for the operation.
 * @property string $operationName The name of the performed operation.
 * @property array  $operationParameters The collection of parameters for the performed operation.
 * @property array  $operationCaller A collection of attributes that identifies the source of the operation.
 * @property array  $operationStatus The current status of the operation.
 */
class Zend_Service_WindowsAzure_Management_SubscriptionOperationInstance
	extends Zend_Service_WindowsAzure_Management_ServiceEntityAbstract
{
    /**
     * Constructor
     *
     * @param string $operationId The globally unique identifier (GUID) of the operation.
     * @param string $operationObjectId The target object for the operation.
     * @param string $operationName The name of the performed operation.
     * @param array  $operationParameters The collection of parameters for the performed operation.
     * @param array  $operationCaller A collection of attributes that identifies the source of the operation.
     * @param array  $operationStatus The current status of the operation.
     */
    public function __construct($operationId, $operationObjectId, $operationName, $operationParameters = array(), $operationCaller = array(), $operationStatus = array())
    {
        $this->_data = array(
            'operationid'          => $operationId,
	        'operationobjectid'    => $operationObjectId,
	        'operationname'        => $operationName,
	        'operationparameters'  => $operationParameters,
	        'operationcaller'      => $operationCaller,
	        'operationstatus'      => $operationStatus
        );
    }

	/**
	 * Add operation parameter
	 *
 	 * @param	string	$name	Name
 	 * @param	string	$value  Value
	 */
    public function addOperationParameter($name, $value)
    {
    	$this->_data['operationparameters'][$name] = $value;
    }
}
