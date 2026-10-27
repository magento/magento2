<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\Container;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\ScheduledStructure\Helper;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ContainerTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Container|MockObject
     */
    protected $container;

    /**
     * @var Helper|MockObject
     */
    protected $helperMock;

    /**
     * @var ReaderPool|MockObject
     */
    protected $readerPoolMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerPoolMock = $this->getMockBuilder(ReaderPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container = $this->objectManagerHelper->getObject(
            Container::class,
            [
                'helper' => $this->helperMock,
                'readerPool' => $this->readerPoolMock
            ]
        );
    }

    /**
     * @param Element $elementCurrent
     * @param string $containerName
     * @param array $structureElement
     * @param array $expectedData
     * @param InvokedCount $getStructureCondition
     * @param InvokedCount $setStructureCondition
     * @param InvokedCount $setRemoveCondition
     *     */
    #[DataProvider('processDataProvider')]
    public function testProcess(
        $elementCurrent,
        $containerName,
        $structureElement,
        $expectedData,
        $getStructureCondition,
        $setStructureCondition,
        $setRemoveCondition
    ) {
        // Convert string expectations to matchers
        $getStructureCondition = is_string($getStructureCondition) 
            ? $this->createInvocationMatcher($getStructureCondition) 
            : $getStructureCondition;
        $setStructureCondition = is_string($setStructureCondition) 
            ? $this->createInvocationMatcher($setStructureCondition) 
            : $setStructureCondition;
        $setRemoveCondition = is_string($setRemoveCondition) 
            ? $this->createInvocationMatcher($setRemoveCondition) 
            : $setRemoveCondition;
        
        /** @var ScheduledStructure|MockObject $scheduledStructureMock */
        $scheduledStructureMock = $this->getMockBuilder(ScheduledStructure::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        /** @var Context|MockObject $contextMock */
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
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
    public static function processDataProvider()
    {
        return [
            'container' => [
                'elementCurrent' => self::getElement(
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
                'getStructureCondition' => 'once',
                'setStructureCondition' => 'once',
                'setRemoveCondition' => 'never',
            ],
            'referenceContainer' => [
                'elementCurrent' => self::getElement(
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
                'getStructureCondition' => 'once',
                'setStructureCondition' => 'once',
                'setRemoveCondition' => 'never',
            ],
            'referenceContainerNoRemove' => [
                'elementCurrent' => self::getElement(
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
                'getStructureCondition' => 'once',
                'setStructureCondition' => 'once',
                'setRemoveCondition' => 'never',
            ],
            'referenceContainerRemove' => [
                'elementCurrent' => self::getElement(
                    '<referenceContainer name="reference" remove="1"/>',
                    'referenceContainer'
                ),
                'containerName' => 'reference',
                'structureElement' => [],
                'expectedData' => [],
                'getStructureCondition' => 'never',
                'setStructureCondition' => 'never',
                'setRemoveCondition' => 'once',
            ],
            'referenceContainerRemove2' => [
                'elementCurrent' => self::getElement(
                    '<referenceContainer name="reference" remove="true"/>',
                    'referenceContainer'
                ),
                'containerName' => 'reference',
                'structureElement' => [],
                'expectedData' => [],
                'getStructureCondition' => 'never',
                'setStructureCondition' => 'never',
                'setRemoveCondition' => 'once',
            ],
            'referenceContainerDisplayFalse' => [
                'elementCurrent' => self::getElement(
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
                'getStructureCondition' => 'once',
                'setStructureCondition' => 'once',
                'setRemoveCondition' => 'never',
            ]
        ];
    }

    /**
     * @param string $xml
     * @param string $elementType
     * @return Element
     */
    protected static function getElement($xml, $elementType)
    {
        $xml = simplexml_load_string(
            '<parent_element>' . $xml . '</parent_element>',
            Element::class
        );
        return $xml->{$elementType};
    }
}
