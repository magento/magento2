<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Convert\Test\Unit;

use Magento\Framework\Convert\Excel;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExcelFactoryTest extends TestCase
{
    /**
     * @var ExcelFactory
     */
    protected $model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();

        $this->model = new ExcelFactory(
            $this->objectManager
        );
    }

    public function testCreate()
    {
        $excel = $this->getMockBuilder(Excel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(Excel::class, [])
            ->willReturn($excel);

        $this->assertInstanceOf(Excel::class, $this->model->create());
    }
}
