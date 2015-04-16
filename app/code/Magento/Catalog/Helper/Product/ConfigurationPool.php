<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper\Product;

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
    private $helpers = [];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $helpers
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $helpers)
    {
        $this->_objectManager = $objectManager;
        $this->helpers = $helpers;
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
                    "\\Magento\\Catalog\\Helper\\Product\\Configuration\\ConfigurationInterface"
                );
            }
            $this->_instances[$className] = $helperInstance;
        }
        return $this->_instances[$className];
    }

    /**
     * @param $productType
     * @return Configuration\ConfigurationInterface
     */
    public function getProductConfigurationHelper($productType)
    {
        if (!isset($this->helpers[$productType])) {
            return $this->helpers['default'];
        }
        return $this->helpers[$productType];
    }
}
