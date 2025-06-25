<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch8\Model\Adapter\DynamicTemplates;

/**
 * @inheridoc
 * @deprecated Elasticsearch8 is no longer supported by Adobe
 * @see this class will be responsible for ES8 only
 */
class IntegerMapper implements MapperInterface
{
    /**
     * @inheritdoc
     */
    public function processTemplates(array $templates): array
    {
        $templates[] = [
            'integer_mapping' => [
                'match_mapping_type' => 'long',
                'mapping' => [
                    'type' => 'integer',
                ],
            ],
        ];

        return $templates;
    }
}
