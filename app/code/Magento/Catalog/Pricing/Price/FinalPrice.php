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

namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Final price model
 */
class FinalPrice extends AbstractPrice implements FinalPriceInterface
{
    /**
     * Price type final
     */
    const PRICE_CODE = 'final_price';

    /**
     * @var BasePrice
     */
    protected $basePrice;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator
    ) {
        parent::__construct($saleableItem, $quantity, $calculator);
        $this->basePrice = $this->priceInfo->getPrice(BasePrice::PRICE_CODE);
    }

    /**
     * Get Value
     *
     * @return float|bool
     */
    public function getValue()
    {
        return max(0, $this->basePrice->getValue());
    }

    /**
     * Get Minimal Price Amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinimalPrice()
    {
        $minimalPrice = $this->product->getMinimalPrice();
        if ($minimalPrice === null) {
            $minimalPrice = $this->getValue();
        }
        return $this->calculator->getAmount($minimalPrice, $this->product);
    }

    /**
     * Get Maximal Price Amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaximalPrice()
    {
        return $this->calculator->getAmount($this->getValue(), $this->product);
    }
}
