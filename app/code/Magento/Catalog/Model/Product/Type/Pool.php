<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product type factory
 */
namespace Magento\Catalog\Model\Product\Type;

class Pool
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Gets product of particular type
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Catalog\Model\Product\Type\AbstractType
     * @throws \Magento\Framework\Model\Exception
     */
    public function get($className, array $data = [])
    {
        $product = $this->_objectManager->get($className, $data);

        if (!$product instanceof \Magento\Catalog\Model\Product\Type\AbstractType) {
            throw new \Magento\Framework\Model\Exception(
                $className . ' doesn\'t extends \Magento\Catalog\Model\Product\Type\AbstractType'
            );
        }
        return $product;
    }
}
