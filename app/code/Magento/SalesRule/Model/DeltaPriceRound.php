<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Round price and save rounding operation delta.
 */
class DeltaPriceRound implements ResetAfterRequestInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var float[]|null
     */
    private $roundingDeltas;

    /**
     * Constructor
     *
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->roundingDeltas = null;
    }

    /**
     * Round price based on previous rounding operation delta.
     *
     * @param float $price
     * @param string $type
     * @return float
     */
    public function round(float $price, string $type): float
    {
        if ($price) {
            // initialize the delta to a small number to avoid non-deterministic behavior with rounding of 0.5
            $delta = isset($this->roundingDeltas[$type]) ? $this->roundingDeltas[$type] : 0.000001;
            $price += $delta;
            $roundPrice = $this->priceCurrency->round($price);
            $this->roundingDeltas[$type] = $price - $roundPrice;
            $price = $roundPrice;
        }

        return $price;
    }

    /**
     * Reset all deltas.
     *
     * @return void
     */
    public function resetAll(): void
    {
        $this->roundingDeltas = [];
    }

    /**
     * Reset deltas by type.
     *
     * @param string $type
     * @return void
     */
    public function reset(string $type): void
    {
        if (isset($this->roundingDeltas[$type])) {
            unset($this->roundingDeltas[$type]);
        }
    }
}
