<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Layout\Reader\Move;
use Magento\Framework\View\Layout\ScheduledStructure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MoveTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Move
     */
    protected $move;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ScheduledStructure|MockObject
     */
    protected $scheduledStructureMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->scheduledStructureMock = $this->getMockBuilder(ScheduledStructure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getScheduledStructure')
            ->willReturn($this->scheduledStructureMock);

        $this->move = $this->objectManagerHelper->getObject(Move::class);
    }

    /**
     * @param Element $currentElement
     * @param string $destination
     * @param string $siblingName
     * @param bool $isAfter
     * @param string $alias
     * @param Element $parentElement
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
                'element' => new Element('
                    <move element="product" destination="product.info" before="before.block" as="as.product.info"/>
                '),
                'destination' => 'product.info',
                'siblingName' => 'before.block',
                'isAfter' => false,
                'alias' => 'as.product.info',
                'parentElement' => new Element('<element/>'),
            ],
            'move_after' => [
                'element' => new Element('
                    <move element="product" destination="product.info" after="after.block" as="as.product.info"/>
                '),
                'destination' => 'product.info',
                'siblingName' => 'after.block',
                'isAfter' => true,
                'alias' => 'as.product.info',
                'parentElement' => new Element('<element/>'),
            ]
        ];
    }

    public function testProcessInvalidData()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $invalidElement = new Element('<move element="product" into="product.info"/>');
        $this->move->interpret($this->contextMock, $invalidElement, $invalidElement);
    }
}
