<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    [
        'kind'=> 'OBJECT',
        'name'=> 'Currency',
        'description'=> '',
        'fields'=> [
            [
                'name'=> 'available_currency_codes',
                'description'=> null,
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'base_currency_code',
                'description'=> null,
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],

            [
                'name'=> 'base_currency_symbol',
                'description'=> null,
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'default_display_currecy_code',
                'description'=> null,
                'isDeprecated'=> true,
                'deprecationReason'=> 'Symbol was missed. Use `default_display_currency_code`.'
            ],
            [
                'name'=> 'default_display_currecy_symbol',
                'description'=> null,
                'isDeprecated'=> true,
                'deprecationReason'=> 'Symbol was missed. Use `default_display_currency_code`.'
            ],
            [
                'name'=> 'default_display_currency_code',
                'description'=> null,
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'default_display_currency_symbol',
                'description'=> null,
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],
            [
                'name'=> 'exchange_rates',
                'description'=> null,
                'isDeprecated'=> false,
                'deprecationReason'=> null
            ],

        ],
        'enumValues'=> null
    ],
    [
        'kind' =>  'ENUM',
        'name' =>  'DownloadableFileTypeEnum',
        'description' =>  '',
        'fields' =>  null,
        'enumValues' =>  [
            [
                'name' =>  'FILE',
                'description' =>  '',
                'isDeprecated' =>  true,
                'deprecationReason' => 'sample_url` serves to get the downloadable sample'
            ],
            [
                'name' =>  'URL',
                'description' =>  '',
                'isDeprecated' =>  true,
                'deprecationReason' => '`sample_url` serves to get the downloadable sample'
            ]
        ],
        'possibleTypes' =>  null
    ],
];
