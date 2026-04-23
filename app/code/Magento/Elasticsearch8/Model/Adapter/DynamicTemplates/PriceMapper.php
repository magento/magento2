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
class PriceMapper implements MapperInterface
{
    /**
     * @inheritdoc
     */
    public function processTemplates(array $templates): array
    {
        $templates[] = [
            'price_mapping' => [
                'match' => 'price_*',
                'match_mapping_type' => 'string',
                'mapping' => [
                    'type' => 'double',
                    'store' => true,
                ],
            ],
        ];

        return $templates;
    }
}
