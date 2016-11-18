<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Functions that are not secure to use.
 * A <replacement> will be suggested to be used instead.
 * Use <exclude> to specify files and directories that are allowed to use function.
 *
 * Format: [
 *      <class_name> => [
 *          'replacement' => <replacement>,
 *          'exclude' => [
 *              <exclude>,
 *              <exclude>,
 *          ]
 *      ]
 */
return [
    'unserialize' => [
        'replacement' => '\Magento\Framework\Serialize\SerializerInterface::unserialize',
        'exclude' => [
            ['type' => 'library', 'name' => 'magento/framework', 'path' => 'DB/Adapter/Pdo/Mysql.php'],
            ['type' => 'library', 'name' => 'magento/framework', 'path' => 'Serialize/Serializer/Serialize.php'],
        ]
    ],
    'serialize' => [
        'replacement' => '\Magento\Framework\Serialize\SerializerInterface::serialize',
        'exclude' => [
            ['type' => 'library', 'name' => 'magento/framework', 'path' => 'DB/Adapter/Pdo/Mysql.php'],
            ['type' => 'library', 'name' => 'magento/framework', 'path' => 'Serialize/Serializer/Serialize.php'],
        ]
    ],
    'eval' => [
        'replacement' => '',
        'exclude' => []
    ],
    'md5' => [
        'replacement' => '',
        'exclude' => []
    ],
    'srand' => [
        'replacement' => '',
        'exclude' => []
    ],
    'mt_srand' => [
        'replacement' => '',
        'exclude' => []
    ],
];
