<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MsrpConfigurableProduct\Pricing;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Msrp\Pricing\MsrpPriceCalculatorInterface;

/**
 * {@inheritdoc}. Provide information for a Configurable product.
 */
class MsrpPriceCalculator implements MsrpPriceCalculatorInterface
{
    /**
     * @inheritdoc
     */
    public function getMsrpPriceValue(ProductInterface $product): float
    {
        /** @var Product $product */
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return 0;
        }

        /** @var Configurable $configurableProduct */
        $configurableProduct = $product->getTypeInstance();
        $msrp = 0;
        $prices = [];
        foreach ($configurableProduct->getUsedProducts($product) as $item) {
            if ($item->getMsrp() !== null) {
                $prices[] = $item->getMsrp();
            }
        }
        if ($prices) {
            $msrp = (float)max($prices);
        }

        return  $msrp;
    }
}
