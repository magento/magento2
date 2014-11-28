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

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\BasePrice;

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
            $this->maximalPrice = $this->calculator->getMaxAmount($this->getBasePrice()->getValue(), $this->product);
        }
        return $this->maximalPrice;
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
            $this->minimalPrice = $this->calculator->getAmount(parent::getValue(), $this->product);
        }
        return $this->minimalPrice;
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
