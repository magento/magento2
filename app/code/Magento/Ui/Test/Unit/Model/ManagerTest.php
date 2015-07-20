<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
/**
 * Class ManagerTest
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

    public function setUp()
    {
        $this->componentConfigProvider = $this->getMockBuilder(
            'Magento\Framework\View\Element\UiComponent\Config\Provider\Component\Definition'
        )->disableOriginalConstructor()->getMock();
        $this->domMerger = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Config\DomMergerInterface')
            ->getMockForAbstractClass();
        $this->aggregatedFileCollector = $this->getMockBuilder(
            'Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollector'
            )->disableOriginalConstructor()->getMock();
        $this->aggregatedFileCollectorFactory = $this->getMockBuilder(
            'Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollectorFactory'
            )->disableOriginalConstructor()->getMock();
        $this->arrayObjectFactory = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ArrayObjectFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayObjectFactory->expects($this->at(0))
            ->method('create')
            ->willReturn(new \ArrayObject([]));
        $this->uiReader = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Config\UiReaderInterface')
            ->getMockForAbstractClass();
        $this->readerFactory = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Config\ReaderFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheConfig = $this->getMockBuilder('Magento\Framework\Config\CacheInterface')
            ->getMockForAbstractClass();
        $this->argumentInterpreter = $this->getMockBuilder('Magento\Framework\Data\Argument\InterpreterInterface')
            ->getMockForAbstractClass();
        $this->manager = new Manager(
            $this->componentConfigProvider,
            $this->domMerger,
            $this->readerFactory,
            $this->arrayObjectFactory,
            $this->aggregatedFileCollectorFactory,
            $this->cacheConfig,
            $this->argumentInterpreter
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

    public function testPrepareDataWithException()
    {
        $this->setExpectedException(
            'Magento\Framework\Exception\LocalizedException',
            __('Initialization error component, check the spelling of the name or the correctness of the call.')
        );
        $this->manager->prepareData(null);
    }

    /**
     * @dataProvider getComponentData()
     */
    public function testPrepareGetData($componentName, $componentData, $isCached)
    {
        $this->argumentInterpreter->expects($this->any())
            ->method('evaluate')
            ->willReturnCallback(function($argument) {
                return ['argument' => $argument['value']];
            });
        $this->arrayObjectFactory->expects($this->any())
            ->method('create')
            ->willReturn($componentData);
        $this->manager->prepareData($componentName)->getData($componentName);

    }

    public function getComponentData()
    {
        return [
            [
                'test_component1',
                new \ArrayObject(
                    ['test_component1' =>
                        [
                            ManagerInterface::COMPONENT_ARGUMENTS_KEY =>
                                [
                                    'argument_name1' => ['value' => 'value1'],
                                ],
                            'children' => [],
                        ]
                    ]
                ),
                true
            ],
            [
                'test_component2',
                new \ArrayObject(
                    ['test_component2' =>
                        [
                            ManagerInterface::COMPONENT_ARGUMENTS_KEY =>
                                [
                                    'argument_name2' => ['value' => 'value2'],
                                ],
                            'children' => [
                                ManagerInterface::COMPONENT_ARGUMENTS_KEY =>
                                    [
                                        'argument_name21' => ['value' => 'value21'],
                                    ],

                            ],
                        ]
                    ]
                ),
                false
            ],
        ];
    }
}
