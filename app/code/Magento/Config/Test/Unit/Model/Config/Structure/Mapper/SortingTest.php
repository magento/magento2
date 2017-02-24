<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Mapper;

class SortingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Mapper\Sorting
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Config\Model\Config\Structure\Mapper\Sorting();
    }

    public function testMap()
    {
        $tabs = [
            'tab_1' => ['sortOrder' => 10],
            'tab_2' => ['sortOrder' => 5],
            'tab_3' => ['sortOrder' => 1],
        ];

        $sections = [
            'section_1' => ['sortOrder' => 10],
            'section_2' => ['sortOrder' => 5],
            'section_3' => ['sortOrder' => 1],
            'section_4' => [
                'sortOrder' => 500,
                'children' => [
                    'group_1' => ['sortOrder' => 150],
                    'group_2' => ['sortOrder' => 20],
                    'group_3' => [
                        'sortOrder' => 30,
                        'children' => [
                            'field_1' => ['sortOrder' => 200],
                            'field_2' => ['sortOrder' => 100],
                            'subGroup' => [
                                'sortOrder' => 0,
                                'children' => [
                                    'field_4' => ['sortOrder' => 200],
                                    'field_5' => ['sortOrder' => 100],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $data = ['config' => ['system' => ['tabs' => $tabs, 'sections' => $sections]]];

        $expected = [
            'config' => [
                'system' => [
                    'tabs' => [
                        'tab_3' => ['sortOrder' => 1],
                        'tab_2' => ['sortOrder' => 5],
                        'tab_1' => ['sortOrder' => 10],
                    ],
                    'sections' => [
                        'section_3' => ['sortOrder' => 1],
                        'section_2' => ['sortOrder' => 5],
                        'section_1' => ['sortOrder' => 10],
                        'section_4' => [
                            'sortOrder' => 500,
                            'children' => [
                                'group_2' => ['sortOrder' => 20],
                                'group_3' => [
                                    'sortOrder' => 30,
                                    'children' => [
                                        'subGroup' => [
                                            'sortOrder' => 0,
                                            'children' => [
                                                'field_5' => ['sortOrder' => 100],
                                                'field_4' => ['sortOrder' => 200],
                                            ],
                                        ],
                                        'field_2' => ['sortOrder' => 100],
                                        'field_1' => ['sortOrder' => 200],
                                    ],
                                ],
                                'group_1' => ['sortOrder' => 150],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $actual = $this->_model->map($data);
        $this->assertEquals($expected, $actual);
    }
}
