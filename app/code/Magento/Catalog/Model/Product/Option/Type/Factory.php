<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product option factory
 */
namespace Magento\Catalog\Model\Product\Option\Type;

/**
 * Class \Magento\Catalog\Model\Product\Option\Type\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create product option
     *
     * @param string $className
     * @param array $data
     * @return \Magento\Catalog\Model\Product\Option\Type\DefaultType
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function create($className, array $data = [])
    {
        $option = $this->_objectManager->create($className, $data);

        if (!$option instanceof \Magento\Catalog\Model\Product\Option\Type\DefaultType) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('%1 doesn\'t extends \Magento\Catalog\Model\Product\Option\Type\DefaultType', $className)
            );
        }
        return $option;
    }
}
