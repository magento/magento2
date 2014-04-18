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

use Magento\Pricing\Adjustment\CalculatorInterface;
use Magento\Pricing\Amount\AmountInterface;
use Magento\Pricing\Price\PriceInterface;
use Magento\Pricing\PriceInfoInterface;
use Magento\Pricing\Object\SaleableInterface;

/**
 * Class RegularPrice
 */
class RegularPrice implements PriceInterface
{
    /**
     * Default price type
     */
    const PRICE_TYPE_PRICE_DEFAULT = 'regular_price';

    /**
     * @var string
     */
    protected $priceType = self::PRICE_TYPE_PRICE_DEFAULT;

    /**
     * @var SaleableInterface|\Magento\Catalog\Model\Product
     */
    protected $salableItem;

    /**
     * @var PriceInfoInterface
     */
    protected $priceInfo;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var \Magento\Pricing\Adjustment\Calculator
     */
    protected $calculator;

    /**
     * @var bool|float
     */
    protected $value;

    /**
     * @var AmountInterface
     */
    protected $amount;

    /**
     * @param SaleableInterface $salableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     */
    public function __construct(
        SaleableInterface $salableItem,
        $quantity,
        CalculatorInterface $calculator
    ) {
        $this->salableItem = $salableItem;
        $this->quantity = $quantity;
        $this->calculator = $calculator;
        $this->priceInfo = $salableItem->getPriceInfo();
    }

    /**
     * Get price value
     *
     * @return float|bool
     */
    public function getValue()
    {
        if ($this->value === null) {
            $price = $this->salableItem->getPrice();
            $this->value = $price ? floatval($price) : false;
        }
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
            $this->amount = $this->calculator->getAmount($this->getValue(), $this->salableItem);
        }
        return $this->amount;
    }

    /**
     * @param float $amount
     * @param null|bool|string $exclude
     * @return AmountInterface
     */
    public function getCustomAmount($amount = null, $exclude = null)
    {
        if ($amount === null) {
            $amount = $this->getValue();
        }
        return $this->calculator->getAmount($amount, $this->salableItem, $exclude);
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
}
