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
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_WIKI,
        ],
    ],
    '$tableData' => [
        ['field' => '<p>{{widget type="productalert/product_view"}}</p>'],
        ['field' => '<p>{{widget type="catalogSearch/result"}}</p>'],
        ['field' => '<p>Some HTML code</p>'],
    ],
    '$expected' => [
        'updates' => [
            [
                'table' => 'table',
                'field' => 'field',
                'to' => '<p>{{widget type="Magento\ProductAlert\Block\Product\View"}}</p>',
                'from' => ['`field` = ?' => '<p>{{widget type="productalert/product_view"}}</p>'],
            ],
            [
                'table' => 'table',
                'field' => 'field',
                'to' => '<p>{{widget type="Magento\CatalogSearch\Block\Result"}}</p>',
                'from' => ['`field` = ?' => '<p>{{widget type="catalogSearch/result"}}</p>']
            ],
        ],
        'aliases_map' => [
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK => [
                'productalert/product_view' => 'Magento\ProductAlert\Block\Product\View',
                'catalogSearch/result' => 'Magento\CatalogSearch\Block\Result',
            ],
        ],
    ]
];
