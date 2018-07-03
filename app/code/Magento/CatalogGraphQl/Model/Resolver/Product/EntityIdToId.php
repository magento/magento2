<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

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

        $productId = $product->getData(
            $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
        );

        $result = function () use ($productId) {
            return $productId;
        };

        return $this->valueFactory->create($result);
    }
}
