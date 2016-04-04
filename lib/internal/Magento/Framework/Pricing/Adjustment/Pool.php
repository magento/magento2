<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Adjustment;

use Magento\Framework\Pricing\Adjustment\Factory as AdjustmentFactory;

/**
 * Global adjustment pool model
 */
class Pool
{
    /**
     * Default adjustment sort order
     */
    const DEFAULT_SORT_ORDER = -1;

    /**
     * @var AdjustmentFactory
     */
    protected $adjustmentFactory;

    /**
     * @var array[]
     */
    protected $adjustments;

    /**
     * @var AdjustmentInterface[]
     */
    protected $adjustmentInstances;

    /**
     * @param AdjustmentFactory $adjustmentFactory
     * @param array[] $adjustments
     */
    public function __construct(AdjustmentFactory $adjustmentFactory, $adjustments = [])
    {
        $this->adjustmentFactory = $adjustmentFactory;
        $this->adjustments = $adjustments;
    }

    /**
     * @return AdjustmentInterface[]
     */
    public function getAdjustments()
    {
        if (!isset($this->adjustmentInstances)) {
            $this->adjustmentInstances = $this->createAdjustments(array_keys($this->adjustments));
        }
        return $this->adjustmentInstances;
    }

    /**
     * @param string $adjustmentCode
     * @return AdjustmentInterface
     * @throws \InvalidArgumentException
     */
    public function getAdjustmentByCode($adjustmentCode)
    {
        if (!isset($this->adjustmentInstances)) {
            $this->adjustmentInstances = $this->createAdjustments(array_keys($this->adjustments));
        }
        if (!isset($this->adjustmentInstances[$adjustmentCode])) {
            throw new \InvalidArgumentException(sprintf('Price adjustment "%s" is not registered', $adjustmentCode));
        }
        return $this->adjustmentInstances[$adjustmentCode];
    }

    /**
     * Instantiate adjustments
     *
     * @param string[] $adjustments
     * @return AdjustmentInterface[]
     */
    protected function createAdjustments($adjustments)
    {
        $instances = [];
        foreach ($adjustments as $code) {
            if (!isset($instances[$code])) {
                $instances[$code] = $this->createAdjustment($code);
            }
        }
        return $instances;
    }

    /**
     * Create adjustment by code
     *
     * @param string $adjustmentCode
     * @return AdjustmentInterface
     */
    protected function createAdjustment($adjustmentCode)
    {
        $adjustmentData = $this->adjustments[$adjustmentCode];
        $sortOrder = isset($adjustmentData['sortOrder']) ? (int)$adjustmentData['sortOrder'] : self::DEFAULT_SORT_ORDER;
        return $this->adjustmentFactory->create(
            $adjustmentData['className'],
            [
                'sortOrder' => $sortOrder
            ]
        );
    }
}
