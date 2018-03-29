<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\BundleGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\BundleGraphQl\Model\Resolver\Options\Collection;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * {@inheritdoc}
 */
class BundleItems implements ResolverInterface
{
    /**
     * @var Collection
     */
    private $bundleOptionCollection;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var MetadataPool
     */
    private $metdataPool;

    /**
     * @param Collection $bundleOptionCollection
     * @param ValueFactory $valueFactory
     * @param MetadataPool $metdataPool
     */
    public function __construct(
        Collection $bundleOptionCollection,
        ValueFactory $valueFactory,
        MetadataPool $metdataPool
    ) {
        $this->bundleOptionCollection = $bundleOptionCollection;
        $this->valueFactory = $valueFactory;
        $this->metdataPool = $metdataPool;
    }

    /**
     * Fetch and format bundle option items.
     *
     * {@inheritDoc}
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info) : ?Value
    {
        $linkField = $this->metdataPool->getMetadata(ProductInterface::class)->getLinkField();
        if ($value['type_id'] !== Type::TYPE_CODE
            || !isset($value[$linkField])
            || !isset($value[ProductInterface::SKU])
        ) {
            return null;
        }

        $this->bundleOptionCollection->addParentFilterData(
            (int)$value[$linkField],
            (int)$value['entity_id'],
            $value[ProductInterface::SKU]
        );

        $result = function () use ($value, $linkField) {
            return $this->bundleOptionCollection->getOptionsByParentId((int)$value[$linkField]);
        };

        return $this->valueFactory->create($result);
    }
}
