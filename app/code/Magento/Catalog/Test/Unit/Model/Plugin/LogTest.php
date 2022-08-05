<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\Plugin\Log;
use Magento\Catalog\Model\Product\Compare\Item;
use Magento\Customer\Model\ResourceModel\Visitor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    /**
     * @var Log
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $compareItemMock;

    /**
     * @var Visitor|MockObject
     */
    protected $logResourceMock;

    /**
     * @var Visitor|MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->logResourceMock = $this->createMock(Visitor::class);
        $this->compareItemMock = $this->createMock(Item::class);
        $this->subjectMock = $this->createMock(Visitor::class);
        $this->model = new Log($this->compareItemMock);
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
