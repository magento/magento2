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

/**
 * Configured regular price model
 */
class ConfiguredRegularPrice extends RegularPrice implements ConfiguredPriceInterface
{
    /**
     * Price type configured
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
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param ConfiguredOptions $configuredOptions
     * @param ItemInterface|null $item
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
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
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }
    
    /**
     * Price value of product with configured options
     *
     * @return bool|float
     */
    public function getValue()
    {
        $basePrice = parent::getValue();
        return $this->item
            ? $basePrice + $this->configuredOptions->getItemOptionsValue($basePrice, $this->item)
            : $basePrice;
    }
}
