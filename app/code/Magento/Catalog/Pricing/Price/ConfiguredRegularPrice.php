<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Configured price model
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
     * @param ItemInterface|null $item
     * @param ConfiguredOptions|null $configuredOptions
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        ItemInterface $item = null,
        ConfiguredOptions $configuredOptions = null
    ) {
        $this->item = $item;
        $this->configuredOptions = $configuredOptions ?: ObjectManager::getInstance()->get(ConfiguredOptions::class);
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
     * Get value of configured options
     *
     * @return float
     */
    protected function getOptionsValue(): float
    {
        $basePrice = parent::getValue();
        return $this->configuredOptions->getItemOptionsValue($basePrice, $this->item);
    }

    /**
     * Price value of product with configured options
     *
     * @return bool|float
     */
    public function getValue()
    {
        return $this->item ? parent::getValue() + $this->getOptionsValue() : parent::getValue();
    }
}
