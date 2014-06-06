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

use Magento\Catalog\Pricing\Price\RegularPrice;

/**
 * Special price model
 */
class SpecialPrice extends \Magento\Catalog\Pricing\Price\SpecialPrice implements DiscountProviderInterface
{
    /**
     * @var float|false
     */
    protected $percent;

    /**
     * Returns discount percent
     *
     * @return bool|float
     */
    public function getDiscountPercent()
    {
        if ($this->percent === null) {
            $this->percent = parent::getValue();
        }
        return $this->percent;
    }

    /**
     * Returns price value
     *
     * @return bool|float
     */
    public function getValue()
    {
        if ($this->value !== null) {
            return $this->value;
        }

        $specialPrice = $this->getDiscountPercent();
        if ($specialPrice) {
            $regularPrice = $this->getRegularPrice();
            $this->value = $regularPrice * ($specialPrice / 100);
        } else {
            $this->value = false;
        }
        return $this->value;
    }

    /**
     * Returns regular price
     *
     * @return bool|float
     */
    protected function getRegularPrice()
    {
        return $this->priceInfo->getPrice(RegularPrice::PRICE_CODE)->getValue();
    }
}
