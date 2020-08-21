<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Layout\Reader\Move;
use Magento\Framework\View\Layout\ReaderFactory;
use Magento\Framework\View\Layout\ReaderPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderPoolTest extends TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ReaderPool */
    protected $pool;

    /** @var ReaderFactory|MockObject */
    protected $readerFactoryMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->readerFactoryMock = $this->getMockBuilder(ReaderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pool = $this->objectManagerHelper->getObject(
            ReaderPool::class,
            [
                'readerFactory' => $this->readerFactoryMock,
                'readers' => ['move' => Move::class]
            ]
        );
    }

    public function testInterpret()
    {
        /** @var Reader\Context $contextMock */
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $currentElement = new Element(
            '<element><move name="block"/><remove name="container"/><ignored name="user"/></element>'
        );

        /**
         * @var \Magento\Framework\View\Layout\Reader\Move|MockObject $moveReaderMock
         */
        $moveReaderMock = $this->getMockBuilder(Move::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moveReaderMock->expects($this->exactly(2))->method('interpret')
            ->willReturn($this->returnSelf());
        $moveReaderMock->method('getSupportedNodes')
            ->willReturn(['move']);

        $this->readerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturnMap([[Move::class, [], $moveReaderMock]]);

        $this->pool->interpret($contextMock, $currentElement);
        $this->pool->interpret($contextMock, $currentElement);
    }
}
