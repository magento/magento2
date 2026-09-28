<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver;

use Magento\Bundle\Model\Product\Type;
use Magento\BundleGraphQl\Model\Resolver\Options\Collection;
use Magento\BundleGraphQl\Model\Resolver\Options\CollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class BundleItems implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $bundleOptionCollectionFactory;

    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * @var MetadataPool
     */
    private MetadataPool $metadataPool;

    /**
     * @param Collection $bundleOptionCollection Deprecated. Use $bundleOptionCollectionFactory
     * @param ValueFactory $valueFactory
     * @param MetadataPool $metadataPool
     * @param CollectionFactory|null $bundleOptionCollectionFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Collection $bundleOptionCollection,
        ValueFactory $valueFactory,
        MetadataPool $metadataPool,
        ?CollectionFactory $bundleOptionCollectionFactory = null
    ) {
        $this->bundleOptionCollectionFactory = $bundleOptionCollectionFactory
            ?: ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->valueFactory = $valueFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
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
        $bundleOptionCollection = $this->bundleOptionCollectionFactory->create();
        $bundleOptionCollection->addParentFilterData(
            (int)$value[$linkField],
            (int)$value['entity_id'],
            $value[ProductInterface::SKU]
        );
        $result = function () use ($value, $linkField, $bundleOptionCollection) {
            return $bundleOptionCollection->getOptionsByParentId((int)$value[$linkField]);
        };
        return $this->valueFactory->create($result);
    }
}
