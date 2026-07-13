<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
     * @var array<int, bool>
     */
    private $equalFinalPriceCache = [];

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
        ?LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider = null
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
        $this->equalFinalPriceCache = [];
    }

    /**
     * Check whether Configurable Product have more than one children products
     *
     * @param SaleableInterface $product
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isChildProductsOfEqualPrices(SaleableInterface $product): bool
    {
        $storeId = (int) ($product->getStoreId() ?: 0);
        $cacheKey = (int) $product->getId() . ':' . $storeId;
        if (isset($this->equalFinalPriceCache[$cacheKey])) {
            return $this->equalFinalPriceCache[$cacheKey];
        }

        $memoKey = '_children_final_prices_equal_store_' . $storeId;
        $memoized = $product->getData($memoKey);
        if ($memoized !== null) {
            return (bool) $memoized;
        }

        // Listing fast-path: if index fields are present, rely on them and avoid any child loading
        $minIndexed = $product->getData('minimal_price');
        $maxIndexed = $product->getData('max_price');
        if (is_numeric($minIndexed) && is_numeric($maxIndexed)) {
            $result = ((float)$minIndexed === (float)$maxIndexed);
            $this->equalFinalPriceCache[$cacheKey] = $result;
            $product->setData($memoKey, $result);
            return $result;
        }

        $children = $product->getTypeInstance()->getUsedProducts($product);
        $firstFinal = null;
        $saleableChildrenCount = 0;
        $allEqual = true;
        foreach ($children as $child) {
            if (!$child->isSalable()) {
                continue;
            }
            $saleableChildrenCount++;
            $value = $child->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
            if ($firstFinal === null) {
                $firstFinal = $value;
                continue;
            }
            if ($value != $firstFinal) {
                $allEqual = false;
                break;
            }
        }

        if ($saleableChildrenCount < 1 || $firstFinal === null || !$allEqual) {
            $product->setData($memoKey, false);
            $this->equalFinalPriceCache[$cacheKey] = false;
            return false;
        }

        // Guard against parent-level extra discounts (compute only when children are equal)
        $result = !($product->getFinalPrice() < $firstFinal);
        $this->equalFinalPriceCache[$cacheKey] = $result;
        $product->setData($memoKey, $result);
        return $result;
    }
}
