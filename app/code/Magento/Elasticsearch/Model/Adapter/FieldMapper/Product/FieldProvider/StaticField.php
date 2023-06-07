<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface
    as IndexTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ResolverInterface
    as FieldIndexResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface
    as FieldTypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

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
     * @var FieldName\ResolverInterface
     */
    private $fieldNameResolver;

    /**
     * @var array
     */
    private $excludedAttributes;

    /**
     * @param Config $eavConfig
     * @param FieldTypeConverterInterface $fieldTypeConverter
     * @param IndexTypeConverterInterface $indexTypeConverter
     * @param FieldTypeResolver $fieldTypeResolver
     * @param FieldIndexResolver $fieldIndexResolver
     * @param AttributeProvider $attributeAdapterProvider
     * @param FieldName\ResolverInterface $fieldNameResolver
     * @param array $excludedAttributes
     */
    public function __construct(
        Config $eavConfig,
        FieldTypeConverterInterface $fieldTypeConverter,
        IndexTypeConverterInterface $indexTypeConverter,
        FieldTypeResolver $fieldTypeResolver,
        FieldIndexResolver $fieldIndexResolver,
        AttributeProvider $attributeAdapterProvider,
        FieldName\ResolverInterface $fieldNameResolver,
        array $excludedAttributes = []
    ) {
        $this->eavConfig = $eavConfig;
        $this->fieldTypeConverter = $fieldTypeConverter;
        $this->indexTypeConverter = $indexTypeConverter;
        $this->fieldTypeResolver = $fieldTypeResolver;
        $this->fieldIndexResolver = $fieldIndexResolver;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->fieldNameResolver = $fieldNameResolver;
        $this->excludedAttributes = $excludedAttributes;
    }

    /**
     * Get static fields.
     *
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getFields(array $context = []): array
    {
        $attributes = $this->eavConfig->getEntityAttributes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $allAttributes = [];

        foreach ($attributes as $attribute) {
            $allAttributes += $this->getField($attribute);
        }

        $allAttributes['store_id'] = [
            'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING),
            'index' => $this->indexTypeConverter->convert(IndexTypeConverterInterface::INTERNAL_NO_INDEX_VALUE),
        ];

        return $allAttributes;
    }

    /**
     * Get field mapping for specific attribute.
     *
     * @param AbstractAttribute $attribute
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getField(AbstractAttribute $attribute): array
    {
        $fieldMapping = [];
        if (in_array($attribute->getAttributeCode(), $this->excludedAttributes, true)) {
            return $fieldMapping;
        }

        $attributeAdapter = $this->attributeAdapterProvider->getByAttributeCode($attribute->getAttributeCode());
        $fieldName = $this->fieldNameResolver->getFieldName($attributeAdapter);

        $fieldMapping[$fieldName] = [
            'type' => $this->fieldTypeResolver->getFieldType($attributeAdapter),
        ];
        if ($this->isNeedToAddCustomAnalyzer($fieldName) && $this->getCustomAnalyzer($fieldName)) {
            $fieldMapping[$fieldName]['analyzer'] = $this->getCustomAnalyzer($fieldName);
        }

        $index = $this->fieldIndexResolver->getFieldIndex($attributeAdapter);
        if (null !== $index) {
            $fieldMapping[$fieldName]['index'] = $index;
        }

        if ($attributeAdapter->isSortable() && !$attributeAdapter->isComplexType()) {
            $sortFieldName = $this->fieldNameResolver->getFieldName(
                $attributeAdapter,
                ['type' => FieldMapperInterface::TYPE_SORT]
            );
            $fieldMapping[$fieldName]['fields'][$sortFieldName] = [
                'type' => $this->fieldTypeConverter->convert(
                    FieldTypeConverterInterface::INTERNAL_DATA_TYPE_KEYWORD
                ),
                'index' => $this->indexTypeConverter->convert(
                    IndexTypeConverterInterface::INTERNAL_NO_ANALYZE_VALUE
                )
            ];
        }

        if ($attributeAdapter->isTextType()) {
            $keywordFieldName = FieldTypeConverterInterface::INTERNAL_DATA_TYPE_KEYWORD;
            $index = $this->indexTypeConverter->convert(
                IndexTypeConverterInterface::INTERNAL_NO_ANALYZE_VALUE
            );
            $fieldMapping[$fieldName]['fields'][$keywordFieldName] = [
                'type' => $this->fieldTypeConverter->convert(
                    FieldTypeConverterInterface::INTERNAL_DATA_TYPE_KEYWORD
                )
            ];
            if ($index) {
                $fieldMapping[$fieldName]['fields'][$keywordFieldName]['index'] = $index;
            }
        }

        if ($attributeAdapter->isComplexType()) {
            $childFieldName = $this->fieldNameResolver->getFieldName(
                $attributeAdapter,
                ['type' => FieldMapperInterface::TYPE_QUERY]
            );
            $fieldMapping[$childFieldName] = [
                'type' => $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING)
            ];
            if ($attributeAdapter->isSortable()) {
                $sortFieldName = $this->fieldNameResolver->getFieldName(
                    $attributeAdapter,
                    ['type' => FieldMapperInterface::TYPE_SORT]
                );
                $fieldMapping[$childFieldName]['fields'][$sortFieldName] = [
                    'type' => $this->fieldTypeConverter->convert(
                        FieldTypeConverterInterface::INTERNAL_DATA_TYPE_KEYWORD
                    ),
                    'index' => $this->indexTypeConverter->convert(
                        IndexTypeConverterInterface::INTERNAL_NO_ANALYZE_VALUE
                    )
                ];
            }
        }

        return $fieldMapping;
    }

    /**
     * Check is the custom analyzer exists for the field
     *
     * @param string $fieldName
     * @return bool
     */
    private function isNeedToAddCustomAnalyzer(string $fieldName): bool
    {
        return $fieldName === 'sku';
    }

    /**
     * Getter for the field custom analyzer if it's exists
     *
     * @param string $fieldName
     * @return string|null
     */
    private function getCustomAnalyzer(string $fieldName): ?string
    {
        return $fieldName === 'sku' ? 'sku' : null;
    }
}
