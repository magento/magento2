<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\PriceInfo;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Adjustment\Collection;
use Magento\Framework\Pricing\Price\Collection as PriceCollection;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfoInterface;

/**
 * Class Base
 * Price info base model
 */
class Base implements PriceInfoInterface
{
    /**
     * @var PriceCollection
     */
    protected $priceCollection;

    /**
     * @var Collection
     */
    protected $adjustmentCollection;

    /**
     * @param PriceCollection $prices
     * @param Collection $adjustmentCollection
     */
    public function __construct(
        PriceCollection $prices,
        Collection $adjustmentCollection
    ) {
        $this->adjustmentCollection = $adjustmentCollection;
        $this->priceCollection = $prices;
    }

    /**
     * Returns array of prices
     *
     * @return PriceCollection
     */
    public function getPrices()
    {
        return $this->priceCollection;
    }

    /**
     * Returns price by code
     *
     * @param string $priceCode
     * @return PriceInterface
     */
    public function getPrice($priceCode)
    {
        return $this->priceCollection->get($priceCode);
    }

    /**
     * Get all registered adjustments
     *
     * @return AdjustmentInterface[]
     */
    public function getAdjustments()
    {
        return $this->adjustmentCollection->getItems();
    }

    /**
     * Get adjustment by code
     *
     * @param string $adjustmentCode
     * @throws \InvalidArgumentException
     * @return AdjustmentInterface
     */
    public function getAdjustment($adjustmentCode)
    {
        return $this->adjustmentCollection->getItemByCode($adjustmentCode);
    }
}
