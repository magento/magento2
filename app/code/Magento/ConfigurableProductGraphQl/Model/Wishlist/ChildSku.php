<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Wishlist;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

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
        ?array $value = null,
        ?array $args = null
    ) {
        if (!$value['model'] instanceof Product) {
            throw new LocalizedException(__('"itemModel" should be a "%instance" instance', [
                'instance' => Product::class
            ]));
        }

        /** @var Product $product */
        $product = $value['model'];

        /** to handle no child sku selected at add to wishlist time */
        $optionsArray = json_decode($product->getCustomOption('info_buyRequest')->getValue(), true);
        $superAttribute = $optionsArray['super_attribute'];
        $totalSelected = array_filter($superAttribute);

        if (count($totalSelected) > 0) {
            $optionProduct = $product->getCustomOption('simple_product')->getProduct();
            return $optionProduct->getSku();
        } else {
            return "";
        }
    }
}
