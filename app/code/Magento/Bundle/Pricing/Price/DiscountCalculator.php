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

/**
 * Class DiscountCalculator
 */
class DiscountCalculator
{
    /**
     * Apply percentage discount
     *
     * @param Product $product
     * @param float|null $value
     * @return float|null
     */
    public function calculateDiscount(Product $product, $value = null)
    {
        if (is_null($value)) {
            $value = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
        }

        $discount = null;
        foreach ($product->getPriceInfo()->getPrices() as $price) {
            if ($price instanceof DiscountProviderInterface && $price->getDiscountPercent()) {
                $discount = min($price->getDiscountPercent(), $discount ?: $price->getDiscountPercent());
            }
        }
        return (null !== $discount) ?  $discount/100 * $value : $value;
    }
}
