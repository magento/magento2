<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'renderers' => [
        'type_one' => [
            'product_type_one' => 'Renderer\Type\One\Product\One',
            'product_type_two' => 'Renderer\Type\One\Product\Two',
        ],
        'type_two' => [
            'product_type_three' => 'Renderer\Type\Two\Product\Two',
        ],
    ],
    'totals' => [
        'total1' => [
            'title' => 'Title1 Modified',
            'source_field' => 'source1',
            'title_source_field' => 'title_source1',
            'font_size' => '1',
            'display_zero' => 'false',
            'sort_order' => '1',
            'model' => 'Model1',
            'amount_prefix' => 'prefix1',
        ],
        'total2' => [
            'title' => 'Title2',
            'source_field' => 'source2',
            'title_source_field' => 'title_source2',
            'font_size' => '2',
            'display_zero' => 'true',
            'sort_order' => '2',
            'model' => 'Model2',
            'amount_prefix' => 'prefix2',
        ],
        'total3' => [
            'title' => 'Title3',
            'source_field' => 'source3',
            'title_source_field' => 'title_source3',
            'font_size' => '3',
            'display_zero' => 'false',
            'sort_order' => '3',
            'model' => 'Model3',
            'amount_prefix' => 'prefix3',
        ],
    ],
];
