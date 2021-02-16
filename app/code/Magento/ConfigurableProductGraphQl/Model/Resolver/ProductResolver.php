<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Fetches the Product data according to the GraphQL schema
 */
class ProductResolver implements ResolverInterface
{
    /**
     * @var ItemResolverInterface
     */
    private $configurableItemResolver;

    /**
     * @param ItemResolverInterface $configurableItemResolver
     */
    public function __construct(ItemResolverInterface $configurableItemResolver)
    {
        $this->configurableItemResolver = $configurableItemResolver;
    }

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
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $product = $this->configurableItemResolver->getFinalProduct($value['model']);
        $productData = $product->toArray();
        $productData['model'] = $product;
        return $productData;
    }
}
