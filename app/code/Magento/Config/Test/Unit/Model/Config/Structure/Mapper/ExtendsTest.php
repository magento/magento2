<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Mapper;

use Magento\Config\Model\Config\Structure\Mapper\ExtendsMapper;
use Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter;
use PHPUnit\Framework\TestCase;

class ExtendsTest extends TestCase
{
    /**
     * @var ExtendsMapper
     */
    protected $_sut;

    protected function setUp(): void
    {
        $this->_sut = new ExtendsMapper(
            new RelativePathConverter()
        );
    }

    /**
     * @dataProvider mapDataProvider
     * @param array $sourceData
     * @param array $resultData
     */
    public function testMap($sourceData, $resultData)
    {
        $this->assertEquals($resultData, $this->_sut->map($sourceData));
    }

    public function testMapWithBadPath()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid path in extends attribute of config/system/sections/section1 node');
        $sourceData = [
            'config' => [
                'system' => ['sections' => ['section1' => ['extends' => 'nonExistentSection2']]],
            ],
        ];

        $this->_sut->map($sourceData);
    }

    /**
     * @return array
     */
    public static function mapDataProvider()
    {
        return [
            [[], []],
            self::_emptySectionsNodeData(),
            self::_extendFromASiblingData(),
            self::_extendFromNodeOnHigherLevelData(),
            self::_extendWithMerge()
        ];
    }

    /**
     * @return array
     */
    protected static function _emptySectionsNodeData()
    {
        $data = ['config' => ['system' => ['sections' => 'some_non_array']]];

        return [$data, $data];
    }

    /**
     * @return array
     */
    protected static function _extendFromASiblingData()
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

    /**
     * @return array
     */
    protected static function _extendFromNodeOnHigherLevelData()
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

    /**
     * @return array
     */
    protected static function _extendWithMerge()
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
