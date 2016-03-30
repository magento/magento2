<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\Plugin;

use Magento\Reports\Model\Plugin\Log;

class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\Plugin\Log
     */
    protected $log;

    /**
     * @var \Magento\Reports\Model\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\Compared|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $comparedMock;

    /**
     * @var \Magento\Reports\Model\Product\Index\Viewed|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewedMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logResourceMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->eventMock = $this->getMockBuilder('Magento\Reports\Model\Event')
            ->disableOriginalConstructor()
            ->getMock();
        $this->comparedMock = $this->getMockBuilder('Magento\Reports\Model\Product\Index\Compared')
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewedMock = $this->getMockBuilder('Magento\Reports\Model\Product\Index\Viewed')
            ->disableOriginalConstructor()
            ->getMock();

        $this->logResourceMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Visitor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Visitor')
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
