<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\Model\Adapter\DynamicTemplates;

/**
 * @inheridoc
 */
class StringMapper implements MapperInterface
{
    /**
     * @inheridoc
     */
    public function processTemplates(array $templates): array
    {
        $templates[] = [
            'string_mapping' => [
                'match' => '*',
                'match_mapping_type' => 'string',
                'mapping' => [
                    'type' => 'text',
                    'index' => true,
                    'copy_to' => '_search',
                ],
            ],
        ];

        return $templates;
    }
}
