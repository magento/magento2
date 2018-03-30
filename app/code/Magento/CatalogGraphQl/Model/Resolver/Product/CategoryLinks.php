<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Fixed the id related data in the product data
 *
 * {@inheritdoc}
 */
class CategoryLinks implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @param ValueFactory $valueFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     */
    public function __construct(
        ValueFactory $valueFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->valueFactory = $valueFactory;
        $this->productResource = $productResource;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ): ?Value {
        if (!isset($value['model'])) {
            return null;
        }

        /** @var Product $product */
        $product = $value['model'];

        $categories = [];
        $categoryLinks = $this->productResource->getCategoryIds($product);
        foreach ($categoryLinks as $position => $catLink) {
            $categories[] =
                ['position' => $position, 'category_id' => $catLink];
        }

        $result = function () use ($categories) {
            return $categories;
        };

        return $this->valueFactory->create($result);
    }
}
