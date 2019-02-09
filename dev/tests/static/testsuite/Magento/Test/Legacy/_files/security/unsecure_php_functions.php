<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        'exclude' => [
            /*
             * Usage of md5 in MessageQueue key generation algorithm
             * added to exclude list to avoid backward incompatible changes
             */
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'MessageQueue/Rpc/Publisher.php',
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'MessageQueue/MessageController.php',
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'MessageQueue/Publisher.php',
            ],
            [
                'type' => 'module',
                'name' => 'Magento_AsynchronousOperations',
                'path' => 'Model/ResourceModel/System/Message/Collection/Synchronized/Plugin.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_AuthorizenetAcceptjs',
                'path' => 'Gateway/Validator/TransactionHashValidator.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_Authorizenet',
                'path' => 'Model/Directpost/Response.php'
            ]
        ]
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
