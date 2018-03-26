<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as Type;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\ConfigurableProductGraphQl\Model\Variant\Collection;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;
use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection as AttributeCollection;

/**
 * {@inheritdoc}
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
     * @param Collection $variantCollection
     * @param OptionCollection $optionCollection
     * @param ValueFactory $valueFactory
     * @param AttributeCollection $attributeCollection
     */
    public function __construct(
        Collection $variantCollection,
        OptionCollection $optionCollection,
        ValueFactory $valueFactory,
        AttributeCollection $attributeCollection
    ) {
        $this->variantCollection = $variantCollection;
        $this->optionCollection = $optionCollection;
        $this->valueFactory = $valueFactory;
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * Fetch and format configurable variants.
     *
     * {@inheritDoc}
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info) : ?Value
    {
        if ($value['type_id'] !== Type::TYPE_CODE || !isset($value['id'])) {
            return null;
        }

        $this->variantCollection->addParentId((int)$value['id']);
        $fields = $this->getProductFields($info);
        $matchedFields = $this->attributeCollection->getRequestAttributes($fields);
        $this->variantCollection->addEavAttributes($matchedFields);
        $this->optionCollection->addProductId((int)$value['id']);

        $result = function () use ($value) {
            $children = $this->variantCollection->getChildProductsByParentId((int)$value['id']) ?: [];
            $options = $this->optionCollection->getAttributesByProductId((int)$value['id']) ?: [];
            $variants = [];
            foreach ($children as $key => $child) {
                $variants[$key] = ['product' => $child, 'options' => $options];
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
