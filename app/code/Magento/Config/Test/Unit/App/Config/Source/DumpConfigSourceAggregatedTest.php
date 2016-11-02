<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\DumpConfigSourceAggregated;
use Magento\Config\Model\Config\Export\ExcludeList;
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

    public function setUp()
    {
        $this->sourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->sourceMockTwo = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->excludeListMock = $this->getMockBuilder(ExcludeList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sources = [
            [
                'source' => $this->sourceMockTwo,
                'sortOrder' => 100
            ],
            [
                'source' => $this->sourceMock,
                'sortOrder' => 10
            ],

        ];

        $this->model = new DumpConfigSourceAggregated($this->excludeListMock, $sources);
    }

    public function testGet()
    {
        $path = '';
        $data = [
            'default' => [
                'web' => [
                    'unsecure' => [
                        'base_url' => 'http://test.local',
                    ],
                    'secure' => [
                        'base_url' => 'https://test.local',
                    ]
                ]
            ],
            'test' => [
                'test' => [
                    'test1' => [
                        'test2' => [
                            'test3' => 5,
                        ]
                    ]
                ]
            ]
        ];

        $this->sourceMock->expects($this->once())
            ->method('get')
            ->with($path)
            ->willReturn($data);
        $this->sourceMockTwo->expects($this->once())
            ->method('get')
            ->with($path)
            ->willReturn(['key' => 'value2']);
        $this->excludeListMock->expects($this->any())
            ->method('isPresent')
            ->willReturnMap([
                ['web/unsecure/base_url', false],
                ['web/secure/base_url', true],
                ['test1/test2/test/3', false]
            ]);

        $this->assertEquals(
            [
                'test' => [
                    'test' => [
                        'test1' => [
                            'test2' => [
                                'test3' => 5,
                            ]
                        ]
                    ],
                ],
                'key' => 'value2',
                'default' => [
                    'web' => [
                        'unsecure' => [
                            'base_url' => 'http://test.local',
                        ],
                        'secure' => []
                    ]
                ],
            ],
            $this->model->get($path)
        );
    }

    public function testGetExcludedFields()
    {
        $path = '';
        $data = [
            'default' => [
                'web' => [
                    'unsecure' => [
                        'base_url' => 'http://test.local',
                    ],
                    'secure' => [
                        'base_url' => 'https://test.local',
                    ]
                ]
            ],
            'test' => [
                'test' => [
                    'test1' => [
                        'test2' => [
                            'test3' => 5,
                        ]
                    ]
                ]
            ]
        ];

        $this->sourceMock->expects($this->once())
            ->method('get')
            ->with($path)
            ->willReturn($data);
        $this->sourceMockTwo->expects($this->once())
            ->method('get')
            ->with($path)
            ->willReturn(['key' => 'value2']);
        $this->excludeListMock->expects($this->any())
            ->method('isPresent')
            ->willReturnMap([
                ['web/unsecure/base_url', false],
                ['web/secure/base_url', true],
                ['test1/test2/test/3', false]
            ]);

        $this->assertEquals(
            ['web/secure/base_url'],
            $this->model->getExcludedFields()
        );
    }
}
