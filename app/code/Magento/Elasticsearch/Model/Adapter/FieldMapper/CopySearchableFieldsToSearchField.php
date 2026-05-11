<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
     * @var array
     */
    private array $exclude = [];
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
            if ($this->isSearchable((string) $field, $definition)) {
                $definition['copy_to'][] = '_search';
                $mapping[$field] = $definition;
            }
        }
        // Reset exclude list after processing
        $this->exclude = [];
        return $mapping;
    }

    /**
     * Add fields to exclude from copying to search field
     *
     * @param array $fields
     * @return void
     */
    public function addExclude(array $fields): void
    {
        $this->exclude += array_fill_keys($fields, true);
    }

    /**
     * Determine if the field is searchable by mapping
     *
     * The field is searchable if it's indexed and its mapping type is either "text" or "keyword"
     *
     * @param string $field
     * @param array $mapping
     * @return bool
     */
    private function isSearchable(string $field, array $mapping): bool
    {
        return in_array($mapping['type'] ?? null, self::FIELD_TYPES)
            && (($mapping['index'] ?? true) !== false)
            && !isset($this->exclude[$field]);
    }
}
