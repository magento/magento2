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
class PriceMapper implements MapperInterface
{
    /**
     * @inheritDoc
     */
    public function processTemplates(array $templates): array
    {
        $templates[] = [
            'price_mapping' => [
                "match_pattern" => "regex",
                'match' => 'price_\\d+_\\d+',
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
