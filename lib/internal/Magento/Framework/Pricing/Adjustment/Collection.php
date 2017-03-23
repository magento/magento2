<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Adjustment;

/**
 * Adjustment collection model
 */
class Collection
{
    /**
     * @var Pool
     */
    protected $adjustmentPool;

    /**
     * @var string[]
     */
    protected $adjustments;

    /**
     * @var AdjustmentInterface[]
     */
    protected $adjustmentInstances;

    /**
     * @param Pool $adjustmentPool
     * @param string[] $adjustments
     */
    public function __construct(
        Pool $adjustmentPool,
        array $adjustments
    ) {
        $this->adjustmentPool = $adjustmentPool;
        $this->adjustments = $adjustments;
    }

    /**
     * @return AdjustmentInterface[]
     */
    public function getItems()
    {
        if ($this->adjustmentInstances === null) {
            $this->adjustmentInstances = $this->fetchAdjustments($this->adjustments);
        }
        return $this->adjustmentInstances;
    }

    /**
     * Get adjustment by code
     *
     * @param string $adjustmentCode
     * @throws \InvalidArgumentException
     * @return AdjustmentInterface
     */
    public function getItemByCode($adjustmentCode)
    {
        if ($this->adjustmentInstances === null) {
            $this->adjustmentInstances = $this->fetchAdjustments($this->adjustments);
        }

        if (!isset($this->adjustmentInstances[$adjustmentCode])) {
            throw new \InvalidArgumentException(sprintf('Price adjustment "%s" is not found', $adjustmentCode));
        }
        return $this->adjustmentInstances[$adjustmentCode];
    }

    /**
     * @param string[] $adjustments
     * @return AdjustmentInterface[]
     */
    protected function fetchAdjustments($adjustments)
    {
        $instances = [];
        foreach ($adjustments as $code) {
            $instances[$code] = $this->adjustmentPool->getAdjustmentByCode($code);
        }

        uasort($instances, [$this, 'sortAdjustments']);

        return $instances;
    }

    /**
     * Sort adjustments
     *
     * @param AdjustmentInterface $firstAdjustment
     * @param AdjustmentInterface $secondAdjustment
     * @return int
     */
    protected function sortAdjustments(AdjustmentInterface $firstAdjustment, AdjustmentInterface $secondAdjustment)
    {
        if ($firstAdjustment->getSortOrder() === \Magento\Framework\Pricing\Adjustment\Pool::DEFAULT_SORT_ORDER) {
            return 1;
        }
        return $firstAdjustment->getSortOrder() > $secondAdjustment->getSortOrder() ? 1 : -1;
    }
}
