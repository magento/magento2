<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Adjustment;

/**
 * Adjustment collection model
 *
 * @api
 * @since 2.0.0
 */
class Collection
{
    /**
     * @var Pool
     * @since 2.0.0
     */
    protected $adjustmentPool;

    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $adjustments;

    /**
     * @var AdjustmentInterface[]
     * @since 2.0.0
     */
    protected $adjustmentInstances;

    /**
     * @param Pool $adjustmentPool
     * @param string[] $adjustments
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function sortAdjustments(AdjustmentInterface $firstAdjustment, AdjustmentInterface $secondAdjustment)
    {
        if ($firstAdjustment->getSortOrder() === \Magento\Framework\Pricing\Adjustment\Pool::DEFAULT_SORT_ORDER) {
            return 1;
        }
        return $firstAdjustment->getSortOrder() > $secondAdjustment->getSortOrder() ? 1 : -1;
    }
}
