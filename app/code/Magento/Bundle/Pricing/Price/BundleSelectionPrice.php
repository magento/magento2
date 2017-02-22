<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price as CatalogPrice;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Bundle option price
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleSelectionPrice extends AbstractPrice
{
    /**
     * Price model code
     */
    const PRICE_CODE = 'bundle_selection';

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $bundleProduct;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var DiscountCalculator
     */
    protected $discountCalculator;

    /**
     * @var bool
     */
    protected $useRegularPrice;

    /**
     * @var Product
     */
    protected $selection;

    /**
     * Code of parent adjustment to be skipped from calculation
     *
     * @var string
     */
    protected $excludeAdjustment = null;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param SaleableInterface $bundleProduct
     * @param ManagerInterface $eventManager
     * @param DiscountCalculator $discountCalculator
     * @param bool $useRegularPrice
     * @param array $excludeAdjustment
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        SaleableInterface $bundleProduct,
        ManagerInterface $eventManager,
        DiscountCalculator $discountCalculator,
        $useRegularPrice = false,
        $excludeAdjustment = null
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->bundleProduct = $bundleProduct;
        $this->eventManager = $eventManager;
        $this->discountCalculator = $discountCalculator;
        $this->useRegularPrice = $useRegularPrice;
        $this->selection = $saleableItem;
        $this->excludeAdjustment = $excludeAdjustment;
    }

    /**
     * Get the price value for one of selection product
     *
     * @return bool|float
     */
    public function getValue()
    {
        if (null !== $this->value) {
            return $this->value;
        }

        $priceCode = $this->useRegularPrice ? BundleRegularPrice::PRICE_CODE : FinalPrice::PRICE_CODE;
        if ($this->bundleProduct->getPriceType() == Price::PRICE_TYPE_DYNAMIC) {
            // just return whatever the product's value is
            $value = $this->priceInfo
                ->getPrice($priceCode)
                ->getValue();
        } else {
            // don't multiply by quantity.  Instead just keep as quantity = 1
            $selectionPriceValue = $this->selection->getSelectionPriceValue();
            if ($this->product->getSelectionPriceType()) {
                // calculate price for selection type percent
                $price = $this->bundleProduct->getPriceInfo()
                    ->getPrice(CatalogPrice\RegularPrice::PRICE_CODE)
                    ->getValue();
                $product = clone $this->bundleProduct;
                $product->setFinalPrice($price);
                $this->eventManager->dispatch(
                    'catalog_product_get_final_price',
                    ['product' => $product, 'qty' => $this->bundleProduct->getQty()]
                );
                $value = $product->getData('final_price') * ($selectionPriceValue / 100);
            } else {
                // calculate price for selection type fixed
                $value = $this->priceCurrency->convert($selectionPriceValue) * $this->quantity;
            }
        }
        if (!$this->useRegularPrice) {
            $value = $this->discountCalculator->calculateDiscount($this->bundleProduct, $value);
        }
        $this->value = $this->priceCurrency->round($value);

        return $this->value;
    }

    /**
     * Get Price Amount object
     *
     * @return AmountInterface
     */
    public function getAmount()
    {
        if (null === $this->amount) {
            $exclude = null;
            if ($this->getProduct()->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $exclude = $this->excludeAdjustment;
            }
            $this->amount = $this->calculator->getAmount($this->getValue(), $this->getProduct(), $exclude);
        }
        return $this->amount;
    }

    /**
     * @return SaleableInterface
     */
    public function getProduct()
    {
        if ($this->bundleProduct->getPriceType() == Price::PRICE_TYPE_DYNAMIC) {
            return parent::getProduct();
        } else {
            return $this->bundleProduct;
        }
    }
}
