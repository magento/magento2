<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Class RegularPrice
 * @since 2.0.0
 */
class ConfigurableRegularPrice extends AbstractPrice implements ConfigurableRegularPriceInterface
{
    /**
     * Price type
     */
    const PRICE_CODE = 'regular_price';

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     * @since 2.0.0
     */
    protected $maxRegularAmount;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     * @since 2.0.0
     */
    protected $minRegularAmount;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $values = [];

    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface
     * @since 2.0.0
     */
    protected $priceResolver;

    /**
     * @var ConfigurableOptionsProviderInterface
     * @since 2.2.0
     */
    private $configurableOptionsProvider;

    /**
     * @var LowestPriceOptionsProviderInterface
     * @since 2.2.0
     */
    private $lowestPriceOptionsProvider;

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface $saleableItem
     * @param float $quantity
     * @param \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param PriceResolverInterface $priceResolver
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Pricing\SaleableInterface $saleableItem,
        $quantity,
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        PriceResolverInterface $priceResolver,
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider = null
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->priceResolver = $priceResolver;
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider ?:
            ObjectManager::getInstance()->get(LowestPriceOptionsProviderInterface::class);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getValue()
    {
        if (!isset($this->values[$this->product->getId()])) {
            $this->values[$this->product->getId()] = $this->priceResolver->resolvePrice($this->product);
        }

        return $this->values[$this->product->getId()];
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAmount()
    {
        return $this->getMinRegularAmount();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getUsedProducts()
    {
        return $this->getConfigurableOptionsProvider()->getProducts($this->product);
    }

    /**
     * @return \Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getConfigurableOptionsProvider()
    {
        if (null === $this->configurableOptionsProvider) {
            $this->configurableOptionsProvider = ObjectManager::getInstance()
                ->get(ConfigurableOptionsProviderInterface::class);
        }
        return $this->configurableOptionsProvider;
    }
}
