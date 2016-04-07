<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'config' => [
        'system' => [
            'sections' => [
                'section_1' => [
                    'id' => 'section_1',
                    '_elementType' => 'section',
                    'children' => [
                        'group_1' => [
                            'id' => 'group_1',
                            '_elementType' => 'group',
                            'path' => 'section_1',
                            'depends' => [
                                'fields' => [
                                    'field_2' => [
                                        'id' => 'section_1/group_1/field_2',
                                        'value' => 10,
                                        'dependPath' => [
                                            'section_1',
                                            'group_1',
                                            'field_2',
                                        ],
                                    ],
                                ],
                            ],
                            'children' => [
                                'field_2' => [
                                    'id' => 'field_2',
                                    '_elementType' => 'field',
                                ],
                            ],
                        ],
                        'group_2' => [
                            'id' => 'group_2',
                            '_elementType' => 'group',
                            'children' => [
                                'field_3' => [
                                    'id' => 'field_3',
                                    '_elementType' => 'field',
                                ],
                            ],
                        ],
                    ],
                ],
                'section_2' => [
                    'id' => 'section_2',
                    '_elementType' => 'section',
                    'children' => [
                        'group_3' => [
                            'id' => 'group_3',
                            '_elementType' => 'group',
                            'children' => [
                                'field_3' => [
                                    'id' => 'field_3',
                                    '_elementType' => 'field',
                                    'path' => 'section_2/group_3',
                                    'depends' => [
                                        'fields' => [
                                            'field_4' => [
                                                'id' => 'section_2/group_3/field_4',
                                                'value' => 'someValue',
                                                'dependPath' => [
                                                    'section_2',
                                                    'group_3',
                                                    'field_4',
                                                ],
                                            ],
                                            'field_1' => [
                                                'id' => 'section_1/group_3/field_1',
                                                'value' => 'someValue',
                                                'dependPath' => [
                                                    'section_1',
                                                    'group_3',
                                                    'field_1',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'field_4' => [
                                    'id' => 'field_4',
                                    '_elementType' => 'field',
                                    'path' => 'section_2/group_3',
                                    'depends' => [
                                        'fields' => [
                                            'field_3' => [
                                                'id' => 'section_2/group_3/field_3',
                                                'value' => 0,
                                                'dependPath' => [
                                                    'section_2',
                                                    'group_3',
                                                    'field_3',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'group_4_1' => [
                                    'id' => 'group_4_1',
                                    '_elementType' => 'group',
                                    'path' => 'section_2/group_3',
                                    'depends' => [
                                        'fields' => [
                                            'field_3' => [
                                                'id' => 'section_2/group_3/field_3',
                                                'value' => 0,
                                                'dependPath' => [
                                                    'section_2',
                                                    'group_3',
                                                    'field_3',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'children' => [
                                        'field_5' => [
                                            'id' => 'field_5',
                                            '_elementType' => 'field',
                                            'path' => 'section_2/group_3/group_4_1',
                                            'depends' => [
                                                'fields' => [
                                                    'field_4' => [
                                                        'id' => 'section_2/group_3/group_4_1/field_4',
                                                        'value' => 'someValue',
                                                        'dependPath' => [
                                                            'section_2',
                                                            'group_3',
                                                            'group_4_1',
                                                            'field_4',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
