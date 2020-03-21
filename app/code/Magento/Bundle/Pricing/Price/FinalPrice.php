<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Final price model
 */
class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice implements FinalPriceInterface
{
    /**
     * @var AmountInterface
     */
    protected $maximalPrice;

    /**
     * @var AmountInterface
     */
    protected $minimalPrice;

    /**
     * @var AmountInterface
     */
    protected $priceWithoutOption;

    /**
     * @var BundleOptionPrice
     */
    protected $bundleOptionPrice;

    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $productOptionRepository;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductCustomOptionRepositoryInterface $productOptionRepository
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        ProductCustomOptionRepositoryInterface $productOptionRepository
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->productOptionRepository = $productOptionRepository;
    }

    /**
     * Returns price value
     *
     * @return float
     */
    public function getValue()
    {
        return parent::getValue() + $this->getBundleOptionPrice()->getValue();
    }

    /**
     * Returns max price
     *
     * @return AmountInterface
     */
    public function getMaximalPrice()
    {
        if (!$this->maximalPrice) {
            $price = $this->getBasePrice()->getValue();
            if ($this->product->getPriceType() == Price::PRICE_TYPE_FIXED) {
                /** @var CustomOptionPrice $customOptionPrice */
                $customOptionPrice = $this->priceInfo->getPrice(CustomOptionPrice::PRICE_CODE);
                $price += $customOptionPrice->getCustomOptionRange(false);
            }
            $this->maximalPrice = $this->calculator->getMaxAmount($price, $this->product);
        }

        return $this->maximalPrice;
    }

    /**
     * Returns min price
     *
     * @return AmountInterface
     */
    public function getMinimalPrice()
    {
        return $this->getAmount();
    }

    /**
     * Returns price amount
     *
     * @return AmountInterface
     */
    public function getAmount()
    {
        if (!$this->minimalPrice) {
            $price = parent::getValue();
            if ($this->product->getPriceType() == Price::PRICE_TYPE_FIXED) {
                $this->loadProductCustomOptions();
                /** @var CustomOptionPrice $customOptionPrice */
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
            foreach ($this->productOptionRepository->getProductOptions($this->product) as $option) {
                $option->setProduct($this->product);
                $options[] = $option;
            }
            $this->product->setOptions($options);
        }
    }

    /**
     * Get bundle product price without any option
     *
     * @return AmountInterface
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
     * @return BundleOptionPrice
     */
    protected function getBundleOptionPrice()
    {
        if (!$this->bundleOptionPrice) {
            $this->bundleOptionPrice = $this->priceInfo->getPrice(BundleOptionPrice::PRICE_CODE);
        }
        return $this->bundleOptionPrice;
    }
}
