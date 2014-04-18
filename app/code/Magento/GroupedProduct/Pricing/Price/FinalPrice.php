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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GroupedProduct\Pricing\Price;

use Magento\Pricing\Adjustment\Calculator;
use Magento\Pricing\Amount\AmountInterface;
use Magento\Pricing\Object\SaleableInterface;
use Magento\Catalog\Pricing\Price\FinalPriceInterface;
use Magento\Pricing\Price\PriceInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Catalog\Model\ProductFactory;

/**
 * Final price model
 */
class FinalPrice implements FinalPriceInterface, PriceInterface
{
    /**
     * @var string
     */
    protected $priceType = self::PRICE_TYPE_FINAL;

    /**
     * @var \Magento\Pricing\Object\SaleableInterface
     */
    protected $salableItem;

    /**
     * @var \Magento\Pricing\Adjustment\Calculator
     */
    protected $calculator;

    /**
     * @var SaleableInterface
     */
    protected $minProduct;

    /**
     * @var AmountInterface
     */
    protected $amount;

    /**
     * @param SaleableInterface $salableItem
     * @param Calculator $calculator
     */
    public function __construct(
        SaleableInterface $salableItem,
        Calculator $calculator
    ) {
        $this->salableItem = $salableItem;
        $this->calculator = $calculator;
    }

    /**
     * Return minimal product price
     *
     * @return float
     */
    public function getValue()
    {
        return $this->getMinProduct()->getPriceInfo()
            ->getPrice(\Magento\Catalog\Pricing\Price\FinalPriceInterface::PRICE_TYPE_FINAL)->getValue();
    }

    /**
     * Get price type code
     *
     * @return string
     */
    public function getPriceType()
    {
        return $this->priceType;
    }

    /**
     * Get Price Amount object
     *
     * @return AmountInterface
     */
    public function getAmount()
    {
        if (!$this->amount) {
            $this->amount = $this->calculator->getAmount($this->getValue(), $this->salableItem);
        }
        return $this->amount;
    }

    /**
     * Returns product with minimal price
     *
     * @return SaleableInterface
     */
    public function getMinProduct()
    {
        if (null === $this->minProduct) {
            $products = $this->salableItem->getTypeInstance()->getAssociatedProducts($this->salableItem);
            $minPrice = null;
            foreach ($products as $item) {
                $product = clone $item;
                $product->setQty(\Magento\Pricing\PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT);
                $price = $product->getPriceInfo()
                    ->getPrice(FinalPriceInterface::PRICE_TYPE_FINAL)
                    ->getValue();
                if (($price !== false) && ($price <= (is_null($minPrice) ? $price : $minPrice))) {
                    $this->minProduct = $product;
                    $minPrice = $price;
                }
            }
        }
        return $this->minProduct;
    }

    /**
     * @param float $amount
     * @param null|string $exclude
     * @return AmountInterface
     */
    public function getCustomAmount($amount = null, $exclude = null)
    {
        if ($amount === null) {
            $amount = $this->getValue();
        }
        return $this->calculator->getAmount($amount, $this->salableItem, $exclude);
    }
}
