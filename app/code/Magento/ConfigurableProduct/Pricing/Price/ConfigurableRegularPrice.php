<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ConfigurableMaxPriceCalculator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * Class RegularPrice
 */
class ConfigurableRegularPrice extends AbstractPrice implements
    ConfigurableRegularPriceInterface,
    ResetAfterRequestInterface
{
    /**
     * Price type
     */
    public const PRICE_CODE = 'regular_price';

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $maxRegularAmount;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $minRegularAmount;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface
     */
    protected $priceResolver;

    /**
     * @var ConfigurableOptionsProviderInterface
     */
    private $configurableOptionsProvider;

    /**
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    /**
     * @var ConfigurableMaxPriceCalculator
     */
    private $configurableMaxPriceCalculator;

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $saleableItem
     * @param float $quantity
     * @param \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param PriceResolverInterface $priceResolver
     * @param ConfigurableMaxPriceCalculator $configurableMaxPriceCalculator
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
     */
    public function __construct(
        \Magento\Framework\Pricing\SaleableInterface $saleableItem,
        $quantity,
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        PriceResolverInterface $priceResolver,
        ConfigurableMaxPriceCalculator $configurableMaxPriceCalculator,
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider = null
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->priceResolver = $priceResolver;
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider ?:
            ObjectManager::getInstance()->get(LowestPriceOptionsProviderInterface::class);
        $this->configurableMaxPriceCalculator = $configurableMaxPriceCalculator;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        if (!isset($this->values[$this->product->getId()])) {
            $this->values[$this->product->getId()] = $this->priceResolver->resolvePrice($this->product);
        }

        return $this->values[$this->product->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getAmount()
    {
        return $this->getMinRegularAmount();
    }

    /**
     * @inheritdoc
     */
    public function getMaxRegularAmount()
    {
        if (null === $this->maxRegularAmount) {
            $this->maxRegularAmount = $this->doGetMaxRegularAmount() ?: false;
        }
        return $this->maxRegularAmount;
    }

    /**
     * Get max regular amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function doGetMaxRegularAmount()
    {
        $maxAmount = null;
        foreach ($this->getUsedProducts() as $product) {
            $childPriceAmount = $product->getPriceInfo()->getPrice(self::PRICE_CODE)->getAmount();
            if (!$maxAmount || ($childPriceAmount->getValue() > $maxAmount->getValue())) {
                $maxAmount = $childPriceAmount;
            }
        }
        return $maxAmount;
    }

    /**
     * @inheritdoc
     */
    public function getMinRegularAmount()
    {
        if (null === $this->minRegularAmount) {
            $this->minRegularAmount = $this->doGetMinRegularAmount() ?: parent::getAmount();
        }
        return $this->minRegularAmount;
    }

    /**
     * Get min regular amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function doGetMinRegularAmount()
    {
        $minAmount = null;
        foreach ($this->lowestPriceOptionsProvider->getProducts($this->product) as $product) {
            $childPriceAmount = $product->getPriceInfo()->getPrice(self::PRICE_CODE)->getAmount();
            if (!$minAmount || ($childPriceAmount->getValue() < $minAmount->getValue())) {
                $minAmount = $childPriceAmount;
            }
        }
        return $minAmount;
    }

    /**
     * Get children simple products
     *
     * @return Product[]
     */
    protected function getUsedProducts()
    {
        return $this->getConfigurableOptionsProvider()->getProducts($this->product);
    }

    /**
     * Retrieve Configurable Option Provider
     *
     * @return \Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface
     * @deprecated 100.1.1
     * @see we don't recommend this approach anymore
     */
    private function getConfigurableOptionsProvider()
    {
        if (null === $this->configurableOptionsProvider) {
            $this->configurableOptionsProvider = ObjectManager::getInstance()
                ->get(ConfigurableOptionsProviderInterface::class);
        }
        return $this->configurableOptionsProvider;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->values = [];
    }

    /**
     * Check whether Configurable Product have more than one children products
     *
     * @param SaleableInterface $product
     * @return bool
     */
    public function isChildProductsOfEqualPrices(SaleableInterface $product): bool
    {
        $minPrice = $this->getMinRegularAmount()->getValue();
        $final_price = $product->getFinalPrice();
        $productId = $product->getId();
        if ($final_price < $minPrice) {
            return false;
        }
        $attributes = $product->getTypeInstance()->getConfigurableAttributes($product);
        $items = $attributes->getItems();
        $options = reset($items);
        $maxPrice = $this->configurableMaxPriceCalculator->getMaxPriceForConfigurableProduct($productId);
        if ($maxPrice == 0) {
            $maxPrice = $this->getMaxRegularAmount()->getValue();
        }
        return (count($options->getOptions()) > 1) && $minPrice == $maxPrice;
    }
}
