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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

class DumpConfigSourceAggregatedTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp(): void
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
                        'unsecure' => ['without_type' => 'some_value'],
                        'secure' => ['environment_type' => 'some_environment_value'],
                        'some_key' => [
                            'without_type' => 'some_value',
                            'sensitive_type' => 'some_sensitive_value'
                        ],
                    ]
                ],
                'test' => [
                    'test' => [
                        'test1' => [
                            'test2' => ['without_type' => 5]
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
                        'another_key' => ['sensitive_type' => 'some_sensitive_value']
                    ]
                ]
            ]);

        $this->typePoolMock->expects($this->any())
            ->method('isPresent')
            ->willReturnMap([
                ['web/unsecure/without_type', TypePool::TYPE_SENSITIVE, false],
                ['web/secure/environment_type', TypePool::TYPE_ENVIRONMENT, true],
                ['test1/test2/test/without_type', TypePool::TYPE_SENSITIVE, false],
                ['web/some_key/without_type', TypePool::TYPE_ENVIRONMENT, false],
                ['web/some_key/sensitive_type', TypePool::TYPE_SENSITIVE, true],
                ['web/another_key/sensitive_type', TypePool::TYPE_SENSITIVE, true],
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
                            'test2' => ['without_type' => 5]
                        ]
                    ],
                ],
                'default' => [
                    'web' => [
                        'unsecure' => [
                            'without_type' => 'some_value',
                        ],
                        'some_key' => [
                            'without_type' => 'some_value',
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
                        'secure' => ['environment_type' => 'some_environment_value'],
                        'some_key' => [
                            'sensitive_type' => 'some_sensitive_value'
                        ],
                        'another_key' => ['sensitive_type' => 'some_sensitive_value']
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
                'web/secure/environment_type',
                'web/some_key/sensitive_type',
                'web/another_key/sensitive_type'
            ],
            $this->model->getExcludedFields()
        );
    }
}
