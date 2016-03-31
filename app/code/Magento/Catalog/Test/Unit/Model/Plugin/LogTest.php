<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Plugin;

class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Plugin\Log
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $compareItemMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logResourceMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Visitor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->logResourceMock = $this->getMock('Magento\Customer\Model\ResourceModel\Visitor', [], [], '', false);
        $this->compareItemMock = $this->getMock(
            'Magento\Catalog\Model\Product\Compare\Item',
            [],
            [],
            '',
            false
        );
        $this->subjectMock = $this->getMock('Magento\Customer\Model\ResourceModel\Visitor', [], [], '', false);
        $this->model = new \Magento\Catalog\Model\Plugin\Log($this->compareItemMock);
    }

    /**
     * @covers \Magento\Catalog\Model\Plugin\Log::afterClean
     */
    public function testAfterClean()
    {
        $this->compareItemMock->expects($this->once())->method('clean');

        $this->assertEquals(
            $this->logResourceMock,
            $this->model->afterClean($this->subjectMock, $this->logResourceMock)
        );
    }
}
