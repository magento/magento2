<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price as CatalogPrice;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Model\Product\Price;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Object\SaleableInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Bundle option price
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
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param SaleableInterface $bundleProduct
     * @param ManagerInterface $eventManager
     * @param DiscountCalculator $discountCalculator
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        SaleableInterface $bundleProduct,
        ManagerInterface $eventManager,
        DiscountCalculator $discountCalculator
    ) {
        parent::__construct($saleableItem, $quantity, $calculator);
        $this->bundleProduct = $bundleProduct;
        $this->eventManager = $eventManager;
        $this->discountCalculator = $discountCalculator;
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

        if ($this->bundleProduct->getPriceType() == Price::PRICE_TYPE_DYNAMIC) {
            $value = $this->priceInfo
                ->getPrice(FinalPrice::PRICE_CODE)
                ->getValue();
        } else {
            if ($this->product->getSelectionPriceType()) {
                // calculate price for selection type percent
                $price = $this->bundleProduct->getPriceInfo()
                    ->getPrice(CatalogPrice\RegularPrice::PRICE_CODE)
                    ->getValue();
                $product = clone $this->bundleProduct;
                $product->setFinalPrice($price);
                $this->eventManager->dispatch(
                    'catalog_product_get_final_price',
                    array('product' => $product, 'qty' => $this->bundleProduct->getQty())
                );
                $value = $product->getData('final_price') * ($this->product->getSelectionPriceValue() / 100);
            } else {
                // calculate price for selection type fixed
                $value = $this->product->getSelectionPriceValue() * $this->quantity;
            }
        }
        $this->value = $this->discountCalculator->calculateDiscount($this->bundleProduct, $value);
        return $this->value;
    }
}
