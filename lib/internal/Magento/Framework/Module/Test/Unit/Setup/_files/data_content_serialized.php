<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    '$replaceRules' => [
        [
            'table',
            'field',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_SERIALIZED,
        ],
    ],
    '$tableData' => [
        ['field' => 'a:1:{s:5:"model";s:34:"catalogrule/rule_condition_combine";}'],
        ['field' => 'a:1:{s:5:"model";s:16:"some random text";}'],
    ],
    '$expected' => [
        'updates' => [
            [
                'table' => 'table',
                'field' => 'field',
                'to' => 'a:1:{s:5:"model";s:48:"Magento\CatalogRule\Model\Rule\Condition\Combine";}',
                'from' => ['`field` = ?' => 'a:1:{s:5:"model";s:34:"catalogrule/rule_condition_combine";}'],
            ],
        ],
        'aliases_map' => [
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL => [
                'catalogrule/rule_condition_combine' => 'Magento\CatalogRule\Model\Rule\Condition\Combine',
            ],
        ],
    ]
];
