<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'application' => [
        'url_host' => '127.0.0.1',
        'url_path' => '/',
        'installation' => [
            'options' => [
                'option1' => 'value 1',
                'option2' => 'value 2',
                'backend_frontname' => 'backend',
                'admin_username' => 'admin',
                'admin_password' => 'password1',
            ],
        ],
    ],
    'scenario' => [
        'common_config' => [
            'arguments' => ['arg1' => 'value 1', 'arg2' => 'value 2'],
            'settings' => ['setting1' => 'setting 1', 'setting2' => 'setting 2'],
            'fixtures' => ['fixture2.php'],
        ],
        'scenarios' => [
            'Scenario' => [
                'file' => 'scenario.jmx',
                'arguments' => [
                    'arg2' => 'overridden value 2',
                    'arg3' => 'custom value 3',
                    \Magento\TestFramework\Performance\Scenario::ARG_HOST => 'no crosscutting params',
                ],
                'settings' => ['setting2' => 'overridden setting 2', 'setting3' => 'setting 3'],
                'fixtures' => ['fixture.php'],
            ],
            'Scenario with Error' => ['file' => 'scenario_error.jmx'],
            'Scenario with Failure' => [
                'file' => 'scenario_failure.jmx',
                'settings' => [\Magento\TestFramework\Performance\Testsuite::SETTING_SKIP_WARM_UP => true],
            ],
        ],
    ],
    'report_dir' => 'report'
];
