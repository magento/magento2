<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;

/**
 * Fixed the id related data in the product data
 *
 * {@inheritdoc}
 */
class EntityIdToId implements ResolverInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param MetadataPool $metadataPool
     * @param ValueFactory $valueFactory
     */
    public function __construct(MetadataPool $metadataPool, ValueFactory $valueFactory)
    {
        $this->metadataPool = $metadataPool;
        $this->valueFactory = $valueFactory;
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

        $id = $product->getData(
            $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
        );

        $result = function () use ($id) {
            return $id;
        };

        return $this->valueFactory->create($result);
    }
}
