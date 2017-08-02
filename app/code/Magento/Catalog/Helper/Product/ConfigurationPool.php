<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper\Product;

/**
 * @api
 * @since 2.0.0
 */
class ConfigurationPool
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface[]
     * @since 2.0.0
     */
    private $_instances = [];

    /**
     * @var array
     * @since 2.0.0
     */
    private $instancesByType = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $instancesByType
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getByProductType($productType)
    {
        if (!isset($this->instancesByType[$productType])) {
            return $this->instancesByType['default'];
        }
        return $this->instancesByType[$productType];
    }
}
