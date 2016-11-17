<?php
/**
 * Classes that are restricted to use directly.
 * A <replacement> will be suggested to be used instead.
 * Use <whitelist> to specify files and directories that are allowed to use restricted classes.
 *
 * Format: array(<class_name>, <replacement>[, array(<whitelist>)]])
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        'replacement' => 'No suggested replacement',
        'exclude' => []
    ],
    'md5' => [
        'replacement' => 'No suggested replacement',
        'exclude' => []
    ],
    'srand' => [
        'replacement' => 'No suggested replacement',
        'exclude' => []
    ],
    'mt_srand' => [
        'replacement' => 'No suggested replacement',
        'exclude' => []
    ],
];
