<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    '$replaceRules' => [
        [
            'table',
            'field',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_XML,
        ],
    ],
    '$tableData' => [
        ['field' => '<reference><block class="catalog/product_newProduct" /></reference>'],
        ['field' => '<reference><block class="catalogSearch/result" /></reference>'],
        ['field' => '<reference></reference>'],
    ],
    '$expected' => [
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
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_BLOCK => [
                'catalog/product_newProduct' => 'Magento\Catalog\Block\Product\NewProduct',
                'catalogSearch/result' => 'Magento\CatalogSearch\Block\Result',
            ],
        ],
    ]
];
