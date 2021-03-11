<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Plugin;

class LogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Plugin\Log
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $compareItemMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Visitor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $logResourceMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Visitor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->logResourceMock = $this->createMock(\Magento\Customer\Model\ResourceModel\Visitor::class);
        $this->compareItemMock = $this->createMock(\Magento\Catalog\Model\Product\Compare\Item::class);
        $this->subjectMock = $this->createMock(\Magento\Customer\Model\ResourceModel\Visitor::class);
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
