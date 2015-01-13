<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\Plugin;

class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\Plugin\Log
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportEventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmpProductIdxMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewProductIdxMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->reportEventMock = $this->getMock('Magento\Reports\Model\Event', [], [], '', false);
        $this->cmpProductIdxMock = $this->getMock(
            'Magento\Reports\Model\Product\Index\Compared',
            [],
            [],
            '',
            false
        );
        $this->viewProductIdxMock = $this->getMock(
            'Magento\Reports\Model\Product\Index\Viewed',
            [],
            [],
            '',
            false
        );

        $this->logResourceMock = $this->getMock('Magento\Log\Model\Resource\Log', [], [], '', false);

        $this->subjectMock = $this->getMock('Magento\Log\Model\Resource\Log', [], [], '', false);
        $this->model = new \Magento\Reports\Model\Plugin\Log(
            $this->reportEventMock,
            $this->cmpProductIdxMock,
            $this->viewProductIdxMock
        );
    }

    /**
     * @covers \Magento\Reports\Model\Plugin\Log::afterClean
     */
    public function testAfterClean()
    {
        $this->reportEventMock->expects($this->once())->method('clean');

        $this->cmpProductIdxMock->expects($this->once())->method('clean');

        $this->viewProductIdxMock->expects($this->once())->method('clean');

        $this->assertEquals(
            $this->logResourceMock,
            $this->model->afterClean($this->subjectMock, $this->logResourceMock)
        );
    }
}
