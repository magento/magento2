<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Mapper;

class ExtendsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Mapper\ExtendsMapper
     */
    protected $_sut;

    protected function setUp()
    {
        $this->_sut = new \Magento\Config\Model\Config\Structure\Mapper\ExtendsMapper(
            new \Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter()
        );
    }

    /**
     * @dataProvider testMapDataProvider
     * @param array $sourceData
     * @param array $resultData
     */
    public function testMap($sourceData, $resultData)
    {
        $this->assertEquals($resultData, $this->_sut->map($sourceData));
    }

    public function testMapWithBadPath()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Invalid path in extends attribute of config/system/sections/section1 node'
        );
        $sourceData = [
            'config' => [
                'system' => ['sections' => ['section1' => ['extends' => 'nonExistentSection2']]],
            ],
        ];

        $this->_sut->map($sourceData);
    }

    public function testMapDataProvider()
    {
        return [
            [[], []],
            $this->_emptySectionsNodeData(),
            $this->_extendFromASiblingData(),
            $this->_extendFromNodeOnHigherLevelData(),
            $this->_extendWithMerge()
        ];
    }

    protected function _emptySectionsNodeData()
    {
        $data = ['config' => ['system' => ['sections' => 'some_non_array']]];

        return [$data, $data];
    }

    protected function _extendFromASiblingData()
    {
        $source = $result = [
            'config' => [
                'system' => [
                    'sections' => [
                        'section1' => ['children' => ['child1', 'child2', 'child3']],
                        'section2' => ['extends' => 'section1'],
                    ],
                ],
            ],
        ];

        $result['config']['system']['sections']['section2']['children'] =
            $source['config']['system']['sections']['section1']['children'];

        return [$source, $result];
    }

    protected function _extendFromNodeOnHigherLevelData()
    {
        $source = $result = [
            'config' => [
                'system' => [
                    'sections' => [
                        'section1' => [
                            'children' => [
                                'child1' => [
                                    'children' => [
                                        'subchild1' => 1,
                                        'subchild2' => ['extends' => '*/child2'],
                                    ],
                                ],
                                'child2' => ['some' => 'Data', 'for' => 'node', 'being' => 'extended'],
                                'child3' => 3,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result['config']['system']['sections']['section1']['children']['child1']['children']['subchild2']['some'] =
            'Data';
        $result['config']['system']['sections']['section1']['children']['child1']['children']['subchild2']['for'] =
            'node';
        $result['config']['system']['sections']['section1']['children']['child1']['children']['subchild2']['being'] =
            'extended';

        return [$source, $result];
    }

    protected function _extendWithMerge()
    {
        $source = $result = [
            'config' => [
                'system' => [
                    'sections' => [
                        'section1' => [
                            'scalarValue1' => 1,
                            'children' => ['child1' => 1, 'child2' => 2, 'child3' => 3],
                        ],
                        'section2' => [
                            'extends' => 'section1',
                            'scalarValue1' => 2,
                            'children' => ['child4' => 4, 'child5' => 5, 'child1' => 6],
                        ],
                    ],
                ],
            ],
        ];

        $section2 = & $result['config']['system']['sections']['section2'];
        $section2['children'] = ['child4' => 4, 'child5' => 5, 'child1' => 6, 'child2' => 2, 'child3' => 3];

        return [$source, $result];
    }
}
