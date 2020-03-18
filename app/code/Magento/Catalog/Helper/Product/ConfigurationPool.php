<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * @api
 * @since 100.0.2
 */
class ConfigurationPool
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var array
     */
    private $instancesByType;

    /**
     * @var ConfigurationInterface[]
     */
    private $instances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $instancesByType
     */
    public function __construct(ObjectManagerInterface $objectManager, array $instancesByType)
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
        if (!isset($this->instances[$className])) {
            /** @var ConfigurationInterface $helperInstance */
            $helperInstance = $this->_objectManager->get($className);
            if (false ===
                $helperInstance instanceof ConfigurationInterface
            ) {
                throw new \LogicException(
                    "{$className} doesn't implement " .
                    ConfigurationInterface::class
                );
            }
            $this->instances[$className] = $helperInstance;
        }
        return $this->instances[$className];
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
