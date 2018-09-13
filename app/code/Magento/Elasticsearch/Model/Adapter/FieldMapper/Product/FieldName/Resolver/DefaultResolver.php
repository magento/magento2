<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Resolver;

use Magento\Eav\Model\Config;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldType;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\ResolverInterface;

/**
 * Default name resolver.
 */
class DefaultResolver implements ResolverInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var FieldType
     */
    private $fieldType;

    /**
     * @param Config $eavConfig
     * @param FieldType $fieldType
     */
    public function __construct(
        Config $eavConfig,
        FieldType $fieldType
    ) {
        $this->eavConfig = $eavConfig;
        $this->fieldType = $fieldType;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName($attributeCode, $context = [])
    {
        $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
        $fieldType = $this->fieldType->getFieldType($attribute);
        $frontendInput = $attribute->getFrontendInput();
        if (empty($context['type'])) {
            $fieldName = $attributeCode;
        } elseif ($context['type'] === FieldMapperInterface::TYPE_FILTER) {
            if (in_array($fieldType, ['string', FieldType::ES_DATA_TYPE_TEXT], true)) {
                return $this->getFieldName(
                    $attributeCode,
                    array_merge($context, ['type' => FieldMapperInterface::TYPE_QUERY])
                );
            }
            $fieldName = $attributeCode;
        } elseif ($context['type'] === FieldMapperInterface::TYPE_QUERY) {
            $fieldName = $this->getQueryTypeFieldName($frontendInput, $fieldType, $attributeCode);
        } else {
            $fieldName = 'sort_' . $attributeCode;
        }

        return $fieldName;
    }

    /**
     * @param string $frontendInput
     * @param string $fieldType
     * @param string $attributeCode
     * @return string
     */
    private function getQueryTypeFieldName($frontendInput, $fieldType, $attributeCode)
    {
        if ($attributeCode === '*') {
            $fieldName = '_all';
        } else {
            $fieldName = $this->getRefinedFieldName($frontendInput, $fieldType, $attributeCode);
        }
        return $fieldName;
    }

    /**
     * @param string $frontendInput
     * @param string $fieldType
     * @param string $attributeCode
     * @return string
     */
    private function getRefinedFieldName($frontendInput, $fieldType, $attributeCode)
    {
        switch ($frontendInput) {
            case 'select':
                return in_array($fieldType, ['text','integer'], true) ? $attributeCode . '_value' : $attributeCode;
            case 'boolean':
                return $fieldType === 'integer' ? $attributeCode . '_value' : $attributeCode;
            default:
                return $attributeCode;
        }
    }
}
