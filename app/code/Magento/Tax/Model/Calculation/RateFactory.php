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

class RateFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(\Magento\Tax\Model\Calculation\Rate::class, $arguments);
    }
}
