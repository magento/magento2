<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);

=======
>>>>>>> upstream/2.2-develop
namespace Magento\SalesRule\Model;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Round price and save rounding operation delta.
 */
class DeltaPriceRound
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var float[]
     */
    private $roundingDeltas;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Round price based on previous rounding operation delta.
     *
     * @param float $price
     * @param string $type
     * @return float
     */
<<<<<<< HEAD
    public function round(float $price, string $type): float
=======
    public function round($price, $type)
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
    public function resetAll(): void
=======
    public function resetAll()
>>>>>>> upstream/2.2-develop
    {
        $this->roundingDeltas = [];
    }

    /**
     * Reset deltas by type.
     *
     * @param string $type
     * @return void
     */
<<<<<<< HEAD
    public function reset(string $type): void
=======
    public function reset($type)
>>>>>>> upstream/2.2-develop
    {
        if (isset($this->roundingDeltas[$type])) {
            unset($this->roundingDeltas[$type]);
        }
    }
}
