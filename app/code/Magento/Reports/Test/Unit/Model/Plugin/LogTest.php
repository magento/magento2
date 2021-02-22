<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\Plugin;

use Magento\Reports\Model\Plugin\Log;

class LogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Model\Plugin\Log
     */
    protected $log;

    /**
     * @var \Magento\Reports\Model\Event|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\Compared|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $comparedMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\Viewed|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewedMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Visitor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $logResourceMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Visitor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->eventMock = $this->getMockBuilder(\Magento\Reports\Model\Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->comparedMock = $this->getMockBuilder(\Magento\Reports\Model\Product\Index\Compared::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewedMock = $this->getMockBuilder(\Magento\Reports\Model\Product\Index\Viewed::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logResourceMock = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Visitor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Visitor::class)
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
