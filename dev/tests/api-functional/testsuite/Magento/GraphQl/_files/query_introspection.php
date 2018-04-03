<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [

    [
        'name' => 'customAttributeMetadata',
        'args' =>
            [
                [
                    'name' => 'attributes',
                    'description' => '',
                    'type' =>
                        [
                            'kind' => 'NON_NULL',
                            'name' => null,
                            'ofType' =>
                                [
                                    'kind' => 'LIST',
                                    'name' => null,
                                ]
                        ],
                        'defaultValue' => null,
                ]
            ]
    ],
     [
         'name' => 'testItem',
         'args' =>
         [
             [
                 'name' => 'id',
                 'description' => '',
                 'type' =>
                     [
                         'kind' => 'NON_NULL',
                         'name' => null,
                         'ofType' =>
                             [
                                 'kind' => 'SCALAR',
                                 'name' => 'Int',
                             ]

                     ],
                     'defaultValue' => null,

             ]
         ]

     ],
     [
         'name' => 'urlResolver',
         'args' =>
         [
             [
                 'name' => 'url',
                 'description' => '',
                 'type' =>
                     [
                         'kind' => 'NON_NULL',
                         'name' => null,
                         'ofType' =>
                             [
                                 'kind' => 'SCALAR',
                                 'name' => 'String',
                             ]
                     ],
                     'defaultValue' => null,
             ]
         ]

     ]
];
