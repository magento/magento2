<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Adjustment;

/**
 * Adjustment factory
 */
class Factory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create shared price adjustment
     *
     * @param string $className
     * @param array $arguments
     * @return \Magento\Framework\Pricing\Adjustment\AdjustmentInterface
     * @throws \InvalidArgumentException
     */
    public function create($className, array $arguments = [])
    {
        $adjustment = $this->objectManager->create($className, $arguments);
        if (!$adjustment instanceof AdjustmentInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Framework\Pricing\Adjustment\AdjustmentInterface'
            );
        }
        return $adjustment;
    }
}
