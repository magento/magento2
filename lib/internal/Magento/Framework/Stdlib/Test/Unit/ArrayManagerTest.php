<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Stdlib\ArrayManager;

class ArrayManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->arrayManager = $this->objectManagerHelper->getObject(ArrayManager::class);
    }

    /**
     * @param string $path
     * @param array $data
     * @param bool $result
     * @dataProvider existsDataProvider
     */
    public function testExists($path, $data, $result)
    {
        $this->assertSame($result, $this->arrayManager->exists($path, $data));
    }

    /**
     * @return array
     */
    public function existsDataProvider()
    {
        return [
            0 => [
                'path' => 'some/path',
                'data' => ['some' => ['path' => null]],
                'result' => true
            ],
            1 => [
                'path' => '0/0/test',
                'data' => [[['test' => false]]],
                'result' => true
            ],
            2 => [
                'path' => 'invalid/path',
                'data' => ['valid' => ['path' => 0]],
                'result' => false
            ]
        ];
    }

    public function testExistsCustomDelimiter()
    {
        $data = ['custom' => ['delimiter' => null]];

        $this->assertFalse($this->arrayManager->exists('custom/delimiter', $data, '~'));
        $this->assertTrue($this->arrayManager->exists('custom~delimiter', $data, '~'));
    }

    /**
     * @param string $path
     * @param array $data
     * @param mixed $result
     * @dataProvider getDataProvider
     */
    public function testGet($path, $data, $result)
    {
        $this->assertSame($result, $this->arrayManager->get($path, $data));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            0 => [
                'path' => 'nested/path/0',
                'data' => ['nested' => ['path' => ['value1']]],
                'result' => 'value1'
            ],
            1 => [
                'path' => '0',
                'data' => [false],
                'result' => false
            ],
            2 => [
                'path' => 'invalid/path/0',
                'data' => [],
                'result' => null
            ]
        ];
    }

    /**
     * @param string $path
     * @param array $data
     * @param mixed $value
     * @param array $result
     * @dataProvider setDataProvider
     */
    public function testSet($path, $data, $value, $result)
    {
        $this->assertSame($result, $this->arrayManager->set($path, $data, $value));
    }

    /**
     * @return array
     */
    public function setDataProvider()
    {
        return [
            0 => [
                'path' => '0/1',
                'data' => [[false, false]],
                'value' => true,
                'result' => [[false, true]]
            ],
            1 => [
                'path' => 'test',
                'data' => ['test' => ['lost data']],
                'value' => 'found data',
                'result' => ['test' => 'found data']
            ],
            2 => [
                'path' => 'new/path/2',
                'data' => ['existing' => ['path' => 1]],
                'value' => 'valuable data',
                'result' => ['existing' => ['path' => 1], 'new' => ['path' => [2 => 'valuable data']]]
            ],
            3 => [
                'path' => ['new', 'path/2'],
                'data' => ['existing' => ['path' => 1]],
                'value' => 'valuable data',
                'result' => ['existing' => ['path' => 1], 'new' => ['path' => [2 => 'valuable data']]]
            ]
        ];
    }

    /**
     * @param string $path
     * @param array $data
     * @param mixed $value
     * @param array $result
     * @dataProvider setDataProvider
     */
    public function testReplace($path, $data, $value, $result)
    {
        $this->assertSame($result, $this->arrayManager->set($path, $data, $value));
    }

    /**
     * @return array
     */
    public function setReplaceProvider()
    {
        return [
            0 => [
                'path' => '0/1',
                'data' => [[false, false]],
                'value' => true,
                'result' => [[false, true]]
            ],
            1 => [
                'path' => 'test',
                'data' => ['test' => ['lost data']],
                'value' => 'found data',
                'result' => ['test' => 'found data']
            ],
            2 => [
                'path' => 'new/path/2',
                'data' => ['existing' => ['path' => 1]],
                'value' => 'valuable data',
                'result' => ['existing' => ['path' => 1]]
            ],
            3 => [
                'path' => ['new', 'path', '2'],
                'data' => ['existing' => ['path' => 1]],
                'value' => 'valuable data',
                'result' => ['existing' => ['path' => 1]]
            ]
        ];
    }

    /**
     * @param string $path
     * @param string $targetPath
     * @param array $data
     * @param bool $overwrite
     * @param array $result
     * @dataProvider moveDataProvider
     */
    public function testMove($path, $targetPath, array $data, $overwrite, array $result)
    {
        $this->assertSame($result, $this->arrayManager->move($path, $targetPath, $data, $overwrite));
    }

    /**
     * @return array
     */
    public function moveDataProvider()
    {
        return [
            0 => [
                'path' => 'not/valid/path',
                'targetPath' => 'target/path',
                'data' => ['valid' => ['path' => 'value']],
                'overwrite' => false,
                'result' => ['valid' => ['path' => 'value']]
            ],
            1 => [
                'path' => 'valid/path',
                'targetPath' => 'target/path',
                'data' => ['valid' => ['path' => 'value']],
                'overwrite' => false,
                'result' => ['valid' => [], 'target' => ['path' => 'value']]
            ],
            2 => [
                'path' => 'valid/path',
                'targetPath' => 'target/path',
                'data' => ['valid' => ['path' => 'value'], 'target' => ['path' => 'exists']],
                'overwrite' => false,
                'result' => ['valid' => ['path' => 'value'], 'target' => ['path' => 'exists']]
            ],
            3 => [
                'path' => 'valid/path',
                'targetPath' => 'target/path',
                'data' => ['valid' => ['path' => 'value'], 'target' => ['path' => 'exists']],
                'overwrite' => true,
                'result' => ['valid' => [], 'target' => ['path' => 'value']]
            ],
            4 => [
                'path' => ['valid', 'path'],
                'targetPath' => 'target/path',
                'data' => ['valid' => ['path' => 'value'], 'target' => ['path' => 'exists']],
                'overwrite' => true,
                'result' => ['valid' => [], 'target' => ['path' => 'value']]
            ]
        ];
    }

    /**
     * @param string $path
     * @param array $data
     * @param array $value
     * @param array $result
     * @dataProvider mergeDataProvider
     */
    public function testMerge($path, $data, $value, $result)
    {
        $this->assertSame($result, $this->arrayManager->merge($path, $data, $value));
    }

    /**
     * @return array
     */
    public function mergeDataProvider()
    {
        return [
            0 => [
                'path' => '0/path/1',
                'data' => [['path' => [false, ['value' => false]]]],
                'value' => ['value' => true, 'new_value' => false],
                'result' => [['path' => [false, ['value' => true, 'new_value' => false]]]]
            ],
            1 => [
                'path' => 0,
                'data' => [['nested' => ['test' => 2, 'test2' => 1]]],
                'value' => ['nested' => ['test' => 3], 'more' => 4],
                'result' => [['nested' => ['test' => 3, 'test2' => 1], 'more' => 4]]
            ],
            2 => [
                'path' => 'invalid/path',
                'data' => [],
                'value' => [true],
                'result' => []
            ],
            3 => [
                'path' => ['0', 'path/1'],
                'data' => [['path' => [false, ['value' => false]]]],
                'value' => ['value' => true, 'new_value' => false],
                'result' => [['path' => [false, ['value' => true, 'new_value' => false]]]]
            ],
        ];
    }

    /**
     * @param string $path
     * @param array $data
     * @param array $result
     * @dataProvider populateDataProvider
     */
    public function testPopulate($path, $data, $result)
    {
        $this->assertSame($result, $this->arrayManager->populate($path, $data));
    }

    /**
     * @return array
     */
    public function populateDataProvider()
    {
        return [
            0 => [
                'path' => 'some/is/not/array',
                'data' => ['some' => true],
                'result' => ['some' => true]
            ],
            1 => [
                'path' => 0,
                'data' => [],
                'result' => [[]]
            ],
            2 => [
                'path' => 'nested/1/array',
                'data' => ['nested' => [true]],
                'result' => ['nested' => [true, ['array' => []]]]
            ]
        ];
    }

    /**
     * @param string $path
     * @param array $data
     * @param array $result
     * @dataProvider removeDataProvider
     */
    public function testRemove($path, $data, $result)
    {
        $this->assertSame($result, $this->arrayManager->remove($path, $data));
    }

    /**
     * @return array
     */
    public function removeDataProvider()
    {
        return [
            0 => [
                'path' => '0/0/0/0',
                'data' => [[[[null]]]],
                'result' => [[[[]]]]
            ],
            1 => [
                'path' => 'simple',
                'data' => ['simple' => true, 'complex' => false],
                'result' => ['complex' => false]
            ],
            2 => [
                'path' => 'invalid',
                'data' => [true],
                'result' => [true]
            ],
            3 => [
                'path' => ['simple'],
                'data' => ['simple' => true, 'complex' => false],
                'result' => ['complex' => false]
            ],
        ];
    }

    /**
     * @param array|mixed $indexes
     * @param array $data
     * @param string|array|null $startPath
     * @param string|array|null $internalPath
     * @param array $result
     * @dataProvider findPathsDataProvider
     */
    public function testFindPaths($indexes, array $data, $startPath, $internalPath, $result)
    {
        $this->assertSame($result, $this->arrayManager->findPaths($indexes, $data, $startPath, $internalPath));
    }

    /**
     * @return array
     */
    public function findPathsDataProvider()
    {
        $data = [
            'element1' => [
                'children' => [
                    'element11' => [
                        'children' => [true, true]
                    ],
                    'element12' => [
                        'config' => [
                            'argument' => [
                                'data' => true
                            ]
                        ]
                    ]
                ]
            ],
            'element2' => [
                'children' => [true, true, true]
            ],
            '' => [
                [[[[]]]]
            ]
        ];

        return [
            0 => [
                'indexes' => [0, 2],
                'data' => $data,
                'startPath' => 'element2',
                'internalPath' => null,
                'result' => ['element2/children/0', 'element2/children/2']
            ],
            1 => [
                'indexes' => 0,
                'data' => $data,
                'startPath' => ['', '0'],
                'internalPath' => '0',
                'result' => ['/0/0', '/0/0/0/0']
            ],
            2 => [
                'indexes' => 0,
                'data' => $data,
                'startPath' => '',
                'internalPath' => ['0', '0'],
                'result' => ['/0', '/0/0/0/0']
            ],
            3 => [
                'indexes' => 'data',
                'data' => $data,
                'startPath' => 'element1/children',
                'internalPath' => 'config/argument',
                'result' => ['element1/children/element12/config/argument/data']
            ],
            4 => [
                'indexes' => 1,
                'data' => $data,
                'startPath' => null,
                'internalPath' => 'elements',
                'result' => []
            ]
        ];
    }

    /**
     * @param array|mixed $indexes
     * @param array $data
     * @param string|array|null $startPath
     * @param string|array|null $internalPath
     * @param array $result
     * @dataProvider findPathDataProvider
     */
    public function testFindPath($indexes, array $data, $startPath, $internalPath, $result)
    {
        $this->assertSame($result, $this->arrayManager->findPath($indexes, $data, $startPath, $internalPath));
    }

    /**
     * @return array
     */
    public function findPathDataProvider()
    {
        $data = [
            'element1' => [
                'children' => [
                    'element11' => [
                        'children' => [true, true]
                    ],
                    'element12' => [
                        'config' => [
                            'argument' => [
                                'data' => true
                            ]
                        ]
                    ]
                ]
            ],
            'element2' => [
                'children' => [true, true, true]
            ],
            '' => [
                [[[[]]]]
            ]
        ];

        return [
            0 => [
                'indexes' => [0, 2],
                'data' => $data,
                'startPath' => 'element2',
                'internalPath' => null,
                'result' => 'element2/children/0'
            ],
            1 => [
                'indexes' => 0,
                'data' => $data,
                'startPath' => ['', '0'],
                'internalPath' => '0',
                'result' => '/0/0'
            ],
            2 => [
                'indexes' => 0,
                'data' => $data,
                'startPath' => '',
                'internalPath' => ['0', '0'],
                'result' => '/0'
            ],
            3 => [
                'indexes' => 'data',
                'data' => $data,
                'startPath' => 'element1/children',
                'internalPath' => 'config/argument',
                'result' => 'element1/children/element12/config/argument/data'
            ],
            4 => [
                'indexes' => 1,
                'data' => $data,
                'startPath' => null,
                'internalPath' => 'elements',
                'result' => null
            ]
        ];
    }

    /**
     * @param string $path
     * @param int $offset
     * @param int|null $length
     * @param string $result
     * @dataProvider slicePathDataProvider
     */
    public function testSlicePath($path, $offset, $length, $result)
    {
        $this->assertSame($result, $this->arrayManager->slicePath($path, $offset, $length));
    }

    /**
     * @return array
     */
    public function slicePathDataProvider()
    {
        $path = 'some/very/very/long/path/0/goes/1/3/here';

        return [
            0 => [
                'path' => $path,
                'offset' => 3,
                'length' => null,
                'result' => 'long/path/0/goes/1/3/here'
            ],
            1 => [
                'path' => $path,
                'offset' => -3,
                'length' => null,
                'result' => '1/3/here'
            ],
            2 => [
                'path' => $path,
                'offset' => 500,
                'length' => null,
                'result' => ''
            ],
            3 => [
                'path' => $path,
                'offset' => 2,
                'length' => 2,
                'result' => 'very/long'
            ],
            4 => [
                'path' => $path,
                'offset' => -6,
                'length' => 3,
                'result' => 'path/0/goes'
            ],
        ];
    }

    public function testSlicePathCustomDelimiter()
    {
        $path = 'my~custom~path';

        $this->assertSame('custom', $this->arrayManager->slicePath($path, 1, 1, '~'));
        $this->assertSame('', $this->arrayManager->slicePath($path, 1, 1));
    }
}
