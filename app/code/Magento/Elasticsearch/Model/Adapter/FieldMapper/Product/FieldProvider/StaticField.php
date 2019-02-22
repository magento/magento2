<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider;

use Magento\Eav\Model\Config;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface
    as IndexTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface
    as FieldTypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ResolverInterface
    as FieldIndexResolver;

/**
 * Provide static fields for mapping of product.
 */
class StaticField implements FieldProviderInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var FieldTypeConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * @var IndexTypeConverterInterface
     */
    private $indexTypeConverter;

    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var FieldTypeResolver
     */
    private $fieldTypeResolver;

    /**
     * @var FieldIndexResolver
     */
    private $fieldIndexResolver;

    /**
     * @param Config $eavConfig
     * @param FieldTypeConverterInterface $fieldTypeConverter
     * @param IndexTypeConverterInterface $indexTypeConverter
     * @param FieldTypeResolver $fieldTypeResolver
     * @param FieldIndexResolver $fieldIndexResolver
     * @param AttributeProvider $attributeAdapterProvider
     */
    public function __construct(
        Config $eavConfig,
        FieldTypeConverterInterface $fieldTypeConverter,
        IndexTypeConverterInterface $indexTypeConverter,
        FieldTypeResolver $fieldTypeResolver,
        FieldIndexResolver $fieldIndexResolver,
        AttributeProvider $attributeAdapterProvider
    ) {
        $this->eavConfig = $eavConfig;
        $this->fieldTypeConverter = $fieldTypeConverter;
        $this->indexTypeConverter = $indexTypeConverter;
        $this->fieldTypeResolver = $fieldTypeResolver;
        $this->fieldIndexResolver = $fieldIndexResolver;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
    }

    /**
     * Get static fields.
     *
     * @param array $context
     * @return array
     */
    public function getFields(array $context = []): array
    {
        $attributes = $this->eavConfig->getEntityAttributes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $allAttributes = [];

        foreach ($attributes as $attribute) {
            $attributeAdapter = $this->attributeAdapterProvider->getByAttributeCode($attribute->getAttributeCode());
            $code = $attributeAdapter->getAttributeCode();

            $allAttributes[$code] = [
                'type' => $this->fieldTypeResolver->getFieldType($attributeAdapter),
            ];

            $index = $this->fieldIndexResolver->getFieldIndex($attributeAdapter);
            if (null !== $index) {
                $allAttributes[$code]['index'] = $index;
            }

            if ($attributeAdapter->isComplexType()) {
                $allAttributes[$code . '_value'] = [
                    'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING)
                ];
            }
        }

        $allAttributes['store_id'] = [
            'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING),
            'index' => $this->indexTypeConverter->convert(IndexTypeConverterInterface::INTERNAL_NO_INDEX_VALUE),
        ];

        return $allAttributes;
    }
}
