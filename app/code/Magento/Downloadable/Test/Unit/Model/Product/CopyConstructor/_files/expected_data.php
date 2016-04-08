<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'link' => [
        [
            'is_delete' => false,
            'link_id' => null,
            'title' => 'title',
            'is_shareable' => 'is_shareable',
            'sample' => [
                'type' => 'sample_type',
                'url' => 'sample_url',
                'file' => [['file' => 'sample_file', 'name' => 'sample_file', 'size' => 0, 'status' => null]],
            ],
            'file' => [['file' => 'link_file', 'name' => 'link_file', 'size' => 0, 'status' => null]],
            'type' => 'link_type',
            'link_url' => 'link_url',
            'sort_order' => 'sort_order',
            'number_of_downloads' => 'number_of_downloads',
            'price' => 'price',
        ],
    ],
    'sample' => [
        [
            'is_delete' => false,
            'sample_id' => null,
            'title' => 'title',
            'type' => 'sample_type',
            'file' => [['file' => 'sample_file', 'name' => 'sample_file', 'size' => 0, 'status' => null]],
            'sample_url' => 'sample_url',
            'sort_order' => 'sort_order',
        ],
    ]
];
