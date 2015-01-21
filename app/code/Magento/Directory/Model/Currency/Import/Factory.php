<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Currency\Import;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Directory\Model\Currency\Import\Config
     */
    protected $_serviceConfig;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Directory\Model\Currency\Import\Config $serviceConfig
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Directory\Model\Currency\Import\Config $serviceConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->_serviceConfig = $serviceConfig;
    }

    /**
     * Create new import object
     *
     * @param string $serviceName
     * @param array $data
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @return \Magento\Directory\Model\Currency\Import\ImportInterface
     */
    public function create($serviceName, array $data = [])
    {
        $serviceClass = $this->_serviceConfig->getServiceClass($serviceName);
        if (!$serviceClass) {
            throw new \InvalidArgumentException("Currency import service '{$serviceName}' is not defined.");
        }
        $serviceInstance = $this->_objectManager->create($serviceClass, $data);
        if (!$serviceInstance instanceof \Magento\Directory\Model\Currency\Import\ImportInterface) {
            throw new \UnexpectedValueException(
                "Class '{$serviceClass}' has to implement \\Magento\\Directory\\Model\\Currency\\Import\\ImportInterface."
            );
        }
        return $serviceInstance;
    }
}
