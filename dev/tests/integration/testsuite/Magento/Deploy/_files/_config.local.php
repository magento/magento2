<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'scopes' => [
        'websites' => []
    ],
    /**
     * The configuration file doesn't contain sensitive data for security reasons.
     * Sensitive data can be stored in the following environment variables:
     * CONFIG__DEFAULT__SOME__CONFIG__PATH_ONE for some/config/path_one
     * CONFIG__DEFAULT__SOME__CONFIG__PATH_TWO for some/config/path_two
     * CONFIG__DEFAULT__SOME__CONFIG__PATH_THREE for some/config/path_three
     */
    'system' => [
        'default' => [
            'web' => [],
            'general' => []
        ]
    ]
];
