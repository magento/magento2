<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Wishlist;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Fetches the selected variant SKU of configurable product
 */
class SelectedVariantSku implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!$value['itemModel'] instanceof ItemInterface) {
            throw new LocalizedException(__('"itemModel" should be a "%instance" instance', [
                'instance' => ItemInterface::class
            ]));
        }

        $item = $value['itemModel'];
        $product = $item->getProduct();
        $option = $product->getCustomOption('simple_product');

        return $option && $option->getProduct() ? $option->getProduct()->getSku() : null;
    }
}
