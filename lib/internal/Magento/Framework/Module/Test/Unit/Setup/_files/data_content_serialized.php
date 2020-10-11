<?php declare(strict_types=1);

use Magento\Framework\Module\Setup\Migration;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    '$replaceRules' => [
        [
            'table',
            'field',
            Migration::ENTITY_TYPE_MODEL,
            Migration::FIELD_CONTENT_TYPE_SERIALIZED,
        ],
    ],
    '$tableData' => [
        ['field' => '{"max_text_length":255,"min_text_length":1}'],
        ['field' => '{"model":"some random text"}'],
    ],
    '$expected' => [
        'updates' => [
            [
                'table' => 'table',
                'field' => 'field',
                'to' => '{"model":"Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine"}',
                'from' => ['`field` = ?' => '{"model":"catalogrule\/rule_condition_combine"}'],
            ],
        ],
        'aliases_map' => [
            Migration::ENTITY_TYPE_MODEL => [
                'catalogrule/rule_condition_combine' => 'Magento\CatalogRule\Model\Rule\Condition\Combine',
            ],
        ],
    ]
];
