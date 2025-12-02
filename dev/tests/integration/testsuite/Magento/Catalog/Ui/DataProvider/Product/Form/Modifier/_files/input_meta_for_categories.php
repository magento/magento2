<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

return [
    'product-details' =>
        [
            'children' =>
                ['container_category_ids' =>
                    [
                        'arguments' => [
                            'data' =>
                                [
                                    'config' =>
                                        [
                                            'formElement' => 'container',
                                            'componentType' => 'container',
                                            'breakLine' => false,
                                            'label' => 'Categories',
                                            'required' => '0',
                                            'sortOrder' => 70,
                                        ],
                                ],
                        ],
                        'children' => [
                            'category_ids' =>
                                [
                                    'arguments' =>
                                        [
                                            'data' =>
                                                [
                                                    'config' =>
                                                        [
                                                            'dataType' => 'text',
                                                            'formElement' => 'input',
                                                            'visible' => '1',
                                                            'required' => '0',
                                                            'notice' => null,
                                                            'default' => null,
                                                            'label' => 'Categories',
                                                            'code' => 'category_ids',
                                                            'source' => 'product-details',
                                                            'scopeLabel' => '[GLOBAL]',
                                                            'globalScope' => true,
                                                            'sortOrder' => 70,
                                                            'componentType' => 'field',
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                    ]]]];
