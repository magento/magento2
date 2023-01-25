<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\Plugin;

use Magento\Customer\Model\ResourceModel\Visitor;
use Magento\Reports\Model\Event;
use Magento\Reports\Model\Plugin\Log;
use Magento\Reports\Model\Product\Index\Compared;
use Magento\Reports\Model\Product\Index\Viewed;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Event|MockObject
     */
    protected $eventMock;

    /**
     * @var Compared|MockObject
     */
    protected $comparedMock;

    /**
     * @var Viewed|MockObject
     */
    protected $viewedMock;

    /**
     * @var Visitor|MockObject
     */
    protected $logResourceMock;

    /**
     * @var Visitor|MockObject
     */
    protected $subjectMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->comparedMock = $this->getMockBuilder(Compared::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewedMock = $this->getMockBuilder(Viewed::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logResourceMock = $this->getMockBuilder(Visitor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(Visitor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->log = new Log(
            $this->eventMock,
            $this->comparedMock,
            $this->viewedMock
        );
    }

    /**
     * @return void
     */
    public function testAfterClean()
    {
        $this->eventMock->expects($this->once())->method('clean');
        $this->comparedMock->expects($this->once())->method('clean');
        $this->viewedMock->expects($this->once())->method('clean');

        $this->assertEquals(
            $this->logResourceMock,
            $this->log->afterClean($this->subjectMock, $this->logResourceMock)
        );
    }
}
