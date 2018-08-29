<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Format the product links information to conform to GraphQL schema representation
 *
 * {@inheritdoc}
 */
class ProductLinks implements ResolverInterface
{
    /**
     * @var string[]
     */
    private $linkTypes = ['related', 'upsell', 'crosssell'];

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ValueFactory $valueFactory
     */
    public function __construct(ValueFactory $valueFactory)
    {
        $this->valueFactory = $valueFactory;
    }

    /**
     * Format product links data to conform to GraphQL schema
     *
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        if (!isset($value['model'])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        /** @var Product $product */
        $product = $value['model'];

        $links = null;
        if ($product->getProductLinks()) {
            $links = [];
            /** @var Link $productLink */
            foreach ($product->getProductLinks() as $productLink) {
                if (in_array($productLink->getLinkType(), $this->linkTypes)) {
                    $links[] = $productLink->getData();
                }
            }
        }

        $result = function () use ($links) {
            return $links;
        };

        return $this->valueFactory->create($result);
    }
}
