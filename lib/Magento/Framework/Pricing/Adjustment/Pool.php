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

use Magento\Framework\Pricing\Adjustment\Factory as AdjustmentFactory;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;

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
