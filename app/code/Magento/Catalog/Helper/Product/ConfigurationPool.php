<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper\Product;

/**
 * @api
 */
class ConfigurationPool
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface[]
     */
    private $_instances = [];

    /**
     * @var array
     */
    private $instancesByType = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $instancesByType
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $instancesByType)
    {
        $this->_objectManager = $objectManager;
        $this->instancesByType = $instancesByType;
    }

    /**
     * @param string $className
     * @return \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface
     * @throws \LogicException
     */
    public function get($className)
    {
        if (!isset($this->_instances[$className])) {
            /** @var \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface $helperInstance */
            $helperInstance = $this->_objectManager->get($className);
            if (false ===
                $helperInstance instanceof \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface
            ) {
                throw new \LogicException(
                    "{$className} doesn't implement " .
                    \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface::class
                );
            }
            $this->_instances[$className] = $helperInstance;
        }
        return $this->_instances[$className];
    }

    /**
     * @param string $productType
     * @return Configuration\ConfigurationInterface
     */
    public function getByProductType($productType)
    {
        if (!isset($this->instancesByType[$productType])) {
            return $this->instancesByType['default'];
        }
        return $this->instancesByType[$productType];
    }
}
