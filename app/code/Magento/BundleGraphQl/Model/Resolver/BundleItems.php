<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Bundle\Model\Product\Type;
use Magento\BundleGraphQl\Model\Resolver\Options\Collection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * @inheritdoc
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
    private $metadataPool;

    /**
     * @param Collection $bundleOptionCollection
     * @param ValueFactory $valueFactory
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Collection $bundleOptionCollection,
        ValueFactory $valueFactory,
        MetadataPool $metadataPool
    ) {
        $this->bundleOptionCollection = $bundleOptionCollection;
        $this->valueFactory = $valueFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Fetch and format bundle option items.
     *
     * {@inheritDoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        if ($value['type_id'] !== Type::TYPE_CODE
            || !isset($value[$linkField])
            || !isset($value[ProductInterface::SKU])
        ) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
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
