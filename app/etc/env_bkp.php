<?php
return [
    'backend'            => [
        'frontName' => 'backend'
    ],
    'crypt'              => [
        'key' => '3a478036e3fe5f4ccd846daf6925e699'
    ],
    'x-frame-options'    => 'SAMEORIGIN',
    'MAGE_MODE'          => 'developer',
    'cache_types'        => [
        'config'                 => 0,
        'layout'                 => 0,
        'block_html'             => 0,
        'collections'            => 0,
        'reflection'             => 0,
        'db_ddl'                 => 0,
        'compiled_config'        => 0,
        'eav'                    => 0,
        'customer_notification'  => 0,
        'config_integration'     => 0,
        'config_integration_api' => 0,
        'full_page'              => 0,
        'config_webservice'      => 0,
        'translate'              => 0
    ],
    'install'            => [
        'date' => 'Thu, 22 Oct 2020 07:46:28 +0000'
    ],
    'resource'           => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'system'             => [
        'stores'  => [
            'catalog' => [
                'layered_navigation' => [
                    'price_range_calculation' => 'improved',
                    'interval_division_limit' => 1,
                    'price_range_step' => 20,
                    'price_range_max_intervals' => 10,
                    'one_price_interval' => 1
                ]
            ]
        ]
    ],
    'db'                 => [
        'connection'   => [
            'default' => [
                'host'           => 'db',
                'dbname'         => 'magento',
                'username'       => 'root',
                'password'       => '',
                'model'          => 'mysql4',
                'engine'         => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active'         => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ],
    ],
    'search-store-front' => [
        'connections'            => [
            'default' => [
                'protocol'    => 'http',
                'hostname'    => 'elastic',
                'port'        => '9200',
                'enable_auth' => '',
                'username'    => '',
                'password'    => '',
                'timeout'     => 30
            ]
        ],
        'engine'                 => 'storefrontElasticsearch6',
        'minimum_should_match'   => 1,
        'index_prefix'           => 'magento2',
        'source_current_version' => 1
    ],
];
