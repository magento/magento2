<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'application' => [
        'url_host' => '192.168.0.1',
        'url_path' => '/',
        'installation' => [
            'options' => [
                'backend-frontname' => 'backend',
                'admin-user' => 'admin',
                'admin-password' => 'password1',
            ],
        ],
    ],
    'scenario' => ['scenarios' => []],
    'report_dir' => 'report'
];
