<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Api
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservices server handler WSI
 *
 * @category   Mage
 * @package    Mage_Api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Server_Handler_Soap_Wsi extends Mage_Api_Model_Server_HandlerAbstract
{
    protected $_resourceSuffix = '_V2';

    /**
     * Interceptor for all interfaces
     *
     * @param string $function
     * @param array $args
     */

    public function __call($function, $args)
    {
        $args = $args[0];

        /** @var Mage_Api_Helper_Data */
        $helper = Mage::helper('Mage_Api_Helper_Data');

        $helper->wsiArrayUnpacker($args);
        $args = get_object_vars($args);

        if (isset($args['sessionId'])) {
            $sessionId = $args['sessionId'];
            unset($args['sessionId']);
        } else {
            // Was left for backward compatibility.
            $sessionId = array_shift($args);
        }

        $apiKey = '';
        $nodes = Mage::getSingleton('Mage_Api_Model_Config')->getNode('v2/resources_function_prefix')->children();
        foreach ($nodes as $resource => $prefix) {
            $prefix = $prefix->asArray();
            if (false !== strpos($function, $prefix)) {
                $method = substr($function, strlen($prefix));
                $apiKey = $resource . '.' . strtolower($method[0]) . substr($method, 1);
            }
        }

        list($modelName, $methodName) = $this->_getResourceName($apiKey);
        $methodParams = $this->getMethodParams($modelName, $methodName);

        $args = $this->prepareArgs($methodParams, $args);

        $res = $this->call($sessionId, $apiKey, $args);

        $obj = $helper->wsiArrayPacker($res);
        $stdObj = new stdClass();
        $stdObj->result = $obj;

        return $stdObj;
    }

    /**
     * Login user and Retrieve session id
     *
     * @param string $username
     * @param string $apiKey
     * @return string
     */
    public function login($username, $apiKey = null)
    {
        if (is_object($username)) {
            $apiKey = $username->apiKey;
            $username = $username->username;
        }

        $stdObject = new stdClass();
        $stdObject->result = parent::login($username, $apiKey);
        return $stdObject;
    }

    /**
     * Return called class and method names
     *
     * @param String $apiPath
     * @return Array
     */
    protected function _getResourceName($apiPath)
    {

        list($resourceName, $methodName) = explode('.', $apiPath);

        if (empty($resourceName) || empty($methodName)) {
            return $this->_fault('resource_path_invalid');
        }

        $resourcesAlias = $this->_getConfig()->getResourcesAlias();
        $resources = $this->_getConfig()->getResources();
        if (isset($resourcesAlias->$resourceName)) {
            $resourceName = (string)$resourcesAlias->$resourceName;
        }

        $methodInfo = $resources->$resourceName->methods->$methodName;

        $modelName = $this->_prepareResourceModelName((string)$resources->$resourceName->model);

        $modelClass = Mage::getConfig()->getModelClassName($modelName);

        $method = (isset($methodInfo->method) ? (string)$methodInfo->method : $methodName);

        return array($modelClass, $method);
    }

    /**
     * Return an array of parameters for the callable method.
     *
     * @param String $modelName
     * @param String $methodName
     * @return Array of ReflectionParameter
     */
    public function getMethodParams($modelName, $methodName)
    {

        $method = new ReflectionMethod($modelName, $methodName);

        return $method->getParameters();
    }

    /**
     * Prepares arguments for the method calling. Sort in correct order, set default values for omitted parameters.
     *
     * @param Array $params
     * @param Array $args
     * @return Array
     */
    public function prepareArgs($params, $args)
    {

        $callArgs = array();

        /** @var $parameter ReflectionParameter */
        foreach ($params AS $parameter) {
            $pName = $parameter->getName();
            if (isset($args[$pName])) {
                $callArgs[$pName] = $args[$pName];
            } else {
                if ($parameter->isOptional()) {
                    $callArgs[$pName] = $parameter->getDefaultValue();
                } else {
                    Mage::logException(new Exception("Required parameter \"$pName\" is missing.", 0));
                    $this->_fault('invalid_request_param');
                }
            }
        }
        return $callArgs;
    }

}
