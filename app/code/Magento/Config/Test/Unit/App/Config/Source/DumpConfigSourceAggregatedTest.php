<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\DumpConfigSourceAggregated;
use Magento\Config\Model\Config\Export\ExcludeList;
use Magento\Config\Model\Config\TypePool;
use Magento\Framework\App\Config\ConfigSourceInterface;

class DumpConfigSourceAggregatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceMock;

    /**
     * @var ConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceMockTwo;

    /**
     * @var DumpConfigSourceAggregated
     */
    private $model;

    /**
     * @var ExcludeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $excludeListMock;

    /**
     * @var TypePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typePool;

    public function setUp()
    {
        $this->sourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->sourceMockTwo = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->excludeListMock = $this->getMockBuilder(ExcludeList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typePool = $this->getMockBuilder(TypePool::class)
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

        $this->sourceMockTwo->expects($this->once())
            ->method('get')
            ->with('')
            ->willReturn(['key' => 'value2']);

        $this->excludeListMock->expects($this->any())
            ->method('isPresent')
            ->willReturnMap([
                ['web/unsecure/base_url', false],
                ['web/secure/base_url', true],
                ['test1/test2/test/3', false],
                ['web/some_key1/some_key11', true],
                ['web/some_key1/some_key12', false],
            ]);

        $this->typePool->expects($this->any())
            ->method('isPresent')
            ->willReturnMap([
                ['web/unsecure/base_url', TypePool::TYPE_SENSITIVE, false],
                ['web/secure/base_url', TypePool::TYPE_ENVIRONMENT, true],
                ['test1/test2/test/3', TypePool::TYPE_SENSITIVE, false],
                ['web/some_key1/some_key11', TypePool::TYPE_ENVIRONMENT, false],
                ['web/some_key1/some_key12', TypePool::TYPE_SENSITIVE, true],
            ]);

        $this->model = new DumpConfigSourceAggregated(
            $this->excludeListMock,
            [
                [
                    'source' => $this->sourceMockTwo,
                    'sortOrder' => 100
                ],
                [
                    'source' => $this->sourceMock,
                    'sortOrder' => 10
                ],

            ],
            $this->typePool
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
                'key' => 'value2',
                'default' => [
                    'web' => [
                        'unsecure' => [
                            'base_url' => 'http://test.local',
                        ],
                        'secure' => [],
                        'some_key1' => [],
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
                'web/some_key1/some_key11',
                'web/some_key1/some_key12'
            ],
            $this->model->getExcludedFields()
        );
    }
}
