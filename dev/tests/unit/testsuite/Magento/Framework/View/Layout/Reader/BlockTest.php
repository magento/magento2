<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Layout\Reader\Block
 */
namespace Magento\Framework\View\Layout\Reader;

/**
 * Class BlockTest
 *
 * @covers Magento\Framework\View\Layout\Reader\Block
 */
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
     * @return \Magento\Framework\View\Layout\Element
     */
    protected function getElement($xml)
    {
        $xml = '<' . \Magento\Framework\View\Layout\Reader\Block::TYPE_BLOCK . '>'
            . $xml
            . '</' . \Magento\Framework\View\Layout\Reader\Block::TYPE_BLOCK . '>';

        $xml = simplexml_load_string($xml, 'Magento\Framework\View\Layout\Element');
        return current($xml->children());
    }

    /**
     * Prepare reader pool
     *
     * @param string $xml
     */
    protected function prepareReaderPool($xml)
    {
        $this->currentElement = $this->getElement($xml);
        $this->readerPool->expects($this->once())->method('interpret')->with($this->context, $this->currentElement);
    }

    /**
     * Return testing instance of block
     *
     * @param array $arguments
     * @return \Magento\Framework\View\Layout\Reader\Block
     */
    protected function getBlock(array $arguments)
    {
        return (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Layout\Reader\Block', $arguments);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
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
     * @dataProvider processDataProvider
     */
    public function testProcessBlock(
        $literal,
        $scheduleStructureCount
    ) {
        $this->context->expects($this->once())->method('getScheduledStructure')
            ->will($this->returnValue($this->scheduledStructure));

        $testValue = 'some_value';

        $helper = $this->getMock('Magento\Framework\View\Layout\ScheduledStructure\Helper', [], [], '', false);
        $helper->expects($scheduleStructureCount)->method('scheduleStructure')->will($this->returnValue($literal));

        $this->prepareReaderPool('<' . $literal . ' ifconfig="' . $testValue . '"/>');

        /** @var \Magento\Framework\View\Layout\Reader\Block $block */
        $block = $this->getBlock(
            [
                'helper' => $helper,
                'readerPool' => $this->readerPool,
            ]
        );
        $block->interpret($this->context, $this->currentElement);
    }

    /**
     * @covers Magento\Framework\View\Layout\Reader\Block::interpret()
     */
    public function testProcessReference()
    {
        $testName = 'test_value';
        $literal = 'referenceBlock';
        $this->context->expects($this->once())->method('getScheduledStructure')
            ->will($this->returnValue($this->scheduledStructure));
        $this->scheduledStructure->expects($this->once())->method('getStructureElementData')->with($testName, [])
            ->will($this->returnValue([]));
        $this->scheduledStructure->expects($this->once())->method('setStructureElementData')
            ->with($testName, ['actions' => [], 'arguments' => []]);

        $this->prepareReaderPool('<' . $literal . ' name="' . $testName . '"/>');

        /** @var \Magento\Framework\View\Layout\Reader\Block $block */
        $block = $this->getBlock(['readerPool' => $this->readerPool]);
        $block->interpret($this->context, $this->currentElement);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            ['block', $this->once()],
            ['page', $this->never()]
        ];
    }
}
