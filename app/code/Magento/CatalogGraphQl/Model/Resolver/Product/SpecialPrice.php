<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Pricing\Price\SpecialPrice as PricingSpecialPrice;

class SpecialPrice implements ResolverInterface
{
    /**
     * Fetches the data from persistence models and format it according to the GraphQL schema.
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     * @throws \Exception
     */public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $value['model'];
        /** @var PricingSpecialPrice $specialPrice */
        $specialPrice = $product->getPriceInfo()->getPrice(PricingSpecialPrice::PRICE_CODE);

        if ($specialPrice->getValue()) {
            return $specialPrice->getValue();
        }

        return null;
    }
}
