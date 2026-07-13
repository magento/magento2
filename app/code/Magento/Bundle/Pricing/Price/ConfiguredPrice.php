<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price as CatalogPrice;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceSelection;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * Configured price model
 * @api
 * @since 100.0.2
 */
class ConfiguredPrice extends CatalogPrice\FinalPrice implements ConfiguredPriceInterface
{
    /**
     * Price type configured
     */
    public const PRICE_CODE = self::CONFIGURED_PRICE_CODE;

    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;

    /**
     * @var null|ItemInterface
     */
    protected $item;

    /**
     * Serializer interface instance.
     *
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var ConfiguredPriceSelection
     */
    private $configuredPriceSelection;

    /**
     * @var DiscountCalculator
     */
    private $discountCalculator;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param BundleCalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param ItemInterface|null $item
     * @param JsonSerializer|null $serializer
     * @param ConfiguredPriceSelection|null $configuredPriceSelection
     * @param DiscountCalculator|null $discountCalculator
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        BundleCalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        ?ItemInterface $item = null,
        ?JsonSerializer $serializer = null,
        ?ConfiguredPriceSelection $configuredPriceSelection = null,
        ?DiscountCalculator $discountCalculator = null
    ) {
        $this->item = $item;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(JsonSerializer::class);
        $this->configuredPriceSelection = $configuredPriceSelection
            ?: \Magento\Framework\App\ObjectManager::getInstance()
                ->get(ConfiguredPriceSelection::class);
        $this->discountCalculator = $discountCalculator
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(DiscountCalculator::class);
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
    }

    /**
     * Set item to the model
     *
     * @param ItemInterface $item
     * @return $this
     */
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Get Options with attached Selections collection.
     *
     * @return array|\Magento\Bundle\Model\ResourceModel\Option\Collection
     */
    public function getOptions()
    {
        $bundleProduct = $this->product;
        $bundleOptions = [];
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $bundleProduct->getTypeInstance();
        $bundleOptionsIds = [];
        if ($this->item !== null) {
            // get bundle options
            $optionsQuoteItemOption = $this->item->getOptionByCode('bundle_option_ids');
            if ($optionsQuoteItemOption && $optionsQuoteItemOption->getValue()) {
                $bundleOptionsIds = $this->serializer->unserialize($optionsQuoteItemOption->getValue());
            }
        }
        if ($bundleOptionsIds) {
            /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $bundleProduct);
            // get and add bundle selections collection
            $selectionsQuoteItemOption = $this->item->getOptionByCode('bundle_selection_ids');
            $bundleSelectionIds = $this->serializer->unserialize($selectionsQuoteItemOption->getValue());
            if ($bundleSelectionIds) {
                $selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $bundleProduct);
                $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            }
        }

        return $bundleOptions;
    }

    /**
     * Option amount calculation for bundle product.
     *
     * @param float $baseValue
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getConfiguredAmount($baseValue = 0.)
    {
        $selectionPriceList = $this->configuredPriceSelection->getSelectionPriceList($this);

        return $this->calculator->calculateBundleAmount(
            $baseValue,
            $this->product,
            $selectionPriceList
        );
    }

    /**
     * Get price value
     *
     * @return float
     */
    public function getValue()
    {
        if ($this->item && $this->item->getProduct()->getId()) {
            $configuredOptionsAmount = $this->getConfiguredAmount()->getBaseAmount();
            return parent::getValue() + $this->discountCalculator->calculateDiscount(
                $this->item->getProduct(),
                $configuredOptionsAmount
            );
        }
        return parent::getValue();
    }

    /**
     * Get Amount for configured price which is included amount for all selected options
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount()
    {
        return $this->item ? $this->getConfiguredAmount($this->getBasePrice()->getValue()) : parent::getAmount();
    }
}
