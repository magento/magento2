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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

    /** @var \Magento\Webapi\Helper\Data */
    protected $_helper;

    /** @var \Magento\Webapi\Model\Config\ClassReflector */
    protected $_classReflector;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\Webapi\Model\Config $config
     * @param \Magento\Webapi\Model\Config\ClassReflector $classReflector
     * @param \Magento\Webapi\Helper\Data $helper
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\App\Filesystem $filesystem,
        \Magento\Webapi\Model\Config $config,
        \Magento\Webapi\Model\Config\ClassReflector $classReflector,
        \Magento\Webapi\Helper\Data $helper
    ) {
        // TODO: Check if Service specific XSD is already cached
        $this->modulesDirectory = $filesystem->getDirectoryRead(\Magento\App\Filesystem::MODULES_DIR);
        $this->_config = $config;
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_classReflector = $classReflector;
        $this->_initServicesMetadata();
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
     *
     * @return array
     */
    protected function _initServicesMetadata()
    {
        // TODO: Implement caching if this approach is approved
        if (is_null($this->_soapServices)) {
            $this->_soapServices = array();
            foreach ($this->_config->getServices() as $serviceData) {
                $serviceClass = $serviceData[Converter::KEY_SERVICE_CLASS];
                $serviceName = $this->_helper->getServiceName($serviceClass);
                foreach ($serviceData[Converter::KEY_SERVICE_METHODS] as $methodMetadata) {
                    // TODO: Simplify the structure in SOAP. Currently it is unified in SOAP and REST
                    $methodName = $methodMetadata[Converter::KEY_SERVICE_METHOD];
                    $this->_soapServices[$serviceName]['methods'][$methodName] = array(
                        self::KEY_METHOD => $methodName,
                        self::KEY_IS_REQUIRED => (bool)$methodMetadata[Converter::KEY_IS_SECURE],
                        self::KEY_IS_SECURE => $methodMetadata[Converter::KEY_IS_SECURE],
                        self::KEY_ACL_RESOURCES => $methodMetadata[Converter::KEY_ACL_RESOURCES]
                    );
                    $this->_soapServices[$serviceName][self::KEY_CLASS] = $serviceClass;
                };
                $reflectedMethodsMetadata = $this->_classReflector->reflectClassMethods(
                    $serviceClass,
                    $this->_soapServices[$serviceName]['methods']
                );
                // TODO: Consider service documentation extraction via reflection
                $this->_soapServices[$serviceName]['methods'] = array_merge_recursive(
                    $this->_soapServices[$serviceName]['methods'],
                    $reflectedMethodsMetadata
                );
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
            if (isset($this->_soapServices[$serviceName])) {
                $services[] = $this->_soapServices[$serviceName];
            }
        }
        return $services;
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
        if (!isset($this->_soapServices[$serviceName]) || !is_array($this->_soapServices[$serviceName])) {
            throw new \RuntimeException(__('Requested service is not available: "%1"', $serviceName));
        }
        return $this->_soapServices[$serviceName];
    }
}
