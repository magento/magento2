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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Soap;

use Magento\Webapi\Model\Config\Converter,
    Magento\Filesystem\Directory\ReadInterface;

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
    const KEY_METHOD = 'method';
    const KEY_IS_REQUIRED = 'inputRequired';
    const KEY_ACL_RESOURCES = 'resources';
    /**#@-*/

    /** @var ReadInterface */
    protected $modulesDirectory;

    /** @var \Magento\Webapi\Model\Config */
    protected $_config;

    /** @var \Magento\ObjectManager */
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

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Webapi\Model\Config $config
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Filesystem $filesystem,
        \Magento\Webapi\Model\Config $config
    ) {
        // TODO: Check if Service specific XSD is already cached
        $this->modulesDirectory = $filesystem->getDirectoryRead(\Magento\Filesystem::MODULES);
        $this->_config = $config;
        $this->_objectManager = $objectManager;
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
            $this->_soapOperations = array();
            foreach ($this->getRequestedSoapServices($requestedService) as $serviceData) {
                foreach ($serviceData[Converter::KEY_SERVICE_METHODS] as $methodData) {
                    $method = $methodData[Converter::KEY_SERVICE_METHOD];
                    $class = $serviceData[Converter::KEY_SERVICE_CLASS];
                    $operationName = $this->getSoapOperation($class, $method);
                    $this->_soapOperations[$operationName] = array(
                        self::KEY_CLASS => $class,
                        self::KEY_METHOD => $method,
                        self::KEY_IS_SECURE => $methodData[Converter::KEY_IS_SECURE],
                        self::KEY_ACL_RESOURCES => $methodData[Converter::KEY_ACL_RESOURCES]
                    );
                }
            }
        }
        return $this->_soapOperations;
    }

    /**
     * Collect the list of services with their operations available in SOAP.
     * The list of services is taken from webapi.xml configuration files.
     * The list of methods in contrast to REST is taken from PHP Interface using reflection.
     *
     * @return array
     */
    protected function _getSoapServices()
    {
        // TODO: Implement caching if this approach is approved
        if (is_null($this->_soapServices)) {
            $this->_soapServices = array();
            foreach ($this->_config->getServices() as $serviceData) {
                $serviceClass = $serviceData[Converter::KEY_SERVICE_CLASS];
                foreach ($serviceData[Converter::KEY_SERVICE_METHODS] as $methodMetadata) {
                    // TODO: Simplify the structure in SOAP. Currently it is unified in SOAP and REST
                    $methodName = $methodMetadata[Converter::KEY_SERVICE_METHOD];
                    $this->_soapServices[$serviceClass]['methods'][$methodName] = array(
                        self::KEY_METHOD => $methodName,
                        self::KEY_IS_REQUIRED => (bool)$methodMetadata[Converter::KEY_IS_SECURE],
                        self::KEY_IS_SECURE => $methodMetadata[Converter::KEY_IS_SECURE],
                        self::KEY_ACL_RESOURCES => $methodMetadata[Converter::KEY_ACL_RESOURCES]
                    );
                    $this->_soapServices[$serviceClass][self::KEY_CLASS] = $serviceClass;
                };
            };
        }
        return $this->_soapServices;
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
        return array(
            self::KEY_CLASS => $soapOperations[$soapOperation][self::KEY_CLASS],
            self::KEY_METHOD => $soapOperations[$soapOperation][self::KEY_METHOD],
            self::KEY_IS_SECURE => $soapOperations[$soapOperation][self::KEY_IS_SECURE],
            self::KEY_ACL_RESOURCES => $soapOperations[$soapOperation][self::KEY_ACL_RESOURCES]
        );
    }

    /**
     * Retrieve the list of services corresponding to specified services and their versions.
     *
     * @param array $requestedServices array('FooBarV1', 'OtherBazV2', ...)
     * @return array Filtered list of services
     */
    public function getRequestedSoapServices(array $requestedServices)
    {
        $services = array();
        foreach ($requestedServices as $serviceName) {
            foreach ($this->_getSoapServices() as $serviceData) {
                $serviceWithVersion = $this->getServiceName($serviceData[self::KEY_CLASS]);
                if ($serviceWithVersion === $serviceName) {
                    $services[] = $serviceData;
                }
            }
        }
        return $services;
    }

    /**
     * Load and return Service XSD for the provided Service Class
     *
     * @param $serviceClass
     * @return \DOMDocument
     */
    public function getServiceSchemaDOM($serviceClass)
    {
        // TODO: Change pattern to match interface instead of class. Think about sub-services.
        if (!preg_match(\Magento\Webapi\Model\Config::SERVICE_CLASS_PATTERN, $serviceClass, $matches)) {
            // TODO: Generate exception when error handling strategy is defined
        }

        $vendorName = $matches[1];
        $moduleName = $matches[2];
        /** Convert "_Catalog_Attribute" into "Catalog/Attribute" */
        $servicePath = str_replace('_', '/', ltrim($matches[3], '_'));
        $version = $matches[4];
        $schemaPath = "{$vendorName}/{$moduleName}/etc/schema/{$servicePath}{$version}.xsd";

        if ($this->modulesDirectory->isFile($schemaPath)) {
            $schema = $this->modulesDirectory->readFile($schemaPath);
        } else {
            $schema = '';
        }

        // TODO: Should happen only once the cache is in place
        $serviceSchema = $this->_objectManager->create('DOMDocument');
        $serviceSchema->loadXML($schema);

        return $serviceSchema;
    }

    /**
     * Generate SOAP operation name.
     *
     * @param string $interfaceName e.g. \Magento\Catalog\Service\ProductInterfaceV1
     * @param string $methodName e.g. create
     * @return string e.g. catalogProductCreate
     */
    public function getSoapOperation($interfaceName, $methodName)
    {
        $serviceName = $this->getServiceName($interfaceName);
        $operationName = $serviceName . ucfirst($methodName);
        return $operationName;
    }

    /**
     * Translate service interface name into service name.
     * Example:
     * <pre>
     * - \Magento\Customer\Service\CustomerV1Interface         => customer          // $preserveVersion == false
     * - \Magento\Customer\Service\Customer\AddressV1Interface => customerAddressV1 // $preserveVersion == true
     * - \Magento\Catalog\Service\ProductV2Interface           => catalogProductV2  // $preserveVersion == true
     * </pre>
     *
     * @param string $interfaceName
     * @param bool $preserveVersion Should version be preserved during interface name conversion into service name
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getServiceName($interfaceName, $preserveVersion = true)
    {
        $serviceNameParts = $this->getServiceNameParts($interfaceName, $preserveVersion);
        return lcfirst(implode('', $serviceNameParts));
    }

    /**
     * Identify the list of service name parts including sub-services using class name.
     *
     * Examples of input/output pairs: <br/>
     * - 'Magento\Customer\Service\Customer\AddressV1Interface' => array('Customer', 'Address', 'V1') <br/>
     * - 'Vendor\Customer\Service\Customer\AddressV1Interface' => array('VendorCustomer', 'Address', 'V1) <br/>
     * - 'Magento\Catalog\Service\ProductV2Interface' => array('CatalogProduct', 'V2')
     *
     * @param string $className
     * @param bool $preserveVersion Should version be preserved during class name conversion into service name
     * @return array
     * @throws \InvalidArgumentException When class is not valid API service.
     */
    public function getServiceNameParts($className, $preserveVersion = false)
    {
        if (preg_match(\Magento\Webapi\Model\Config::SERVICE_CLASS_PATTERN, $className, $matches)) {
            $moduleNamespace = $matches[1];
            $moduleName = $matches[2];
            $moduleNamespace = ($moduleNamespace == 'Magento') ? '' : $moduleNamespace;
            $serviceNameParts = explode('\\', trim($matches[3], '\\'));
            if ($moduleName == $serviceNameParts[0]) {
                /** Avoid duplication of words in service name */
                $moduleName = '';
            }
            $parentServiceName = $moduleNamespace . $moduleName . array_shift($serviceNameParts);
            array_unshift($serviceNameParts, $parentServiceName);
            if ($preserveVersion) {
                $serviceVersion = $matches[4];
                $serviceNameParts[] = $serviceVersion;
            }
            return $serviceNameParts;
        }
        throw new \InvalidArgumentException(sprintf('The service interface name "%s" is invalid.', $className));
    }
}
