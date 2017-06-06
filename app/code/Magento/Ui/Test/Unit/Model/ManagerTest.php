<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Unit\Model;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\View\Element\UiComponent\ArrayObjectFactory;
use Magento\Framework\View\Element\UiComponent\Config\DomMergerInterface;
use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollector;
use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollectorFactory;
use Magento\Framework\View\Element\UiComponent\Config\UiReaderInterface;
use Magento\Ui\Model\Manager;
use Magento\Framework\View\Element\UiComponent\Config\Provider\Component\Definition as ComponentDefinition;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Element\UiComponent\Config\ManagerInterface;
use Magento\Framework\View\Element\UiComponent\Config\Converter;

/**
 * Class ManagerTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var ComponentDefinition|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $componentConfigProvider;

    /**
     * @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheConfig;

    /**
     * @var InterpreterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $argumentInterpreter;

    /**
     * @var UiReaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiReader;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\Config\ReaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerFactory;

    /**
     * @var AggregatedFileCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aggregatedFileCollector;

    /**
     * @var DomMergerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $domMerger;

    /**
     * @var ArrayObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $arrayObjectFactory;

    /**
     * @var AggregatedFileCollectorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aggregatedFileCollectorFactory;

    /** @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $serializer;

    protected function setUp()
    {
        $this->componentConfigProvider = $this->getMockBuilder(
            \Magento\Framework\View\Element\UiComponent\Config\Provider\Component\Definition::class
        )->disableOriginalConstructor()->getMock();
        $this->domMerger = $this->getMockBuilder(
            \Magento\Framework\View\Element\UiComponent\Config\DomMergerInterface::class
        )->getMockForAbstractClass();
        $this->aggregatedFileCollector = $this->getMockBuilder(
            \Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollector::class
        )->disableOriginalConstructor()->getMock();
        $this->aggregatedFileCollectorFactory = $this->getMockBuilder(
            \Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollectorFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->arrayObjectFactory = $this->getMockBuilder(
            \Magento\Framework\View\Element\UiComponent\ArrayObjectFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->arrayObjectFactory->expects($this->at(0))
            ->method('create')
            ->willReturn(new \ArrayObject([]));
        $this->uiReader = $this->getMockBuilder(
            \Magento\Framework\View\Element\UiComponent\Config\UiReaderInterface::class
        )->getMockForAbstractClass();
        $this->readerFactory = $this->getMockBuilder(
            \Magento\Framework\View\Element\UiComponent\Config\ReaderFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->cacheConfig = $this->getMockBuilder(\Magento\Framework\Config\CacheInterface::class)
            ->getMockForAbstractClass();
        $this->argumentInterpreter = $this->getMockBuilder(\Magento\Framework\Data\Argument\InterpreterInterface::class)
            ->getMockForAbstractClass();
        $this->serializer = $this->getMockBuilder(
            \Magento\Framework\Serialize\SerializerInterface::class
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

    public function testGetReader()
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

    public function testPrepareDataWithoutName()
    {
        $this->setExpectedException(
            \Magento\Framework\Exception\LocalizedException::class,
            __("Invalid UI Component element name: ''")
        );
        $this->manager->prepareData(null);
    }

    /**
     * @dataProvider getComponentData()
     */
    public function testPrepareGetData($componentName, $componentData, $isCached, $readerData, $expectedResult)
    {
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
        $this->arrayObjectFactory->expects($this->any())
            ->method('create')
            ->willReturn($componentData);
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

    public function getComponentData()
    {
        $cachedData = new \ArrayObject(
            ['test_component1' =>
                [
                    ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name1' => ['value' => 'value1']],
                    ManagerInterface::CHILDREN_KEY => [
                        'custom' => [
                            ManagerInterface::COMPONENT_ARGUMENTS_KEY =>
                                ['custom_name1' => ['value' => 'custom_value1']],
                            ManagerInterface::CHILDREN_KEY => [],
                        ],
                    ],
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
                                ManagerInterface::COMPONENT_ARGUMENTS_KEY =>
                                    ['custom_name1' => ['argument' => 'custom_value1']],
                                ManagerInterface::CHILDREN_KEY => [],
                            ]
                        ]
                    ],
                ],
            ],
            [
                'test_component2',
                new \ArrayObject(
                    ['test_component2' =>
                        [
                            ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name2' => ['value' => 'value2']],
                            ManagerInterface::CHILDREN_KEY => [
                                'test_component21' => [
                                    ManagerInterface::COMPONENT_ARGUMENTS_KEY =>
                                        ['argument_name21' => ['value' => 'value21']],
                                    ManagerInterface::CHILDREN_KEY => [],
                                ],
                            ],
                        ]
                    ]
                ),
                false,
                ['componentGroup' => [0 => [
                    Converter::DATA_ARGUMENTS_KEY => ['argument_name2' => ['value' => 'value2']],
                    Converter::DATA_ATTRIBUTES_KEY => ['name' => 'attribute_name2'],
                    'test_component21' => [0 => [
                            Converter::DATA_ARGUMENTS_KEY => ['argument_name21' => ['value' => 'value21']],
                            Converter::DATA_ATTRIBUTES_KEY => ['name' => 'attribute_name21'],
                        ]
                    ],
                ]]],
                [
                    'test_component2' => [
                        ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name2' => ['argument' => 'value2']],
                        ManagerInterface::COMPONENT_ATTRIBUTES_KEY => ['name' => 'attribute_name2'],
                        ManagerInterface::CHILDREN_KEY => [
                            'attribute_name21' => [
                                ManagerInterface::COMPONENT_ARGUMENTS_KEY =>
                                    ['argument_name21' => ['argument' => 'value21']],
                                ManagerInterface::COMPONENT_ATTRIBUTES_KEY => ['name' => 'attribute_name21'],
                                ManagerInterface::CHILDREN_KEY => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getComponentDataProvider()
     */
    public function testCreateRawComponentData($componentName, $configData, $componentData, $needEvaluate)
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

    public function getComponentDataProvider()
    {
        return [
            [
                'test_component1',
                [
                    Converter::DATA_ATTRIBUTES_KEY => ['name' => 'attribute_name1'],
                ],
                [
                    ManagerInterface::COMPONENT_ATTRIBUTES_KEY => ['name' => 'attribute_name1'],
                    ManagerInterface::COMPONENT_ARGUMENTS_KEY => [],

                ],
                false,
            ],
            [
                'test_component2',
                [
                    Converter::DATA_ARGUMENTS_KEY => ['argument_name2' => ['value' => 'value2']],
                ],
                [
                    ManagerInterface::COMPONENT_ATTRIBUTES_KEY => [],
                    ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name2' => ['value' => 'value2']],

                ],
                false,
            ],
            [
                'test_component3',
                [
                    Converter::DATA_ATTRIBUTES_KEY => ['name' => 'attribute_name3'],
                    Converter::DATA_ARGUMENTS_KEY => ['argument_name3' => ['value' => 'value3']],
                ],
                [
                    ManagerInterface::COMPONENT_ATTRIBUTES_KEY => ['name' => 'attribute_name3'],
                    ManagerInterface::COMPONENT_ARGUMENTS_KEY => ['argument_name3' => ['argument' => 'value3']],

                ],
                true,
            ],
        ];
    }
}
