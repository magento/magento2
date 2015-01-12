<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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

        $this->helperMock = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure\Helper')
            ->disableOriginalConstructor()->getMock();
        $this->readerPoolMock = $this->getMockBuilder('Magento\Framework\View\Layout\ReaderPool')
            ->disableOriginalConstructor()->getMock();

        $this->container = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Layout\Reader\Container',
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
     *
     * @dataProvider processDataProvider
     */
    public function testProcess(
        $elementCurrent,
        $containerName,
        $structureElement,
        $expectedData
    ) {
        /** @var ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject $scheduledStructureMock */
        $scheduledStructureMock = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure')
            ->disableOriginalConstructor()->getMock();
        $scheduledStructureMock->expects($this->once())
            ->method('getStructureElementData')
            ->with($containerName)
            ->willReturn($structureElement);
        $scheduledStructureMock->expects($this->once())
            ->method('setStructureElementData')
            ->with($containerName, $expectedData)
            ->willReturnSelf();

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $contextMock */
        $contextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
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

        $this->container->interpret($contextMock, $elementCurrent);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'container' => [
                'elementCurrent' => $this->getElement('<container name="container" id="id_add" tag="body"/>'),
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
            ],
            'referenceContainer' => [
                'elementCurrent' => $this->getElement(
                    '<referenceContainer name="reference" htmlTag="span" htmlId="id_add" htmlClass="new" label="Add"/>'
                ),
                'containerName' => 'reference',
                'structureElement' => [],
                'expectedData' => [
                    'attributes' => [
                        Container::CONTAINER_OPT_HTML_TAG   => 'span',
                        Container::CONTAINER_OPT_HTML_ID    => 'id_add',
                        Container::CONTAINER_OPT_HTML_CLASS => 'new',
                        Container::CONTAINER_OPT_LABEL      => 'Add',
                    ],
                ],
            ]
        ];
    }

    /**
     * @param string $xml
     * @return \Magento\Framework\View\Layout\Element
     */
    protected function getElement($xml)
    {
        $xml = simplexml_load_string(
            '<parent_element>' . $xml . '</parent_element>',
            'Magento\Framework\View\Layout\Element'
        );
        return current($xml->children());
    }
}
