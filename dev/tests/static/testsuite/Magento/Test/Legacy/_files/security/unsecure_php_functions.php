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
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'App/Utility/Files.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_Support',
                'path' => 'Console/Command/AbstractBackupDumpCommand.php'
            ],
        ],
    ],
    'srand' => [
        'replacement' => '',
        'exclude' => []
    ],
    'mt_srand' => [
        'replacement' => '',
        'exclude' => [
            [
                'type' => 'setup',
                'path' => 'src/Magento/Setup/Fixtures/ConfigurableProductsFixture.php'
            ],
            [
                'type' => 'setup',
                'path' => 'src/Magento/Setup/Fixtures/PriceProvider.php'
            ],
            [
                'type' => 'setup',
                'path' => 'src/Magento/Setup/Fixtures/SimpleProductsFixture.php'
            ],
        ]
    ],
    'mt_rand' => [
        'replacement' => 'random_int',
        'exclude' => [
            [
                'type' => 'setup',
                'path' => 'src/Magento/Setup/Fixtures/ImagesGenerator/ImagesGenerator.php'
            ],
            [
                'type' => 'setup',
                'path' => 'src/Magento/Setup/Fixtures/BundleProductsFixture.php'
            ],
            [
                'type' => 'setup',
                'path' => 'src/Magento/Setup/Fixtures/ConfigurableProductsFixture.php'
            ],
            [
                'type' => 'setup',
                'path' => 'src/Magento/Setup/Fixtures/OrdersFixture.php'
            ],
            [
                'type' => 'setup',
                'path' => 'src/Magento/Setup/Fixtures/PriceProvider.php'
            ],
            [
                'type' => 'setup',
                'path' => 'src/Magento/Setup/Fixtures/SimpleProductsFixture.php'
            ],
        ]
    ],
];
