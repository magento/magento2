<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class MoveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Move
     */
    protected $move;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var ScheduledStructure|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scheduledStructureMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->scheduledStructureMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ScheduledStructure::class)
            ->disableOriginalConstructor()->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->contextMock->expects($this->any())
            ->method('getScheduledStructure')
            ->willReturn($this->scheduledStructureMock);

        $this->move = $this->objectManagerHelper->getObject(\Magento\Framework\View\Layout\Reader\Move::class);
    }

    /**
     * @param \Magento\Framework\View\Layout\Element $currentElement
     * @param string $destination
     * @param string $siblingName
     * @param bool $isAfter
     * @param string $alias
     * @param \Magento\Framework\View\Layout\Element $parentElement
     *
     * @dataProvider processDataProvider
     */
    public function testProcess($currentElement, $destination, $siblingName, $isAfter, $alias, $parentElement)
    {
        $this->scheduledStructureMock->expects($this->any())
            ->method('setElementToMove')
            ->with(
                (string)$currentElement->getAttribute('element'),
                [$destination, $siblingName, $isAfter, $alias]
            );
        $this->move->interpret($this->contextMock, $currentElement, $parentElement);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'move_before' => [
                'element' => new \Magento\Framework\View\Layout\Element('
                    <move element="product" destination="product.info" before="before.block" as="as.product.info"/>
                '),
                'destination' => 'product.info',
                'siblingName' => 'before.block',
                'isAfter' => false,
                'alias' => 'as.product.info',
                'parentElement' => new \Magento\Framework\View\Layout\Element('<element/>'),
            ],
            'move_after' => [
                'element' => new \Magento\Framework\View\Layout\Element('
                    <move element="product" destination="product.info" after="after.block" as="as.product.info"/>
                '),
                'destination' => 'product.info',
                'siblingName' => 'after.block',
                'isAfter' => true,
                'alias' => 'as.product.info',
                'parentElement' => new \Magento\Framework\View\Layout\Element('<element/>'),
            ]
        ];
    }

    /**
     */
    public function testProcessInvalidData()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $invalidElement = new \Magento\Framework\View\Layout\Element('<move element="product" into="product.info"/>');
        $this->move->interpret($this->contextMock, $invalidElement, $invalidElement);
    }
}
