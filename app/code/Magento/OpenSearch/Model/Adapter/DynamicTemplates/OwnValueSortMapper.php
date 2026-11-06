<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Model\Adapter\DynamicTemplates;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\OwnValueSortField;

/**
 * Map own-value sort fields that reach the index without an explicit mapping as sortable keywords.
 *
 * Must be configured before the string template, which would otherwise map them as text.
 */
class OwnValueSortMapper implements MapperInterface
{
    /**
     * @inheritDoc
     */
    public function processTemplates(array $templates): array
    {
        $templates[] = [
            'own_value_sort_mapping' => [
                'match' => OwnValueSortField::PREFIX . '*',
                'match_mapping_type' => 'string',
                'mapping' => [
                    'type' => 'keyword',
                    'index' => false,
                    'normalizer' => 'folding',
                ],
            ],
        ];

        return $templates;
    }
}
