<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use \Magento\Framework\View\Layout\Reader\Container;

use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Container|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\View\Layout\ReaderPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerPoolMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->helperMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ScheduledStructure\Helper::class)
            ->disableOriginalConstructor()->getMock();
        $this->readerPoolMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ReaderPool::class)
            ->disableOriginalConstructor()->getMock();

        $this->container = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Layout\Reader\Container::class,
            [
                'helper' => $this->helperMock,
                'readerPool' => $this->readerPoolMock
            ]
        );
    }

    /**
     * @param \Magento\Framework\View\Layout\Element $elementCurrent
     * @param string $containerName
     * @param array $structureElement
     * @param array $expectedData
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getStructureCondition
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setStructureCondition
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setRemoveCondition
     *
     * @dataProvider processDataProvider
     */
    public function testProcess(
        $elementCurrent,
        $containerName,
        $structureElement,
        $expectedData,
        $getStructureCondition,
        $setStructureCondition,
        $setRemoveCondition
    ) {
        /** @var ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject $scheduledStructureMock */
        $scheduledStructureMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ScheduledStructure::class)
            ->disableOriginalConstructor()->getMock();
        $scheduledStructureMock->expects($getStructureCondition)
            ->method('getStructureElementData')
            ->with($containerName)
            ->willReturn($structureElement);
        $scheduledStructureMock->expects($setStructureCondition)
            ->method('setStructureElementData')
            ->with($containerName, $expectedData)
            ->willReturnSelf();
        $scheduledStructureMock->expects($setRemoveCondition)
            ->method('setElementToRemoveList')
            ->with($containerName);

        /** @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject $contextMock */
        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->any())
            ->method('getScheduledStructure')
            ->willReturn($scheduledStructureMock);

        $this->helperMock
            ->method('scheduleStructure')
            ->with($scheduledStructureMock, $elementCurrent);

        $this->readerPoolMock->expects($this->once())
            ->method('interpret')
            ->with($contextMock, $elementCurrent)
            ->willReturnSelf();

        if ($elementCurrent->getAttribute('remove') == 'false') {
            $scheduledStructureMock->expects($this->once())
                ->method('unsetElementFromListToRemove')
                ->with($elementCurrent->getAttribute('name'));
        }
        
        $this->container->interpret($contextMock, $elementCurrent);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function processDataProvider()
    {
        return [
            'container' => [
                'elementCurrent' => $this->getElement(
                    '<container name="container" id="id_add" tag="body"/>',
                    'container'
                ),
                'containerName' => 'container',
                'structureElement' => [
                    'attributes' => [
                        'id' => 'id_value',
                        'tag' => 'tag_value',
                        'unchanged' => 'unchanged_value',
                    ],
                ],
                'expectedData' => [
                    'attributes' => [
                        'id' => 'id_add',
                        'tag' => 'body',
                        'unchanged' => 'unchanged_value',
                    ],
                ],
                'getStructureCondition' => $this->once(),
                'setStructureCondition' => $this->once(),
                'setRemoveCondition' => $this->never(),
            ],
            'referenceContainer' => [
                'elementCurrent' => $this->getElement(
                    '<referenceContainer name="reference" htmlTag="span" htmlId="id_add" htmlClass="new" label="Add"/>',
                    'referenceContainer'
                ),
                'containerName' => 'reference',
                'structureElement' => [],
                'expectedData' => [
                    'attributes' => [
                        Container::CONTAINER_OPT_HTML_TAG   => 'span',
                        Container::CONTAINER_OPT_HTML_ID    => 'id_add',
                        Container::CONTAINER_OPT_HTML_CLASS => 'new',
                        Container::CONTAINER_OPT_LABEL      => 'Add',
                        Container::CONTAINER_OPT_DISPLAY    => null,
                    ],
                ],
                'getStructureCondition' => $this->once(),
                'setStructureCondition' => $this->once(),
                'setRemoveCondition' => $this->never(),
            ],
            'referenceContainerNoRemove' => [
                'elementCurrent' => $this->getElement(
                    '<referenceContainer name="reference" remove="false"/>',
                    'referenceContainer'
                ),
                'containerName' => 'reference',
                'structureElement' => [],
                'expectedData' => [
                    'attributes' => [
                        Container::CONTAINER_OPT_HTML_TAG   => null,
                        Container::CONTAINER_OPT_HTML_ID    => null,
                        Container::CONTAINER_OPT_HTML_CLASS => null,
                        Container::CONTAINER_OPT_LABEL      => null,
                        Container::CONTAINER_OPT_DISPLAY    => null,
                    ],
                ],
                'getStructureCondition' => $this->once(),
                'setStructureCondition' => $this->once(),
                'setRemoveCondition' => $this->never(),
            ],
            'referenceContainerRemove' => [
                'elementCurrent' => $this->getElement(
                    '<referenceContainer name="reference" remove="1"/>',
                    'referenceContainer'
                ),
                'containerName' => 'reference',
                'structureElement' => [],
                'expectedData' => [],
                'getStructureCondition' => $this->never(),
                'setStructureCondition' => $this->never(),
                'setRemoveCondition' => $this->once(),
            ],
            'referenceContainerRemove2' => [
                'elementCurrent' => $this->getElement(
                    '<referenceContainer name="reference" remove="true"/>',
                    'referenceContainer'
                ),
                'containerName' => 'reference',
                'structureElement' => [],
                'expectedData' => [],
                'getStructureCondition' => $this->never(),
                'setStructureCondition' => $this->never(),
                'setRemoveCondition' => $this->once(),
            ],
            'referenceContainerDisplayFalse' => [
                'elementCurrent' => $this->getElement(
                    '<referenceContainer name="reference" htmlTag="span" htmlId="id_add" htmlClass="new" label="Add"'
                    . ' display="true"/>',
                    'referenceContainer'
                ),
                'containerName' => 'reference',
                'structureElement' => [],
                'expectedData' => [
                    'attributes' => [
                        Container::CONTAINER_OPT_HTML_TAG   => 'span',
                        Container::CONTAINER_OPT_HTML_ID    => 'id_add',
                        Container::CONTAINER_OPT_HTML_CLASS => 'new',
                        Container::CONTAINER_OPT_LABEL      => 'Add',
                        Container::CONTAINER_OPT_DISPLAY    => 'true',
                    ],
                ],
                'getStructureCondition' => $this->once(),
                'setStructureCondition' => $this->once(),
                'setRemoveCondition' => $this->never(),
            ]
        ];
    }

    /**
     * @param string $xml
     * @param string $elementType
     * @return \Magento\Framework\View\Layout\Element
     */
    protected function getElement($xml, $elementType)
    {
        $xml = simplexml_load_string(
            '<parent_element>' . $xml . '</parent_element>',
            \Magento\Framework\View\Layout\Element::class
        );
        return $xml->{$elementType};
    }
}
