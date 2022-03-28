<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Model;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\UiComponent\ArrayObjectFactory;
use Magento\Framework\View\Element\UiComponent\Config\Converter;
use Magento\Framework\View\Element\UiComponent\Config\DomMergerInterface;
use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollector;
use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollectorFactory;
use Magento\Framework\View\Element\UiComponent\Config\ManagerInterface;
use Magento\Framework\View\Element\UiComponent\Config\Provider\Component\Definition as ComponentDefinition;
use Magento\Framework\View\Element\UiComponent\Config\ReaderFactory;
use Magento\Framework\View\Element\UiComponent\Config\UiReaderInterface;
use Magento\Ui\Model\Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ManagerTest extends TestCase
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var ComponentDefinition|MockObject
     */
    protected $componentConfigProvider;

    /**
     * @var CacheInterface|MockObject
     */
    protected $cacheConfig;

    /**
     * @var InterpreterInterface|MockObject
     */
    protected $argumentInterpreter;

    /**
     * @var UiReaderInterface|MockObject
     */
    protected $uiReader;

    /**
     * @var ReaderFactory|MockObject
     */
    protected $readerFactory;

    /**
     * @var AggregatedFileCollector|MockObject
     */
    protected $aggregatedFileCollector;

    /**
     * @var DomMergerInterface|MockObject
     */
    protected $domMerger;

    /**
     * @var ArrayObjectFactory|MockObject
     */
    protected $arrayObjectFactory;

    /**
     * @var AggregatedFileCollectorFactory|MockObject
     */
    protected $aggregatedFileCollectorFactory;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->componentConfigProvider = $this->getMockBuilder(
            \Magento\Framework\View\Element\UiComponent\Config\Provider\Component\Definition::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->domMerger = $this->getMockBuilder(
            DomMergerInterface::class
        )->getMockForAbstractClass();
        $this->aggregatedFileCollector = $this->getMockBuilder(
            AggregatedFileCollector::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->aggregatedFileCollectorFactory = $this->getMockBuilder(
            AggregatedFileCollectorFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->arrayObjectFactory = $this->getMockBuilder(
            ArrayObjectFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->arrayObjectFactory
            ->method('create')
            ->willReturn(new \ArrayObject([]));
        $this->uiReader = $this->getMockBuilder(
            UiReaderInterface::class
        )->getMockForAbstractClass();
        $this->readerFactory = $this->getMockBuilder(
            ReaderFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->cacheConfig = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();
        $this->argumentInterpreter = $this->getMockBuilder(InterpreterInterface::class)
            ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(
            SerializerInterface::class
        )->getMockForAbstractClass();
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->manager = new Manager(
            $this->componentConfigProvider,
            $this->domMerger,
            $this->readerFactory,
            $this->arrayObjectFactory,
            $this->aggregatedFileCollectorFactory,
            $this->cacheConfig,
            $this->argumentInterpreter,
            $this->serializer
        );
    }

    /**
     * @return void
     */
    public function testGetReader(): void
    {
        $this->readerFactory->expects($this->once())
            ->method('create')
            ->with(['fileCollector' => $this->aggregatedFileCollector, 'domMerger' => $this->domMerger])
            ->willReturn($this->uiReader);
        $this->aggregatedFileCollectorFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->aggregatedFileCollector);
        $this->assertEquals($this->uiReader, $this->manager->getReader('some_name'));
    }

    /**
     * @return void
     */
    public function testPrepareDataWithoutName(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            (string)__('The "" UI component element name is invalid. Verify the name and try again.')
        );
        $this->manager->prepareData(null);
    }

    /**
     * @return void
     * @dataProvider getComponentData()
     */
    public function testPrepareGetData($componentName, $componentData, $isCached, $readerData, $expectedResult): void
    {
        $this->arrayObjectFactory = $this->getMockBuilder(ArrayObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayObjectFactory
            ->method('create')
            ->willReturnOnConsecutiveCalls(new \ArrayObject([]), $componentData);

        $this->manager = new Manager(
            $this->componentConfigProvider,
            $this->domMerger,
            $this->readerFactory,
            $this->arrayObjectFactory,
            $this->aggregatedFileCollectorFactory,
            $this->cacheConfig,
            $this->argumentInterpreter,
            $this->serializer
        );

        $this->readerFactory->expects($this->any())
            ->method('create')
            ->with(['fileCollector' => $this->aggregatedFileCollector, 'domMerger' => $this->domMerger])
            ->willReturn($this->uiReader);
        $this->aggregatedFileCollectorFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->aggregatedFileCollector);
        $this->argumentInterpreter->expects($this->any())
            ->method('evaluate')
            ->willReturnCallback(function ($argument) {
                return ['argument' => $argument['value']];
            });
        $this->cacheConfig->expects($this->any())
            ->method('load')
            ->with(Manager::CACHE_ID . '_' . $componentName)
            ->willReturn($isCached);

        $this->uiReader->expects($this->any())
            ->method('read')
            ->willReturn($readerData);
        $this->assertEquals(
            $expectedResult,
            $this->manager->prepareData($componentName)->getData($componentName)
        );
    }

    /**
     * @return array
     */
    public function getComponentData(): array
    {
        $cachedData = new \ArrayObject(
            [
                'test_component1' => [
                    ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name1' => ['value' => 'value1']],
                    ManagerInterface::CHILDREN_KEY => [
                        'custom' => [
                            ManagerInterface::COMPONENT_ARGUMENTS_KEY => [
                                'custom_name1' => ['value' => 'custom_value1']
                            ],
                            ManagerInterface::CHILDREN_KEY => []
                        ]
                    ]
                ]
            ]
        );

        return [
            [
                'test_component1',
                new \ArrayObject(),
                json_encode($cachedData->getArrayCopy()),
                [],
                [
                    'test_component1' => [
                        ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name1' => ['argument' => 'value1']],
                        ManagerInterface::CHILDREN_KEY => [
                            'custom' => [
                                ManagerInterface::COMPONENT_ARGUMENTS_KEY => [
                                    'custom_name1' => ['argument' => 'custom_value1']
                                ],
                                ManagerInterface::CHILDREN_KEY => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                'test_component2',
                new \ArrayObject(
                    [
                        'test_component2' => [
                            ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name2' => ['value' => 'value2']],
                            ManagerInterface::CHILDREN_KEY => [
                                'test_component21' => [
                                    ManagerInterface::COMPONENT_ARGUMENTS_KEY => [
                                        'argument_name21' => ['value' => 'value21']
                                    ],
                                    ManagerInterface::CHILDREN_KEY => []
                                ]
                            ]
                        ]
                    ]
                ),
                false,
                [
                    'componentGroup' => [
                        0 => [
                            Converter::DATA_ARGUMENTS_KEY => ['argument_name2' => ['value' => 'value2']],
                            Converter::DATA_ATTRIBUTES_KEY => ['name' => 'attribute_name2'],
                            'test_component21' => [
                                0 => [
                                    Converter::DATA_ARGUMENTS_KEY => ['argument_name21' => ['value' => 'value21']],
                                    Converter::DATA_ATTRIBUTES_KEY => ['name' => 'attribute_name21']
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'test_component2' => [
                        ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name2' => ['argument' => 'value2']],
                        ManagerInterface::COMPONENT_ATTRIBUTES_KEY => ['name' => 'attribute_name2'],
                        ManagerInterface::CHILDREN_KEY => [
                            'attribute_name21' => [
                                ManagerInterface::COMPONENT_ARGUMENTS_KEY => [
                                    'argument_name21' => ['argument' => 'value21']
                                ],
                                ManagerInterface::COMPONENT_ATTRIBUTES_KEY => ['name' => 'attribute_name21'],
                                ManagerInterface::CHILDREN_KEY => []
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return void
     * @dataProvider getComponentDataProvider()
     */
    public function testCreateRawComponentData($componentName, $configData, $componentData, $needEvaluate): void
    {
        $this->componentConfigProvider->expects($this->any())
            ->method('getComponentData')
            ->willReturn($configData);
        if ($needEvaluate === true) {
            $this->argumentInterpreter->expects($this->once())
                ->method('evaluate')
                ->willReturnCallback(function ($argument) {
                    return ['argument' => $argument['value']];
                });
        } else {
            $this->argumentInterpreter->expects($this->never())->method('evaluate');
        }
        $this->assertEquals($componentData, $this->manager->createRawComponentData($componentName, $needEvaluate));
    }

    /**
     * @return array
     */
    public function getComponentDataProvider(): array
    {
        return [
            [
                'test_component1',
                [
                    Converter::DATA_ATTRIBUTES_KEY => ['name' => 'attribute_name1']
                ],
                [
                    ManagerInterface::COMPONENT_ATTRIBUTES_KEY => ['name' => 'attribute_name1'],
                    ManagerInterface::COMPONENT_ARGUMENTS_KEY => []

                ],
                false,
            ],
            [
                'test_component2',
                [
                    Converter::DATA_ARGUMENTS_KEY => ['argument_name2' => ['value' => 'value2']]
                ],
                [
                    ManagerInterface::COMPONENT_ATTRIBUTES_KEY => [],
                    ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name2' => ['value' => 'value2']]

                ],
                false
            ],
            [
                'test_component3',
                [
                    Converter::DATA_ATTRIBUTES_KEY => ['name' => 'attribute_name3'],
                    Converter::DATA_ARGUMENTS_KEY => ['argument_name3' => ['value' => 'value3']]
                ],
                [
                    ManagerInterface::COMPONENT_ATTRIBUTES_KEY => ['name' => 'attribute_name3'],
                    ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name3' => ['argument' => 'value3']]

                ],
                true
            ]
        ];
    }
}
