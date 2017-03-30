<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\DumpConfigSourceAggregated;
use Magento\Config\Model\Config\TypePool;
use Magento\Config\Model\Config\Export\ExcludeList;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class DumpConfigSourceAggregatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigSourceInterface|MockObject
     */
    private $sourceMock;

    /**
     * @var ConfigSourceInterface|MockObject
     */
    private $sourceTwoMock;

    /**
     * @var ExcludeList|MockObject
     */
    private $excludeListMock;

    /**
     * @var TypePool|MockObject
     */
    private $typePoolMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var DumpConfigSourceAggregated
     */
    private $model;

    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->sourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->sourceTwoMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->excludeListMock = $this->getMockBuilder(ExcludeList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typePoolMock = $this->getMockBuilder(TypePool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sourceMock->expects($this->once())
            ->method('get')
            ->with('')
            ->willReturn([
                'default' => [
                    'web' => [
                        'unsecure' => ['base_url' => 'http://test.local'],
                        'secure' => ['base_url' => 'https://test.local'],
                        'some_key1' => [
                            'some_key11' => 'someValue11',
                            'some_key12' => 'someValue12'
                        ],
                    ]
                ],
                'test' => [
                    'test' => [
                        'test1' => [
                            'test2' => ['test3' => 5]
                        ]
                    ]
                ]
            ]);

        $this->sourceTwoMock->expects($this->once())
            ->method('get')
            ->with('')
            ->willReturn([
                'default' => [
                    'web' => [
                        'another_key' => ['key1' => 'value']
                    ]
                ]
            ]);

        $this->typePoolMock->expects($this->any())
            ->method('isPresent')
            ->willReturnMap([
                ['web/unsecure/base_url', TypePool::TYPE_SENSITIVE, false],
                ['web/secure/base_url', TypePool::TYPE_ENVIRONMENT, true],
                ['test1/test2/test/3', TypePool::TYPE_SENSITIVE, false],
                ['web/some_key1/some_key11', TypePool::TYPE_ENVIRONMENT, false],
                ['web/some_key1/some_key12', TypePool::TYPE_SENSITIVE, true],
                ['web/another_key/key1', TypePool::TYPE_SENSITIVE, true],
            ]);

        $this->model = new DumpConfigSourceAggregated(
            $this->excludeListMock,
            [
                [
                    'source' => $this->sourceTwoMock,
                    'sortOrder' => 100
                ],
                [
                    'source' => $this->sourceMock,
                    'sortOrder' => 10
                ],

            ],
            $this->typePoolMock,
            [
                'default' => 'include',
                'sensitive' => 'exclude',
                'environment' => 'exclude',
            ]
        );
    }

    public function testGet()
    {
        $this->assertEquals(
            [
                'test' => [
                    'test' => [
                        'test1' => [
                            'test2' => ['test3' => 5]
                        ]
                    ],
                ],
                'default' => [
                    'web' => [
                        'unsecure' => [
                            'base_url' => 'http://test.local',
                        ],
                        'some_key1' => [
                            'some_key11' => 'someValue11',
                        ],
                    ]
                ],
            ],
            $this->model->get('')
        );
    }

    public function testGetWithExcludeDefault()
    {
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->model,
            'rules',
            [
                'default' => 'exclude',
                'sensitive' => 'include',
                'environment' => 'include',
            ]
        );

        $this->assertEquals(
            [
                'default' => [
                    'web' => [
                        'secure' => ['base_url' => 'https://test.local'],
                        'some_key1' => [
                            'some_key12' => 'someValue12'
                        ],
                        'another_key' => ['key1' => 'value']
                    ]
                ],
            ],
            $this->model->get('')
        );
    }

    public function testGetExcludedFields()
    {
        $this->assertEquals(
            [
                'web/secure/base_url',
                'web/some_key1/some_key12',
                'web/another_key/key1'
            ],
            $this->model->getExcludedFields()
        );
    }
}
