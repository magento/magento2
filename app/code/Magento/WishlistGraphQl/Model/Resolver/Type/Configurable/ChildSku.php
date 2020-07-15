<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver\Type\Configurable;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\Item;

/**
 * Fetches the simple child sku of configurable product
 */
class ChildSku implements ResolverInterface
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
        if (!$value['wishlistItemModel'] instanceof Item) {
            throw new LocalizedException(__('"wishlistItemModel" should be a "%instance" instance', [
                'instance' => Item::class
            ]));
        }

        /** @var Item $wishlistItem */
        $wishlistItem = $value['wishlistItemModel'];
        $optionProduct = $wishlistItem->getProduct()->getCustomOption('simple_product')->getProduct();

        return $optionProduct->getSku();
    }
}
