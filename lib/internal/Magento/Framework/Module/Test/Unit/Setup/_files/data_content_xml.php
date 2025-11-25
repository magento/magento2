<?php declare(strict_types=1);

use Magento\Catalog\Block\Product\NewProduct;
use Magento\CatalogSearch\Block\Result;
use Magento\Framework\Module\Setup\Migration;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'replaceRules' => [
        [
            'table',
            'field',
            Migration::ENTITY_TYPE_BLOCK,
            Migration::FIELD_CONTENT_TYPE_XML,
        ],
    ],
    'tableData' => [
        ['field' => '<reference><block class="catalog/product_newProduct" /></reference>'],
        ['field' => '<reference><block class="catalogSearch/result" /></reference>'],
        ['field' => '<reference></reference>'],
    ],
    'expected' => [
        'updates' => [
            [
                'table' => 'table',
                'field' => 'field',
                'to' => '<reference><block class="Magento\Catalog\Block\Product\NewProduct" /></reference>',
                'from' => ['`field` = ?' => '<reference><block class="catalog/product_newProduct" /></reference>'],
            ],
            [
                'table' => 'table',
                'field' => 'field',
                'to' => '<reference><block class="Magento\CatalogSearch\Block\Result" /></reference>',
                'from' => ['`field` = ?' => '<reference><block class="catalogSearch/result" /></reference>']
            ],
        ],
        'aliases_map' => [
            Migration::ENTITY_TYPE_BLOCK => [
                'catalog/product_newProduct' => NewProduct::class,
                'catalogSearch/result' => Result::class,
            ],
        ],
    ]
];
