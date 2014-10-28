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

namespace Magento\Framework\Pricing\Price;

use Magento\Framework\Pricing\Object\SaleableInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceInfoInterface;

/**
 * Class AbstractPrice
 * Should be the base for creating any Price type class
 */
abstract class AbstractPrice implements PriceInterface
{
    /**
     * Default price type
     */
    const PRICE_CODE = 'abstract_price';

    /**
     * @var AmountInterface
     */
    protected $amount;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator
     */
    protected $calculator;

    /**
     * @var SaleableInterface
     */
    protected $product;

    /**
     * @var string
     */
    protected $priceType;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var PriceInfoInterface
     */
    protected $priceInfo;

    /**
     * @var bool|float
     */
    protected $value;

    /**
     * @param SaleableInterface $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     */
    public function __construct(
        SaleableInterface $saleableItem,
        $quantity,
        CalculatorInterface $calculator
    ) {
        $this->product = $saleableItem;
        $this->quantity = $quantity;
        $this->calculator = $calculator;
        $this->priceInfo = $saleableItem->getPriceInfo();
    }

    /**
     * Get price value
     *
     * @return float|bool
     */
    abstract public function getValue();

    /**
     * Get Price Amount object
     *
     * @return AmountInterface
     */
    public function getAmount()
    {
        if (null === $this->amount) {
            $this->amount = $this->calculator->getAmount($this->getValue(), $this->product);
        }
        return $this->amount;
    }

    /**
     * @param float $amount
     * @param null|bool|string $exclude
     * @param null|array $context
     * @return AmountInterface|bool|float
     */
    public function getCustomAmount($amount = null, $exclude = null, $context = [])
    {
        $amount = (null === $amount) ? $this->getValue() : $amount;
        return $this->calculator->getAmount($amount, $this->product, $exclude, $context);
    }

    /**
     * Get price type code
     *
     * @return string
     */
    public function getPriceCode()
    {
        return static::PRICE_CODE;
    }
}
