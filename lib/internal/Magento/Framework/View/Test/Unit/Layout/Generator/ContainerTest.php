<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Generator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Generator\Container;
use Magento\Framework\View\Layout\Generator\Context;

use Magento\Framework\View\Layout\ScheduledStructure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Layout\Reader\Context|MockObject
     */
    protected $readerContextMock;

    /**
     * @var Layout\Generator\Context|MockObject
     */
    protected $generatorContextMock;

    /**
     * @var Container|MockObject
     */
    protected $container;

    /**
     * @var ScheduledStructure|MockObject
     */
    protected $scheduledStructureMock;

    /**
     * @var Structure|MockObject
     */
    protected $structureMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->scheduledStructureMock = $this->getMockBuilder(ScheduledStructure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->generatorContextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->generatorContextMock->expects($this->any())
            ->method('getStructure')
            ->willReturn($this->structureMock);

        $this->readerContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerContextMock->expects($this->any())
            ->method('getScheduledStructure')
            ->willReturn($this->scheduledStructureMock);

        $this->container = $this->objectManagerHelper->getObject(
            Container::class
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
                                Element::CONTAINER_OPT_LABEL => 'dd_label',
                                Container::CONTAINER_OPT_HTML_TAG   => 'dd',
                                Container::CONTAINER_OPT_HTML_CLASS => 'dd_class',
                                Container::CONTAINER_OPT_HTML_ID    => 'dd_id',
                            ]
                        ],
                    ],
                ],
                'setAttributeData' => [
                    ['first_container', Element::CONTAINER_OPT_LABEL, 'dd_label'],
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
            ],
            'Article as allowed container tag' => [
                'structureElements' => [
                    'first_container' => [
                        'container',
                        [
                            'attributes' => [
                                Container::CONTAINER_OPT_HTML_TAG   => 'article',
                                Container::CONTAINER_OPT_HTML_CLASS => 'article_class',
                                Container::CONTAINER_OPT_HTML_ID    => 'article_id',
                            ]
                        ],
                    ],
                ],
                'setAttributeData' => [
                    ['first_container', Container::CONTAINER_OPT_HTML_TAG, 'article'],
                    ['first_container', Container::CONTAINER_OPT_HTML_CLASS, 'article_class'],
                    ['first_container', Container::CONTAINER_OPT_HTML_ID, 'article_id'],
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
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
                                Element::CONTAINER_OPT_HTML_TAG => 'custom_tag',
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
                                Element::CONTAINER_OPT_HTML_ID => 'html_id',
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
                                Element::CONTAINER_OPT_HTML_CLASS => 'html_class',
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }
}
