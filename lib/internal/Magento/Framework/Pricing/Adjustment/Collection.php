<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
