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
class ConfiguredPrice extends FinalPrice implements ConfiguredPriceInterface
{
    /**
     * Price type configured
     */
    const PRICE_CODE = self::CONFIGURED_PRICE_CODE;

    /**
     * @var null|ItemInterface
     */
    protected $item;

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
     * Get value of configured options.
     *
     * @deprecated ConfiguredOptions::getItemOptionsValue is used instead
     * @return float
     */
    protected function getOptionsValue(): float
    {
        $product = $this->item->getProduct();
        $value = 0.;
        $basePrice = parent::getValue();
        $optionIds = $this->item->getOptionByCode('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $product->getOptionById($optionId);
                if ($option) {
                    $itemOption = $this->item->getOptionByCode('option_' . $option->getId());
                    /** @var $group \Magento\Catalog\Model\Product\Option\Type\DefaultType */
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItem($this->item)
                        ->setConfigurationItemOption($itemOption);
                    $value += $group->getOptionPrice($itemOption->getValue(), $basePrice);
                }
            }
        }

        return $value;
    }

    /**
     * Price value of product with configured options.
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
