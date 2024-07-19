<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\Model\Adapter\DynamicTemplates;

/**
 * @inheridoc
 * @deprecated because of EOL for Elasticsearch7
 * @see this class will be responsible for ES7 only
 */
class StringMapper implements MapperInterface
{
    /**
     * Add/remove/edit dynamic template mapping.
     *
     * @param array $templates
     *
     * @return array
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
