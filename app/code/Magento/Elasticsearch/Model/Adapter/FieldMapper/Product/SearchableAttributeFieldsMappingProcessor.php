<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\CopySearchableFieldsToSearchField;

/**
 * Processes attribute fields mapping for searchable attributes.
 *
 * This processor could eventually replace CopySearchableFieldsToSearchField by adding directly the "copy_to" mapping
 * for search fields here.
 * For backward compatibility, we will keep CopySearchableFieldsToSearchField and instruct it to exclude
 * non-searchable fields.
 * CopySearchableFieldsToSearchField cannot determine if a field is searchable because it lacks attribute metadata.
 */
class SearchableAttributeFieldsMappingProcessor implements AttributeFieldsMappingProcessorInterface
{
    /**
     * @param Config $eavConfig
     * @param CopySearchableFieldsToSearchField $copySearchableFieldsToSearchField
     */
    public function __construct(
        private readonly Config $eavConfig,
        private readonly CopySearchableFieldsToSearchField $copySearchableFieldsToSearchField
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(string $attributeCode, array $mapping, array $context = []): array
    {
        $attribute = $this->eavConfig->getAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
        
        if ($attribute && !$attribute->getIsSearchable()) {
            $this->copySearchableFieldsToSearchField->addExclude(array_keys($mapping));
        }

        return $mapping;
    }
}
