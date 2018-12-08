<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
<<<<<<< HEAD

/**
 * Configured regular price model
=======
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Configured regular price model.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class ConfiguredRegularPrice extends RegularPrice implements ConfiguredPriceInterface
{
    /**
<<<<<<< HEAD
     * Price type configured
=======
     * Price type configured.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    const PRICE_CODE = self::CONFIGURED_REGULAR_PRICE_CODE;

    /**
     * @var null|ItemInterface
     */
    private $item;

    /**
     * @var ConfiguredOptions
     */
    private $configuredOptions;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
<<<<<<< HEAD
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
=======
     * @param PriceCurrencyInterface $priceCurrency
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @param ConfiguredOptions $configuredOptions
     * @param ItemInterface|null $item
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
<<<<<<< HEAD
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
=======
        PriceCurrencyInterface $priceCurrency,
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        ConfiguredOptions $configuredOptions,
        ItemInterface $item = null
    ) {
        $this->item = $item;
        $this->configuredOptions = $configuredOptions;
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
    }

    /**
     * @param ItemInterface $item
     * @return $this
     */
<<<<<<< HEAD
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
=======
    public function setItem(ItemInterface $item) : ConfiguredRegularPrice
    {
        $this->item = $item;

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        return $this;
    }

    /**
<<<<<<< HEAD
     * Price value of product with configured options
=======
     * Price value of product with configured options.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @return bool|float
     */
    public function getValue()
    {
        $basePrice = parent::getValue();
<<<<<<< HEAD
=======

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        return $this->item && $basePrice !== false
            ? $basePrice + $this->configuredOptions->getItemOptionsValue($basePrice, $this->item)
            : $basePrice;
    }
}
