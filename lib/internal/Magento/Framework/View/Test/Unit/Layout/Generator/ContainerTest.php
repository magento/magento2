<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout\Generator;

use \Magento\Framework\View\Layout\Generator\Container;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Layout\Reader\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $readerContextMock;

    /**
     * @var Layout\Generator\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $generatorContextMock;

    /**
     * @var Container|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $container;

    /**
     * @var ScheduledStructure|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scheduledStructureMock;

    /**
     * @var \Magento\Framework\View\Layout\Data\Structure|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $structureMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->scheduledStructureMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ScheduledStructure::class)
            ->disableOriginalConstructor()->getMock();

        $this->structureMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Data\Structure::class)
            ->disableOriginalConstructor()->getMock();

        $this->generatorContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Generator\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->generatorContextMock->expects($this->any())
            ->method('getStructure')
            ->willReturn($this->structureMock);

        $this->readerContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->readerContextMock->expects($this->any())
            ->method('getScheduledStructure')
            ->willReturn($this->scheduledStructureMock);

        $this->container = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Layout\Generator\Container::class
        );
    }

    /**
     * @param array $structureElements
     * @param array $setAttributeData
     * @param int $setAttributeCalls
     *
     * @dataProvider processDataProvider
     */
    public function testProcess($structureElements, $setAttributeData, $setAttributeCalls)
    {
        $this->scheduledStructureMock->expects($this->once())
            ->method('getElements')
            ->willReturn($structureElements);

        $this->structureMock->expects($this->exactly($setAttributeCalls))
            ->method('setAttribute')
            ->willReturnMap($setAttributeData);

        $this->container->process($this->readerContextMock, $this->generatorContextMock);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'sample_data' => [
                'structureElements' => [
                    'first_container' => [
                        'container',
                        [
                            'attributes' => [
                                Layout\Element::CONTAINER_OPT_LABEL => 'dd_label',
                                Container::CONTAINER_OPT_HTML_TAG   => 'dd',
                                Container::CONTAINER_OPT_HTML_CLASS => 'dd_class',
                                Container::CONTAINER_OPT_HTML_ID    => 'dd_id',
                            ]
                        ],
                    ],
                ],
                'setAttributeData' => [
                    ['first_container', Layout\Element::CONTAINER_OPT_LABEL, 'dd_label'],
                    ['first_container', Container::CONTAINER_OPT_HTML_TAG, 'dd'],
                    ['first_container', Container::CONTAINER_OPT_HTML_CLASS, 'dd_class'],
                    ['first_container', Container::CONTAINER_OPT_HTML_ID, 'dd_id'],
                ],
                'setAttributeCalls' => 4,
            ],
            'sample_data2' => [
                'structureElements' => [
                    'first_container' => [
                        'container',
                        [
                            'attributes' => [
                                Container::CONTAINER_OPT_HTML_TAG   => 'dd',
                                Container::CONTAINER_OPT_HTML_CLASS => 'dd_class',
                                Container::CONTAINER_OPT_HTML_ID    => 'dd_id',
                            ]
                        ],
                    ],
                ],
                'setAttributeData' => [
                    ['first_container', Container::CONTAINER_OPT_HTML_TAG, 'dd'],
                    ['first_container', Container::CONTAINER_OPT_HTML_CLASS, 'dd_class'],
                    ['first_container', Container::CONTAINER_OPT_HTML_ID, 'dd_id'],
                ],
                'setAttributeCalls' => 3,
            ]
        ];
    }

    /**
     * @param array $structureElements
     *
     * @dataProvider processWithExceptionDataProvider
     */
    public function testProcessWithException($structureElements)
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->scheduledStructureMock->expects($this->once())
            ->method('getElements')
            ->willReturn($structureElements);

        $this->structureMock->expects($this->never())
            ->method('setAttribute')
            ->willReturnSelf();

        $this->container->process($this->readerContextMock, $this->generatorContextMock);
    }

    /**
     * @return array
     */
    public function processWithExceptionDataProvider()
    {
        return [
            'wrong_html_tag' => [
                'structureElements' => [
                    'first_container' => [
                        'container',
                        [
                            'attributes' => [
                                Container::CONTAINER_OPT_LABEL   => 'label',
                                Layout\Element::CONTAINER_OPT_HTML_TAG => 'custom_tag',
                            ]
                        ],
                    ],
                ],
            ],
            'html_id_without_tag' => [
                'structureElements' => [
                    'second_container' => [
                        'container',
                        [
                            'attributes' => [
                                Container::CONTAINER_OPT_LABEL   => 'label',
                                Layout\Element::CONTAINER_OPT_HTML_ID => 'html_id',
                            ]
                        ],
                    ],
                ],
            ],
            'html_class_without_tag' => [
                'structureElements' => [
                    'third_container' => [
                        'container',
                        [
                            'attributes' => [
                                Container::CONTAINER_OPT_LABEL   => 'label',
                                Layout\Element::CONTAINER_OPT_HTML_CLASS => 'html_class',
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }
}
