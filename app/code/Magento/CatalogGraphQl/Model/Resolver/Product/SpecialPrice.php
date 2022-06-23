<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Pricing\Price\SpecialPrice as PricingSpecialPrice;

/**
 * Resolver for Special Price
 */
class SpecialPrice implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var ProductInterface $product */
        $product = $value['model'];

        /** @var PricingSpecialPrice $specialPrice */
        $specialPrice = $product->getPriceInfo()->getPrice(PricingSpecialPrice::PRICE_CODE);

        if ((!$product->hasData('can_show_price')
                || ($product->hasData('can_show_price') && $product->getData('can_show_price') === true)
            )
                && $specialPrice->getValue()) {
            return $specialPrice->getValue();
        }

        return null;
    }
}
