<?php
use Zend\Server\Reflection\ReflectionMethod;

/**
 * Web API configuration.
 *
 * This class is responsible for collecting web API configuration using reflection
 * as well as for implementing interface to provide access to collected configuration.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Mage_Webapi_Model_ConfigAbstract
{
    /**#@+
     * Cache parameters.
     */
    const WEBSERVICE_CACHE_NAME = 'config_webservice';
    const WEBSERVICE_CACHE_TAG = 'WEBSERVICE';
    /**#@-*/

    /**#@+
     * Version parameters.
     */
    const VERSION_NUMBER_PREFIX = 'V';
    const VERSION_MIN = 1;
    /**#@-*/

    /** @var Mage_Webapi_Model_Config_ReaderAbstract */
    protected $_reader;

    /** @var Mage_Webapi_Helper_Config */
    protected $_helper;

    /** @var Mage_Core_Model_App */
    protected $_app;

    /**
     * Resources configuration data.
     *
     * @var array
     */
    protected $_data;

    /**
     * Initialize dependencies. Initialize data.
     *
     * @param Mage_Webapi_Model_Config_ReaderAbstract $reader
     * @param Mage_Webapi_Helper_Config $helper
     * @param Mage_Core_Model_App $app
     */
    public function __construct(
        Mage_Webapi_Model_Config_ReaderAbstract $reader,
        Mage_Webapi_Helper_Config $helper,
        Mage_Core_Model_App $app
    ) {
        $this->_reader = $reader;
        $this->_helper = $helper;
        $this->_app = $app;
        $this->_data = $this->_reader->getData();
    }

    /**
     * Retrieve data type details for the given type name.
     *
     * @param string $typeName
     * @return array
     * @throws InvalidArgumentException
     */
    public function getTypeData($typeName)
    {
        if (!isset($this->_data['types'][$typeName])) {
            throw new InvalidArgumentException(sprintf('Data type "%s" was not found in config.', $typeName));
        }
        return $this->_data['types'][$typeName];
    }

    /**
     * Add or update type data in config.
     *
     * @param string $typeName
     * @param array $data
     */
    public function setTypeData($typeName, $data)
    {
        if (!isset($this->_data['types'][$typeName])) {
            $this->_data['types'][$typeName] = $data;
        } else {
            $this->_data['types'][$typeName] = array_merge_recursive($this->_data['types'][$typeName], $data);
        }
    }

    /**
     * Identify method name by operation name.
     *
     * @param string $operationName
     * @param string $resourceVersion Two formats are acceptable: 'v1' and '1'
     * @return string|bool Method name on success; false on failure
     */
    public function getMethodNameByOperation($operationName, $resourceVersion = null)
    {
        list($resourceName, $methodName) = $this->_parseOperationName($operationName);
        $versionCheckRequired = is_string($resourceVersion);
        if (!$versionCheckRequired) {
            return $methodName;
        }
        /** Allow to take resource version in two formats: with prefix and without it */
        $resourceVersion = is_numeric($resourceVersion)
            ? self::VERSION_NUMBER_PREFIX . $resourceVersion
            : ucfirst($resourceVersion);
        return isset($this->_data['resources'][$resourceName]['versions'][$resourceVersion]['methods'][$methodName])
            ? $methodName : false;
    }

    /**
     * Parse operation name to separate resource name from method name.
     *
     * <pre>Result format:
     * array(
     *      0 => 'resourceName',
     *      1 => 'methodName'
     * )</pre>
     *
     * @param string $operationName
     * @return array
     * @throws InvalidArgumentException In case when the specified operation name is invalid.
     */
    protected function _parseOperationName($operationName)
    {
        /** Note that '(.*?)' must not be greedy to allow regexp to match 'multiUpdate' method before 'update' */
        $regEx = sprintf('/(.*?)(%s)$/i', implode('|', Mage_Webapi_Controller_ActionAbstract::getAllowedMethods()));
        if (preg_match($regEx, $operationName, $matches)) {
            $resourceName = $matches[1];
            $methodName = lcfirst($matches[2]);
            $result = array($resourceName, $methodName);
            return $result;
        }
        throw new InvalidArgumentException(sprintf(
            'The "%s" is not a valid API resource operation name.',
            $operationName
        ));
    }

    /**
     * Identify controller class by operation name.
     *
     * @param string $operationName
     * @return string Resource name on success
     * @throws LogicException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getControllerClassByOperationName($operationName)
    {
        list($resourceName, $methodName) = $this->_parseOperationName($operationName);
        if (isset($this->_data['resources'][$resourceName]['controller'])) {
            return $this->_data['resources'][$resourceName]['controller'];
        }
        throw new LogicException(sprintf('Resource "%s" must have associated controller class.', $resourceName));
    }

    /**
     * Retrieve method metadata.
     *
     * @param Zend\Server\Reflection\ReflectionMethod $methodReflection
     * @return array
     * @throws InvalidArgumentException If specified method was not previously registered in API config.
     */
    public function getMethodMetadata(ReflectionMethod $methodReflection)
    {
        $resourceName = $this->_helper->translateResourceName($methodReflection->getDeclaringClass()->getName());
        $resourceVersion = $this->_getMethodVersion($methodReflection);
        $methodName = $this->_helper->getMethodNameWithoutVersionSuffix($methodReflection);

        if (!isset($this->_data['resources'][$resourceName]['versions'][$resourceVersion]['methods'][$methodName])) {
            throw new InvalidArgumentException(sprintf(
                'The "%s" method of "%s" resource in version "%s" is not registered.',
                $methodName,
                $resourceName,
                $resourceVersion
            ));
        }
        return $this->_data['resources'][$resourceName]['versions'][$resourceVersion]['methods'][$methodName];
    }

    /**
     * Retrieve mapping of complex types defined in WSDL to real data classes.
     *
     * @return array
     */
    public function getTypeToClassMap()
    {
        return !is_null($this->_data['type_to_class_map']) ? $this->_data['type_to_class_map'] : array();
    }

    /**
     * Identify deprecation policy for the specified operation.
     *
     * Return result in the following format:<pre>
     * array(
     *     'removed'      => true,            // either 'deprecated' or 'removed' item must be specified
     *     'deprecated'   => true,
     *     'use_resource' => 'operationName'  // resource to be used instead
     *     'use_method'   => 'operationName'  // method to be used instead
     *     'use_version'  => N,               // version of method to be used instead
     * )
     * </pre>
     *
     * @param string $resourceName
     * @param string $method
     * @param string $resourceVersion
     * @return array|bool On success array with policy details; false otherwise.
     * @throws InvalidArgumentException
     */
    public function getDeprecationPolicy($resourceName, $method, $resourceVersion)
    {
        $deprecationPolicy = false;
        $resourceData = $this->_getResourceData($resourceName, $resourceVersion);
        if (!isset($resourceData['methods'][$method])) {
            throw new InvalidArgumentException(sprintf(
                'Method "%s" does not exist in "%s" version of resource "%s".',
                $method,
                $resourceVersion,
                $resourceName
            ));
        }
        $methodData = $resourceData['methods'][$method];
        if (isset($methodData['deprecation_policy']) && is_array($methodData['deprecation_policy'])) {
            $deprecationPolicy = $methodData['deprecation_policy'];
        }
        return $deprecationPolicy;
    }

    /**
     * Check if specified method is deprecated or removed.
     *
     * Throw exception in two cases:<br/>
     * - method is removed<br/>
     * - method is deprecated and developer mode is enabled
     *
     * @param string $resourceName
     * @param string $method
     * @param string $resourceVersion
     * @throws Mage_Webapi_Exception
     * @throws LogicException
     */
    public function checkDeprecationPolicy($resourceName, $method, $resourceVersion)
    {
        $deprecationPolicy = $this->getDeprecationPolicy($resourceName, $method, $resourceVersion);
        if ($deprecationPolicy) {
            /** Initialize message with information about what method should be used instead of requested one. */
            if (isset($deprecationPolicy['use_resource']) && isset($deprecationPolicy['use_method'])
                && isset($deprecationPolicy['use_version'])
            ) {
                $messageUseMethod = $this->_helper
                    ->__('Please use version "%s" of "%s" method in "%s" resource instead.',
                    $deprecationPolicy['use_version'],
                    $deprecationPolicy['use_method'],
                    $deprecationPolicy['use_resource']
                );
            } else {
                $messageUseMethod = '';
            }

            $badRequestCode = Mage_Webapi_Exception::HTTP_BAD_REQUEST;
            if (isset($deprecationPolicy['removed'])) {
                $removalMessage = $this->_helper
                    ->__('Version "%s" of "%s" method in "%s" resource was removed.',
                    $resourceVersion,
                    $method,
                    $resourceName
                );
                throw new Mage_Webapi_Exception($removalMessage . ' ' . $messageUseMethod, $badRequestCode);
            } elseif (isset($deprecationPolicy['deprecated']) && $this->_app->isDeveloperMode()) {
                $deprecationMessage = $this->_helper
                    ->__('Version "%s" of "%s" method in "%s" resource is deprecated.',
                    $resourceVersion,
                    $method,
                    $resourceName
                );
                throw new Mage_Webapi_Exception($deprecationMessage . ' ' . $messageUseMethod, $badRequestCode);
            }
        }
    }

    /**
     * Identify the maximum version of the specified resource available.
     *
     * @param string $resourceName
     * @return int
     * @throws InvalidArgumentException When resource with the specified name does not exist.
     */
    public function getResourceMaxVersion($resourceName)
    {
        if (!isset($this->_data['resources'][$resourceName])) {
            throw new InvalidArgumentException(sprintf('Resource "%s" does not exist.', $resourceName));
        }
        $resourceVersions = array_keys($this->_data['resources'][$resourceName]['versions']);
        foreach ($resourceVersions as &$version) {
            $version = str_replace(self::VERSION_NUMBER_PREFIX, '', $version);
        }
        $maxVersion = max($resourceVersions);
        return (int)$maxVersion;
    }

    /**
     * Find the most appropriate version suffix for the requested action.
     *
     * If there is no action with requested version, fallback mechanism is used.
     * If there is no appropriate action found after fallback - exception is thrown.
     *
     * @param string $operationName
     * @param int $requestedVersion
     * @param Mage_Webapi_Controller_ActionAbstract $controllerInstance
     * @return string
     * @throws Mage_Webapi_Exception
     */
    public function identifyVersionSuffix($operationName, $requestedVersion, $controllerInstance)
    {
        $methodName = $this->getMethodNameByOperation($operationName, $requestedVersion);
        $methodVersion = $requestedVersion;
        while ($methodVersion >= self::VERSION_MIN) {
            $versionSuffix = Mage_Webapi_Model_ConfigAbstract::VERSION_NUMBER_PREFIX . $methodVersion;
            if ($controllerInstance->hasAction($methodName . $versionSuffix)) {
                return $versionSuffix;
            }
            $methodVersion--;
        }
        throw new Mage_Webapi_Exception($this->_helper
                ->__('The "%s" operation is not implemented in version %s', $operationName, $requestedVersion),
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
    }

    /**
     * Check if version number is from valid range.
     *
     * @param int $version
     * @param string $resourceName
     * @throws Mage_Webapi_Exception
     */
    public function validateVersionNumber($version, $resourceName)
    {
        $maxVersion = $this->getResourceMaxVersion($resourceName);
        if ((int)$version > $maxVersion) {
            throw new Mage_Webapi_Exception(
                $this->_helper->__('The maximum version of the requested resource is "%s".', $maxVersion),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST
            );
        } elseif ((int)$version < self::VERSION_MIN) {
            throw new Mage_Webapi_Exception(
                $this->_helper->__('Resource version cannot be lower than "%s".', self::VERSION_MIN),
                Mage_Webapi_Exception::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Retrieve the list of all resources with their versions.
     *
     * @return array
     */
    public function getAllResourcesVersions()
    {
        $resources = array();
        foreach ($this->_data['resources'] as $resourceName => $data) {
            $resources[$resourceName] = array_keys($data['versions']);
        }

        return $resources;
    }

    /**
     * Identify API method version by its reflection.
     *
     * @param ReflectionMethod $methodReflection
     * @return string|bool Method version with prefix on success.
     *      false is returned in case when method should not be exposed via API.
     */
    protected function _getMethodVersion(ReflectionMethod $methodReflection)
    {
        $methodVersion = false;
        $methodNameWithSuffix = $methodReflection->getName();
        $regularExpression = $this->_helper->getMethodNameRegularExpression();
        if (preg_match($regularExpression, $methodNameWithSuffix, $methodMatches)) {
            $resourceNamePosition = 2;
            $methodVersion = ucfirst($methodMatches[$resourceNamePosition]);
        }
        return $methodVersion;
    }

    /**
     * Retrieve resource description for specified version.
     *
     * @param string $resourceName
     * @param string $resourceVersion Two formats are acceptable: 'v1' and '1'
     * @return array
     * @throws InvalidArgumentException When the specified resource version does not exist.
     */
    protected function _getResourceData($resourceName, $resourceVersion)
    {
        /** Allow to take resource version in two formats: with prefix and without it */
        $resourceVersion = is_numeric($resourceVersion)
            ? self::VERSION_NUMBER_PREFIX . $resourceVersion
            : ucfirst($resourceVersion);
        try {
            $this->_checkIfResourceVersionExists($resourceName, $resourceVersion);
        } catch (RuntimeException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
        return $this->_data['resources'][$resourceName]['versions'][$resourceVersion];
    }

    /**
     * Check if specified version of resource exists. If not - exception is thrown.
     *
     * @param string $resourceName
     * @param string $resourceVersion
     * @throws RuntimeException When resource does not exist.
     */
    protected function _checkIfResourceVersionExists($resourceName, $resourceVersion)
    {
        if (!isset($this->_data['resources'][$resourceName])) {
            throw new RuntimeException($this->_helper->__('Unknown resource "%s".', $resourceName));
        }
        if (!isset($this->_data['resources'][$resourceName]['versions'][$resourceVersion])) {
            throw new RuntimeException($this->_helper->__(
                'Unknown version "%s" for resource "%s".',
                $resourceVersion,
                $resourceName
            ));
        }
    }
}
