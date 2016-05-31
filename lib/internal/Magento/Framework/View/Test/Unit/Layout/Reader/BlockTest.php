<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Layout\Reader\Block
 */
namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\View\Layout\Reader\Block;

class BlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduledStructure;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Layout\ReaderPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerPool;

    /**
     * @var \Magento\Framework\View\Layout\Element
     */
    protected $currentElement;

    /**
     * @param string $xml
     * @param string $elementType
     * @return \Magento\Framework\View\Layout\Element
     */
    protected function getElement($xml, $elementType)
    {
        $xml = '<' . Block::TYPE_BLOCK . '>'
            . $xml
            . '</' . Block::TYPE_BLOCK . '>';

        $xml = simplexml_load_string($xml, 'Magento\Framework\View\Layout\Element');
        return $xml->{$elementType};
    }

    /**
     * Prepare reader pool
     *
     * @param string $xml
     * @param string $elementType
     */
    protected function prepareReaderPool($xml, $elementType)
    {
        $this->currentElement = $this->getElement($xml, $elementType);
        $this->readerPool->expects($this->once())->method('interpret')->with($this->context, $this->currentElement);
    }

    /**
     * Return testing instance of block
     *
     * @param array $arguments
     * @return Block
     */
    protected function getBlock(array $arguments)
    {
        return (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Layout\Reader\Block', $arguments);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->scheduledStructure = $this->getMock(
            'Magento\Framework\View\Layout\ScheduledStructure',
            [],
            [],
            '',
            false
        );
        $this->context = $this->getMock('Magento\Framework\View\Layout\Reader\Context', [], [], '', false);
        $this->readerPool = $this->getMock('Magento\Framework\View\Layout\ReaderPool', [], [], '', false);
    }

    /**
     * @param string $literal
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $scheduleStructureCount
     * @param string $ifconfigValue
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $ifconfigCondition
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getCondition
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setCondition
     * @dataProvider processBlockDataProvider
     */
    public function testProcessBlock(
        $literal,
        $scheduleStructureCount,
        $ifconfigValue,
        $ifconfigCondition,
        $getCondition,
        $setCondition
    ) {
        $this->context->expects($this->once())->method('getScheduledStructure')
            ->will($this->returnValue($this->scheduledStructure));

        $this->scheduledStructure->expects($ifconfigCondition)
            ->method('setElementToIfconfigList')
            ->with($literal, $ifconfigValue, 'scope');
        $this->scheduledStructure->expects($getCondition)
            ->method('getStructureElementData')
            ->with($literal, [])
            ->willReturn([
                'actions' => [
                    ['someMethod', [], 'action_config_path', 'scope'],
                ],
            ]);
        $this->scheduledStructure->expects($setCondition)
            ->method('setStructureElementData')
            ->with(
                $literal,
                [
                    'attributes' => [
                        Block::ATTRIBUTE_GROUP => '',
                        Block::ATTRIBUTE_CLASS => '',
                        Block::ATTRIBUTE_TEMPLATE => '',
                        Block::ATTRIBUTE_TTL => '',
                        Block::ATTRIBUTE_DISPLAY => '',
                        Block::ATTRIBUTE_ACL => ''
                    ],
                    'actions' => [
                        ['someMethod', [], 'action_config_path', 'scope'],
                    ],
                    'arguments' => [],
                ]
            );

        $helper = $this->getMock('Magento\Framework\View\Layout\ScheduledStructure\Helper', [], [], '', false);
        $helper->expects($scheduleStructureCount)->method('scheduleStructure')->will($this->returnValue($literal));

        $this->prepareReaderPool(
            '<' . $literal . ' ifconfig="' . $ifconfigValue . '">'
            . '<action method="someMethod" ifconfig="action_config_path" />'
            . '</' . $literal . '>',
            $literal
        );

        /** @var \Magento\Framework\View\Layout\Reader\Block $block */
        $block = $this->getBlock(
            [
                'helper' => $helper,
                'readerPool' => $this->readerPool,
                'scopeType' => 'scope',
            ]
        );
        $block->interpret($this->context, $this->currentElement);
    }

    /**
     * @return array
     */
    public function processBlockDataProvider()
    {
        return [
            ['block', $this->once(), '', $this->never(), $this->once(), $this->once()],
            ['block', $this->once(), 'config_path', $this->once(), $this->once(), $this->once()],
            ['page', $this->never(), '', $this->never(), $this->never(), $this->never()]
        ];
    }

    /**
     * @param string $literal
     * @param string $remove
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getCondition
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setCondition
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setRemoveCondition
     * @dataProvider processReferenceDataProvider
     */
    public function testProcessReference(
        $literal,
        $remove,
        $getCondition,
        $setCondition,
        $setRemoveCondition
    ) {
        $this->context->expects($this->once())->method('getScheduledStructure')
            ->will($this->returnValue($this->scheduledStructure));

        $this->scheduledStructure->expects($setRemoveCondition)
            ->method('setElementToRemoveList')
            ->with($literal);

        $this->scheduledStructure->expects($getCondition)
            ->method('getStructureElementData')
            ->with($literal, [])
            ->willReturn([
                'actions' => [
                    ['someMethod', [], 'action_config_path', 'scope'],
                ],
            ]);
        $this->scheduledStructure->expects($setCondition)
            ->method('setStructureElementData')
            ->with(
                $literal,
                [
                    'actions' => [
                        ['someMethod', [], 'action_config_path', 'scope'],
                    ],
                    'arguments' => [],
                    'attributes' => [
                        Block::ATTRIBUTE_GROUP => '',
                        Block::ATTRIBUTE_CLASS => '',
                        Block::ATTRIBUTE_TEMPLATE => '',
                        Block::ATTRIBUTE_TTL => '',
                        Block::ATTRIBUTE_DISPLAY => '',
                        Block::ATTRIBUTE_ACL => ''
                    ]
                ]
            );

        $this->prepareReaderPool(
            '<' . $literal . ' name="' . $literal . '" remove="' . $remove . '">'
            . '<action method="someMethod" ifconfig="action_config_path" />'
            . '</' . $literal . '>',
            $literal
        );

        /** @var \Magento\Framework\View\Layout\Reader\Block $block */
        $block = $this->getBlock(
            [
                'readerPool' => $this->readerPool,
                'scopeType' => 'scope',
            ]
        );
        $block->interpret($this->context, $this->currentElement);
    }

    /**
     * @return array
     */
    public function processReferenceDataProvider()
    {
        return [
            ['referenceBlock', 'false', $this->once(), $this->once(), $this->never()],
            ['referenceBlock', 'true', $this->never(), $this->never(), $this->once()],
            ['page', 'false', $this->never(), $this->never(), $this->never()],
            ['page', 'true', $this->never(), $this->never(), $this->never()],
        ];
    }
}
