<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Configured regular price model.
 */
class ConfiguredRegularPrice extends RegularPrice implements ConfiguredPriceInterface
{
    /**
     * Price type configured.
     */
    public const PRICE_CODE = self::CONFIGURED_REGULAR_PRICE_CODE;

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
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfiguredOptions $configuredOptions
     * @param ItemInterface|null $item
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        ConfiguredOptions $configuredOptions,
        ?ItemInterface $item = null
    ) {
        $this->item = $item;
        $this->configuredOptions = $configuredOptions;
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
    }

    /**
     * Set Item Implementation
     *
     * @param ItemInterface $item
     * @return $this
     */
    public function setItem(ItemInterface $item) : ConfiguredRegularPrice
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Price value of product with configured options.
     *
     * @return bool|float
     */
    public function getValue()
    {
        $basePrice = parent::getValue();

        return $this->item && $basePrice !== false
            ? $basePrice + $this->configuredOptions->getItemOptionsValue($basePrice, $this->item)
            : $basePrice;
    }
}
