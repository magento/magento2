<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Classes that are restricted to use directly.
 * A <replacement> will be suggested to be used instead.
 * Use <whitelist> to specify files and directories that are allowed to use restricted classes.
 *
 * Format: array(<class_name>, <replacement>[, array(<whitelist>)]])
 */
return [
    'Zend_Db_Select' => [
        'replacement' => '\Magento\Framework\DB\Select',
        'exclude' => [
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'DB/Select.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'DB/Adapter/Pdo/Mysql.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'Model/ResourceModel/Iterator.php'
            ],
        ]
    ],
    'Zend_Db_Adapter_Pdo_Mysql' => [
        'replacement' => '\Magento\Framework\DB\Adapter\Pdo\Mysql',
        'exclude' => [
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'DB/Adapter/Pdo/Mysql.php'
            ],
        ]
    ],
    'Magento\Framework\Serialize\Serializer\Serialize' => [
        'replacement' => 'Magento\Framework\Serialize\SerializerInterface',
        'exclude' => [
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'App/ObjectManager/ConfigLoader/Compiled.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'App/Config/ScopePool.php'],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'App/ObjectManager/ConfigCache.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'App/ObjectManager/ConfigLoader.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'DB/Adapter/Pdo/Mysql.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'DB/DataConverter/SerializedToJson.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'DB/Test/Unit/DataConverter/SerializedToJsonTest.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'ObjectManager/Config/Compiled.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'Interception/Config/Config.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'Interception/PluginList/PluginList.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'App/Router/ActionList.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'Serialize/Test/Unit/Serializer/SerializeTest.php'
            ],
            [
                'type' => 'setup',
                'path' => 'src/Magento/Setup/Module/Di/Compiler/Config/Writer/Filesystem.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_Sales',
                'path' => 'Setup/SerializedDataConverter.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_Sales',
                'path' => 'Test/Unit/Setup/SerializedDataConverterTest.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'Flag.php'
            ]
        ]
    ],
    'ArrayObject' => [
        'replacement' => 'Custom class, extended from ArrayObject with overwritten serialize/unserialize methods',
        'exclude' => [
            [
                'type' => 'module',
                'name' => 'Magento_Theme',
                'path' => 'Model/Indexer/Design/Config.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_Ui',
                'path' => 'Model/Manager.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_Ui',
                'path' => 'Test/Unit/Model/ManagerTest.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_Backend',
                'path' => 'Model/Menu.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_CatalogSearch',
                'path' => 'Model/Indexer/Fulltext.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_CatalogSearch',
                'path' => 'Test/Unit/Model/Indexer/FulltextTest.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_CatalogSearch',
                'path' => 'Model/Indexer/Fulltext.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'Test/Unit/FlagTest.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'Validator/Test/Unit/Constraint/PropertyTest.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'Indexer/Test/Unit/BatchTest.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'View/Element/UiComponent/ArrayObjectFactory.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'View/Element/UiComponent/Config/Provider/Component/Definition.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'Indexer/Action/Base.php'
            ]
        ]
    ],
    'Magento\Framework\View\Element\UiComponent\ArrayObjectFactory' => [
        'replacement' => 'Factory that creates custom class, extended from ArrayObject with overwritten '
            . 'serialize/unserialize methods',
        'exclude' => [
            [
                'type' => 'module',
                'name' => 'Magento_Ui',
                'path' => 'Model/Manager.php'
            ],
            [
                'type' => 'module',
                'name' => 'Magento_Ui',
                'path' => 'Test/Unit/Model/ManagerTest.php'
            ],
            [
                'type' => 'library',
                'name' => 'magento/framework',
                'path' => 'View/Element/UiComponent/Config/Provider/Component/Definition.php'
            ]
        ]
    ]
];
