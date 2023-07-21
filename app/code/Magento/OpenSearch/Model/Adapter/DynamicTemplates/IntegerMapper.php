<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Model\Adapter\DynamicTemplates;

/**
 * @inheritDoc
 */
class IntegerMapper implements MapperInterface
{
    /**
     * @inheritDoc
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
