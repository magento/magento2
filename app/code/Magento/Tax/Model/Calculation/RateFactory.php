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

use Magento\Framework\ObjectManagerInterface;
use Magento\Tax\Model\Calculation\Rate as ModelCalculationRate;

class RateFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new tax rate model
     *
     * @param array $arguments
     * @return ModelCalculationRate
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(ModelCalculationRate::class, $arguments);
    }
}
