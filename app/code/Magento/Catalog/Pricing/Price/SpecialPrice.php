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

namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Object\SaleableInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Special price model
 */
class SpecialPrice extends RegularPrice implements SpecialPriceInterface
{
    /**
     * @var string
     */
    protected $priceType = self::PRICE_TYPE_SPECIAL;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @param SaleableInterface $salableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        SaleableInterface $salableItem,
        $quantity,
        CalculatorInterface $calculator,
        TimezoneInterface $localeDate
    ) {
        parent::__construct($salableItem, $quantity, $calculator);
        $this->localeDate = $localeDate;
    }

    /**
     * @return bool|float
     */
    public function getValue()
    {
        if (null === $this->value) {
            $this->value = false;
            $specialPrice = $this->getSpecialPrice();
            if (!is_null($specialPrice) && $specialPrice !== false && $this->isScopeDateInInterval()) {
                $this->value = (float) $specialPrice;
            }
        }

        return $this->value;
    }

    /**
     * Returns special price
     *
     * @return float
     */
    public function getSpecialPrice()
    {
        return $this->salableItem->getSpecialPrice();
    }

    /**
     * Returns starting date of the special price
     *
     * @return mixed
     */
    public function getSpecialFromDate()
    {
        return $this->salableItem->getSpecialFromDate();
    }

    /**
     * Returns end date of the special price
     *
     * @return mixed
     */
    public function getSpecialToDate()
    {
        return $this->salableItem->getSpecialToDate();
    }

    /**
     * @return bool
     */
    public function isScopeDateInInterval()
    {
        return $this->localeDate->isScopeDateInInterval(
            $this->salableItem->getStore(),
            $this->getSpecialFromDate(),
            $this->getSpecialToDate()
        );
    }
}
