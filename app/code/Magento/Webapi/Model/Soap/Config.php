<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Webapi\Model\Config\Converter;
use Magento\Webapi\Model\Cache\Type as WebApiCache;

/**
 * Webapi Config Model for Soap.
 */
class Config
{
    /**#@+
     * Keys that a used for service config internal representation.
     */
    const KEY_CLASS = 'class';

    const KEY_IS_SECURE = 'isSecure';

    const KEY_SERVICE_METHODS = 'methods';

    const KEY_METHOD = 'method';

    const KEY_IS_REQUIRED = 'inputRequired';

    const KEY_ACL_RESOURCES = 'resources';

    const CACHE_ID = 'soap-services-config';

    /**#@-*/

    /** @var ReadInterface */
    protected $modulesDirectory;

    /** @var \Magento\Webapi\Model\Config */
    protected $_config;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;

    /**
     * SOAP services should be stored separately as the list of available operations
     * is collected using reflection, not taken from config as for REST
     *
     * @var array
     */
    protected $_soapServices;

    /**
     * List of SOAP operations available in the system
     *
     * @var array
     */
    protected $_soapOperations;

    /** @var \Magento\Webapi\Helper\Data */
    protected $_helper;

    /**
     * @var \Magento\Webapi\Model\Config\ClassReflector
     */
    protected $_classReflector;

    /**
     * @var WebApiCache
     */
    protected $cache;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Webapi\Model\Config $config
     * @param \Magento\Webapi\Model\Config\ClassReflector $classReflector
     * @param \Magento\Webapi\Helper\Data $helper
     * @param WebApiCache $cache
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Webapi\Model\Config $config,
        \Magento\Webapi\Model\Config\ClassReflector $classReflector,
        \Magento\Webapi\Helper\Data $helper,
        WebApiCache $cache
    ) {
        $this->modulesDirectory = $filesystem->getDirectoryRead(DirectoryList::MODULES);
        $this->_config = $config;
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_classReflector = $classReflector;
        $this->cache = $cache;
    }

    /**
     * Retrieve the list of SOAP operations available in the system
     *
     * @param array $requestedService The list of requested services with their versions
     * @return array <pre>
     * array(
     *     array(
     *         'class' => $serviceClass,
     *         'method' => $serviceMethod
     *         'isSecure' => $isSecure
     *     ),
     *      ...
     * )</pre>
     */
    protected function _getSoapOperations($requestedService)
    {
        if (null == $this->_soapOperations) {
            $this->_soapOperations = [];
            foreach ($this->getRequestedSoapServices($requestedService) as $serviceData) {
                foreach ($serviceData[self::KEY_SERVICE_METHODS] as $methodData) {
                    $method = $methodData[self::KEY_METHOD];
                    $class = $serviceData[self::KEY_CLASS];
                    $operationName = $this->getSoapOperation($class, $method);
                    $this->_soapOperations[$operationName] = [
                        self::KEY_CLASS => $class,
                        self::KEY_METHOD => $method,
                        self::KEY_IS_SECURE => $methodData[self::KEY_IS_SECURE],
                        self::KEY_ACL_RESOURCES => $methodData[self::KEY_ACL_RESOURCES],
                    ];
                }
            }
        }
        return $this->_soapOperations;
    }

    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getSoapServicesConfig()
    {
        if (null === $this->_soapServices) {
            $soapServicesConfig = $this->cache->load(self::CACHE_ID);
            if ($soapServicesConfig && is_string($soapServicesConfig)) {
                $this->_soapServices = unserialize($soapServicesConfig);
            } else {
                $this->_soapServices = $this->_initServicesMetadata();
                $this->cache->save(serialize($this->_soapServices), self::CACHE_ID);
            }
        }
        return $this->_soapServices;
    }

    /**
     * Collect the list of services with their operations available in SOAP.
     *
     * @return array
     */
    protected function _initServicesMetadata()
    {
        $soapServices = [];
        foreach ($this->_config->getServices()[Converter::KEY_SERVICES] as $serviceClass => $serviceData) {
            $serviceName = $this->_helper->getServiceName($serviceClass);
            foreach ($serviceData as $methodName => $methodMetadata) {
                $soapServices[$serviceName][self::KEY_SERVICE_METHODS][$methodName] = [
                    self::KEY_METHOD => $methodName,
                    self::KEY_IS_REQUIRED => (bool)$methodMetadata[Converter::KEY_SECURE],
                    self::KEY_IS_SECURE => $methodMetadata[Converter::KEY_SECURE],
                    self::KEY_ACL_RESOURCES => $methodMetadata[Converter::KEY_ACL_RESOURCES],
                ];
                $soapServices[$serviceName][self::KEY_CLASS] = $serviceClass;
            }
            $reflectedMethodsMetadata = $this->_classReflector->reflectClassMethods(
                $serviceClass,
                $soapServices[$serviceName][self::KEY_SERVICE_METHODS]
            );
            $soapServices[$serviceName][self::KEY_SERVICE_METHODS] = array_merge_recursive(
                $soapServices[$serviceName][self::KEY_SERVICE_METHODS],
                $reflectedMethodsMetadata
            );
        }

        return $soapServices;
    }

    /**
     * Retrieve service method information, including service class, method name, and isSecure attribute value.
     *
     * @param string $soapOperation
     * @param array $requestedServices The list of requested services with their versions
     * @return array
     * @throws \Magento\Webapi\Exception
     */
    public function getServiceMethodInfo($soapOperation, $requestedServices)
    {
        $soapOperations = $this->_getSoapOperations($requestedServices);
        if (!isset($soapOperations[$soapOperation])) {
            throw new \Magento\Webapi\Exception(
                __('Operation "%1" not found.', $soapOperation),
                0,
                \Magento\Webapi\Exception::HTTP_NOT_FOUND
            );
        }
        return [
            self::KEY_CLASS => $soapOperations[$soapOperation][self::KEY_CLASS],
            self::KEY_METHOD => $soapOperations[$soapOperation][self::KEY_METHOD],
            self::KEY_IS_SECURE => $soapOperations[$soapOperation][self::KEY_IS_SECURE],
            self::KEY_ACL_RESOURCES => $soapOperations[$soapOperation][self::KEY_ACL_RESOURCES]
        ];
    }

    /**
     * Retrieve the list of services corresponding to specified services and their versions.
     *
     * @param array $requestedServices array('FooBarV1', 'OtherBazV2', ...)
     * @return array Filtered list of services
     */
    public function getRequestedSoapServices(array $requestedServices)
    {
        $services = [];
        $soapServicesConfig = $this->getSoapServicesConfig();
        foreach ($requestedServices as $serviceName) {
            if (isset($soapServicesConfig[$serviceName])) {
                $services[] = $soapServicesConfig[$serviceName];
            }
        }
        return $services;
    }

    /**
     * Generate SOAP operation name.
     *
     * @param string $interfaceName e.g. \Magento\Catalog\Api\ProductInterfaceV1
     * @param string $methodName e.g. create
     * @return string e.g. catalogProductCreate
     */
    public function getSoapOperation($interfaceName, $methodName)
    {
        $serviceName = $this->_helper->getServiceName($interfaceName);
        $operationName = $serviceName . ucfirst($methodName);
        return $operationName;
    }

    /**
     * Retrieve specific service interface data.
     *
     * @param string $serviceName
     * @return array
     * @throws \RuntimeException
     */
    public function getServiceMetadata($serviceName)
    {
        $soapServicesConfig = $this->getSoapServicesConfig();
        if (!isset($soapServicesConfig[$serviceName]) || !is_array($soapServicesConfig[$serviceName])) {
            throw new \RuntimeException(__('Requested service is not available: "%1"', $serviceName));
        }
        return $soapServicesConfig[$serviceName];
    }
}
