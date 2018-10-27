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
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Configured regular price model.
=======

/**
 * Configured regular price model
>>>>>>> upstream/2.2-develop
 */
class ConfiguredRegularPrice extends RegularPrice implements ConfiguredPriceInterface
{
    /**
<<<<<<< HEAD
     * Price type configured.
=======
     * Price type configured
>>>>>>> upstream/2.2-develop
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
     * @param PriceCurrencyInterface $priceCurrency
=======
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
>>>>>>> upstream/2.2-develop
     * @param ConfiguredOptions $configuredOptions
     * @param ItemInterface|null $item
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
<<<<<<< HEAD
        PriceCurrencyInterface $priceCurrency,
=======
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
>>>>>>> upstream/2.2-develop
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
    public function setItem(ItemInterface $item) : ConfiguredRegularPrice
    {
        $this->item = $item;

=======
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
>>>>>>> upstream/2.2-develop
        return $this;
    }

    /**
<<<<<<< HEAD
     * Price value of product with configured options.
=======
     * Price value of product with configured options
>>>>>>> upstream/2.2-develop
     *
     * @return bool|float
     */
    public function getValue()
    {
        $basePrice = parent::getValue();
<<<<<<< HEAD

=======
>>>>>>> upstream/2.2-develop
        return $this->item && $basePrice !== false
            ? $basePrice + $this->configuredOptions->getItemOptionsValue($basePrice, $this->item)
            : $basePrice;
    }
}
