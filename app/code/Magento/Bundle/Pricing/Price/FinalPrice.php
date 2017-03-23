<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Bundle\Model\Product\Price;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;

/**
 * Final price model
 */
class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice implements FinalPriceInterface
{
    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $maximalPrice;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $minimalPrice;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $priceWithoutOption;

    /**
     * @var \Magento\Bundle\Pricing\Price\BundleOptionPrice
     */
    protected $bundleOptionPrice;

    /**
     * @var \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface
     */
    private $productOptionRepository;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
    }

    /**
     * Returns price value
     *
     * @return float
     */
    public function getValue()
    {
        return parent::getValue() +
            $this->getBundleOptionPrice()->getValue();
    }

    /**
     * Returns max price
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaximalPrice()
    {
        if (!$this->maximalPrice) {
            $price = $this->getBasePrice()->getValue();
            if ($this->product->getPriceType() == Price::PRICE_TYPE_FIXED) {
                /** @var \Magento\Catalog\Pricing\Price\CustomOptionPrice $customOptionPrice */
                $customOptionPrice = $this->priceInfo->getPrice(CustomOptionPrice::PRICE_CODE);
                $price += $customOptionPrice->getCustomOptionRange(false);
            }
            $this->maximalPrice = $this->calculator->getMaxAmount($price, $this->product);
        }
        return $this->maximalPrice;
    }

    /**
     * Return ProductCustomOptionRepository
     *
     * @return ProductCustomOptionRepositoryInterface
     * @deprecated
     */
    private function getProductOptionRepository()
    {
        if (!$this->productOptionRepository) {
            $this->productOptionRepository = ObjectManager::getInstance()->get(
                ProductCustomOptionRepositoryInterface::class
            );
        }
        return $this->productOptionRepository;
    }

    /**
     * Returns min price
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinimalPrice()
    {
        return $this->getAmount();
    }

    /**
     * Returns price amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount()
    {
        if (!$this->minimalPrice) {
            $price = parent::getValue();
            if ($this->product->getPriceType() == Price::PRICE_TYPE_FIXED) {
                $this->loadProductCustomOptions();
                /** @var \Magento\Catalog\Pricing\Price\CustomOptionPrice $customOptionPrice */
                $customOptionPrice = $this->priceInfo->getPrice(CustomOptionPrice::PRICE_CODE);
                $price += $customOptionPrice->getCustomOptionRange(true);
            }
            $this->minimalPrice = $this->calculator->getAmount($price, $this->product);
        }
        return $this->minimalPrice;
    }

    /**
     * Load product custom options
     *
     * @return void
     */
    private function loadProductCustomOptions()
    {
        if (!$this->product->getOptions()) {
            $options = [];
            foreach ($this->getProductOptionRepository()->getProductOptions($this->product) as $option) {
                $option->setProduct($this->product);
                $options[] = $option;
            }
            $this->product->setOptions($options);
        }
    }

    /**
     * get bundle product price without any option
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getPriceWithoutOption()
    {
        if (!$this->priceWithoutOption) {
            $this->priceWithoutOption = $this->calculator->getAmountWithoutOption(parent::getValue(), $this->product);
        }
        return $this->priceWithoutOption;
    }

    /**
     * Returns option price
     *
     * @return \Magento\Bundle\Pricing\Price\BundleOptionPrice
     */
    protected function getBundleOptionPrice()
    {
        if (!$this->bundleOptionPrice) {
            $this->bundleOptionPrice = $this->priceInfo->getPrice(BundleOptionPrice::PRICE_CODE);
        }
        return $this->bundleOptionPrice;
    }
}
