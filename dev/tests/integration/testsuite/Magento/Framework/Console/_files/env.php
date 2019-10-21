<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'backend' => [
        'frontName' => 'admin',
    ],
    'crypt' => [
        'key' => 'some_key',
    ],
    'session' => [
        'save' => 'files',
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [],
    ],
    'resource' => [],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'default',
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'translate' => 1,
        'config_webservice' => 1,
    ],
    'install' => [
        'date' => 'Thu, 09 Feb 2017 14:28:00 +0000',
    ],
    'directories' => [
        'document_root_is_pub' => true
    ]
];
