<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'media' => [
        'path' => 'media',
        'dirname' => '.',
        'basename' => 'media',
        'filename' => 'media',
        'type' => 'dir',
        'size' => null,
        'timestamp' => null,
        'visibility' => null,
        'mimetype' => '',
    ],
    'media/tmp' => [
        'path' => 'media/tmp',
        'dirname' => 'media',
        'basename' => 'tmp',
        'filename' => 'tmp',
        'type' => 'dir',
    ],
    'media/tmp/catalog' => [
        'path' => 'media/tmp/catalog',
        'dirname' => 'media/tmp',
        'basename' => 'catalog',
        'filename' => 'catalog',
        'type' => 'dir',
    ],
    'media/tmp/catalog/product' => [
        'path' => 'media/tmp/catalog/product',
        'dirname' => 'media/tmp/catalog',
        'basename' => 'product',
        'filename' => 'product',
        'type' => 'dir',
    ],
    'media/tmp/catalog/product/1' => [
        'path' => 'media/tmp/catalog/product/1',
        'dirname' => 'media/tmp/catalog/product',
        'basename' => '1',
        'filename' => '1',
        'type' => 'dir',
    ],
    'media/tmp/catalog/product/1/test.jpeg' => [
        'path' => 'media/tmp/catalog/product/1/test.jpeg',
        'dirname' => 'media/tmp/catalog/product/1',
        'basename' => 'test.jpeg',
        'extension' => 'jpeg',
        'filename' => 'test.jpeg',
        'type' => 'file',
        'size' => '87066',
        'timestamp' => '1635860865',
        'visibility' => null,
        'mimetype' => 'image/jpeg',
        'extra' => [
            'image-width' => 680,
            'image-height' => 383,
        ],
    ],
];
