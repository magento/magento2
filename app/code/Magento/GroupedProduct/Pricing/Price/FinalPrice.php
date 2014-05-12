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

namespace Magento\GroupedProduct\Pricing\Price;

use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Catalog\Model\Product;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Final price model
 */
class FinalPrice extends AbstractPrice
{
    /**
     * Price type final
     */
    const PRICE_CODE = 'final_price';

    /**
     * @var Product
     */
    protected $minProduct;

    /**
     * Return minimal product price
     *
     * @return float
     */
    public function getValue()
    {
        return $this->getMinProduct()->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
    }

    /**
     * Returns product with minimal price
     *
     * @return Product
     */
    public function getMinProduct()
    {
        if (null === $this->minProduct) {
            $products = $this->product->getTypeInstance()->getAssociatedProducts($this->product);
            $minPrice = null;
            foreach ($products as $item) {
                $product = clone $item;
                $product->setQty(\Magento\Framework\Pricing\PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT);
                $price = $product->getPriceInfo()
                    ->getPrice(FinalPrice::PRICE_CODE)
                    ->getValue();
                if (($price !== false) && ($price <= (is_null($minPrice) ? $price : $minPrice))) {
                    $this->minProduct = $product;
                    $minPrice = $price;
                }
            }
        }
        return $this->minProduct;
    }
}
