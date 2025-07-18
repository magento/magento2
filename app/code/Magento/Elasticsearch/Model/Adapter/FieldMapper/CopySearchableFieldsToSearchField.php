<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper;

use Magento\Elasticsearch\Model\Adapter\FieldsMappingPreprocessorInterface;

/**
 * Add "copy_to" parameter for default search field to index fields.
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
class CopySearchableFieldsToSearchField implements FieldsMappingPreprocessorInterface
{
    /**
     * List of field types to copy
     */
    private const FIELD_TYPES = ['text', 'keyword'];
    /**
     * Add "copy_to" parameter for default search field to index fields.
     *
     * Emulates catch all field (_all) for elasticsearch
     *
     * @param array $mapping
     * @return array
     */
    public function process(array $mapping): array
    {
        foreach ($mapping as $field => $definition) {
            if ($this->isSearchable($definition)) {
                $definition['copy_to'][] = '_search';
                $mapping[$field] = $definition;
            }
        }
        return $mapping;
    }

    /**
     * Determine if the field is searchable by mapping
     *
     * The field is searchable if it's indexed and its mapping type is either "text" or "keyword"
     *
     * @param array $mapping
     * @return bool
     */
    private function isSearchable(array $mapping): bool
    {
        return in_array($mapping['type'] ?? null, self::FIELD_TYPES) && (($mapping['index'] ?? true) !== false);
    }
}
