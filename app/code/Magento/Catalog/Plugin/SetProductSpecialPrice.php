<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type\Price;

/**
 * Class SetProductSpecialPrice will add special_price to product based on Catalog Price Rule
 */
class SetProductSpecialPrice
{
    const PRODUCT_QTY = 1;

    /**
     * @var Price
     */
    private $price;

    /**
     * @param Price $price
     */
    public function __construct(
        Price $price
    ) {
        $this->price = $price;
    }

    /**
     * Set special_price based on Catalog Price Rule
     *
     * @param ProductRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGet(ProductRepositoryInterface $subject, $result)
    {
        if ($result instanceof ProductInterface) {
            $finalPrice = $this->price->getFinalPrice(self::PRODUCT_QTY, $result);
            $result->setData('special_price', $finalPrice);
        }

        return $result;
    }
}
