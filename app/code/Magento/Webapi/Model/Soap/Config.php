<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap;

use Magento\Webapi\Model\ServiceMetadata;

/**
 * Webapi Config Model for Soap.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Config
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    /**
     * List of SOAP operations available in the system
     *
     * @var array
     */
    protected $soapOperations;

    /** @var \Magento\Framework\Registry */
    protected $registry;

    /** @var  \Magento\Webapi\Model\ServiceMetadata */
    protected $serviceMetadata;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
    ) {
        $this->objectManager = $objectManager;
        $this->registry = $registry;
        $this->serviceMetadata = $serviceMetadata;
    }

    /**
     * Retrieve the list of SOAP operations available in the system
     *
     * @param array $requestedServices The list of requested services with their versions
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
    protected function getSoapOperations($requestedServices)
    {
        if (null == $this->soapOperations) {
            $this->soapOperations = [];
            foreach ($this->getRequestedSoapServices($requestedServices) as $serviceName => $serviceData) {
                foreach ($serviceData[ServiceMetadata::KEY_SERVICE_METHODS] as $methodData) {
                    $method = $methodData[ServiceMetadata::KEY_METHOD];
                    $class = $serviceData[ServiceMetadata::KEY_CLASS];
                    $operationName = $serviceName . ucfirst($method);
                    $this->soapOperations[$operationName] = [
                        ServiceMetadata::KEY_CLASS => $class,
                        ServiceMetadata::KEY_METHOD => $method,
                        ServiceMetadata::KEY_IS_SECURE => $methodData[ServiceMetadata::KEY_IS_SECURE],
                        ServiceMetadata::KEY_ACL_RESOURCES => $methodData[ServiceMetadata::KEY_ACL_RESOURCES],
                    ];
                }
            }
        }
        return $this->soapOperations;
    }

    /**
     * Retrieve service method information, including service class, method name, and isSecure attribute value.
     *
     * @param string $soapOperation
     * @param array $requestedServices The list of requested services with their versions
     * @return array
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function getServiceMethodInfo($soapOperation, $requestedServices)
    {
        $soapOperations = $this->getSoapOperations($requestedServices);
        if (!isset($soapOperations[$soapOperation])) {
            throw new \Magento\Framework\Webapi\Exception(
                __('Operation "%1" not found.', $soapOperation),
                0,
                \Magento\Framework\Webapi\Exception::HTTP_NOT_FOUND
            );
        }
        return [
            ServiceMetadata::KEY_CLASS => $soapOperations[$soapOperation][ServiceMetadata::KEY_CLASS],
            ServiceMetadata::KEY_METHOD => $soapOperations[$soapOperation][ServiceMetadata::KEY_METHOD],
            ServiceMetadata::KEY_IS_SECURE => $soapOperations[$soapOperation][ServiceMetadata::KEY_IS_SECURE],
            ServiceMetadata::KEY_ACL_RESOURCES => $soapOperations[$soapOperation][ServiceMetadata::KEY_ACL_RESOURCES]
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
        $soapServicesConfig = $this->serviceMetadata->getServicesConfig();
        foreach ($requestedServices as $serviceName) {
            if (isset($soapServicesConfig[$serviceName])) {
                $services[$serviceName] = $soapServicesConfig[$serviceName];
            }
        }
        return $services;
    }

    /**
     * Generate SOAP operation name.
     *
     * @param string $interfaceName e.g. \Magento\Catalog\Api\ProductInterfaceV1
     * @param string $methodName e.g. create
     * @param string $version
     * @return string e.g. catalogProductCreate
     */
    public function getSoapOperation($interfaceName, $methodName, $version)
    {
        $serviceName = $this->serviceMetadata->getServiceName($interfaceName, $version);
        $operationName = $serviceName . ucfirst($methodName);
        return $operationName;
    }
}
