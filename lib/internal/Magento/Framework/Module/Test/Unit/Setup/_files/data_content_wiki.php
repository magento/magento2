<?php declare(strict_types=1);

use Magento\CatalogSearch\Block\Result;
use Magento\Framework\Module\Setup\Migration;
use Magento\ProductAlert\Block\Product\View;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    '$replaceRules' => [
        [
            'table',
            'field',
            Migration::ENTITY_TYPE_BLOCK,
            Migration::FIELD_CONTENT_TYPE_WIKI,
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
            Migration::ENTITY_TYPE_BLOCK => [
                'productalert/product_view' => View::class,
                'catalogSearch/result' => Result::class,
            ],
        ],
    ]
];
