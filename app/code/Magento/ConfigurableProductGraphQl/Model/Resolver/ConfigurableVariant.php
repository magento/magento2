<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection as AttributeCollection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as Type;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;
use Magento\ConfigurableProductGraphQl\Model\Variant\Collection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * @inheritdoc
 */
class ConfigurableVariant implements ResolverInterface
{
    /**
     * @var Collection
     */
    private $variantCollection;

    /**
     * @var OptionCollection
     */
    private $optionCollection;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Collection $variantCollection
     * @param OptionCollection $optionCollection
     * @param ValueFactory $valueFactory
     * @param AttributeCollection $attributeCollection
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Collection $variantCollection,
        OptionCollection $optionCollection,
        ValueFactory $valueFactory,
        AttributeCollection $attributeCollection,
        MetadataPool $metadataPool
    ) {
        $this->variantCollection = $variantCollection;
        $this->optionCollection = $optionCollection;
        $this->valueFactory = $valueFactory;
        $this->attributeCollection = $attributeCollection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Fetch and format configurable variants.
     *
     * {@inheritDoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        if ($value['type_id'] !== Type::TYPE_CODE || !isset($value[$linkField])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        $this->variantCollection->addParentId((int)$value[$linkField]);
        $fields = $this->getProductFields($info);
        $matchedFields = $this->attributeCollection->getRequestAttributes($fields);
        $this->variantCollection->addEavAttributes($matchedFields);
        $this->optionCollection->addProductId((int)$value[$linkField]);

        $result = function () use ($value, $linkField) {
            $children = $this->variantCollection->getChildProductsByParentId((int)$value[$linkField]);
            $options = $this->optionCollection->getAttributesByProductId((int)$value[$linkField]);
            $variants = [];
            /** @var Product $child */
            foreach ($children as $key => $child) {
                $variants[$key] = ['sku' => $child['sku'], 'product' => $child, 'options' => $options];
            }

            return $variants;
        };

        return $this->valueFactory->create($result);
    }

    /**
     * Return field names for all requested product fields.
     *
     * @param ResolveInfo $info
     * @return string[]
     */
    private function getProductFields(ResolveInfo $info)
    {
        $fieldNames = [];
        foreach ($info->fieldNodes as $node) {
            if ($node->name->value !== 'product') {
                continue;
            }

            foreach ($node->selectionSet->selections as $selectionNode) {
                $fieldNames[] = $selectionNode->name->value;
            }
        }

        return $fieldNames;
    }
}
