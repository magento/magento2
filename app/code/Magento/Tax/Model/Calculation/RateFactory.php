<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax rate factory
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\Calculation;

/**
 * Class \Magento\Tax\Model\Calculation\RateFactory
 *
 * @since 2.0.0
 */
class RateFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new tax rate model
     *
     * @param array $arguments
     * @return \Magento\Tax\Model\Calculation\Rate
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(\Magento\Tax\Model\Calculation\Rate::class, $arguments);
    }
}
