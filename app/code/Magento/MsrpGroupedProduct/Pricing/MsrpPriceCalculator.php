<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MsrpGroupedProduct\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Msrp\Pricing\MsrpPriceCalculatorInterface;

/**
 * {@inheritdoc}. Provide information for a Grouped product.
 */
class MsrpPriceCalculator implements MsrpPriceCalculatorInterface
{
    /**
     * @inheritdoc
     */
    public function getMsrpPriceValue(ProductInterface $product): float
    {
        /** @var Product $product */
        if ($product->getTypeId() !== Grouped::TYPE_CODE) {
            return 0;
        }

        /** @var Grouped $groupedProduct */
        $groupedProduct = $product->getTypeInstance();

        return $groupedProduct->getChildrenMsrp($product);
    }
}
