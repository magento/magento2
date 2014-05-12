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

use Magento\Catalog\Model\Product;
use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;

/**
 * Final price model
 */
class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice
{
    /**
     * Price type final
     */
    const PRICE_CODE = 'final_price';

    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;

    /**
     * @var BasePrice
     */
    protected $basePrice;

    /**
     * @return float
     */
    public function getValue()
    {
        return parent::getValue() + $this->basePrice->calculateBaseValue($this->getBundleOptionPrice()->getValue());
    }

    /**
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaximalPrice()
    {
        return $this->calculator->getMaxAmount($this->basePrice->getValue(), $this->product);
    }

    /**
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinimalPrice()
    {
        return $this->getAmount();
    }

    /**
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount()
    {
        return $this->calculator->getAmount(parent::getValue(), $this->product);
    }

    /**
     * @return \Magento\Bundle\Pricing\Price\BundleOptionPrice
     */
    protected function getBundleOptionPrice()
    {
        return $this->priceInfo->getPrice(BundleOptionPrice::PRICE_CODE, $this->quantity);
    }
}
