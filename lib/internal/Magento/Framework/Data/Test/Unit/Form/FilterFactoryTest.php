<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form;

use Magento\Framework\Data\Form\Filter\Date;
use Magento\Framework\Data\Form\Filter\FilterInterface;
use Magento\Framework\Data\Form\FilterFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterFactoryTest extends TestCase
{
    /**
     * @var FilterFactory
     */
    protected $factory;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->factory = new FilterFactory($this->objectManager);
    }

    public function testCreate()
    {
        $filterClassPrefix = 'Magento\\Framework\\Data\\Form\\Filter\\';
        $filterCode = 'Date';
        $data = [];

        $filterMock = $this->getMockBuilder(Date::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($filterClassPrefix . ucfirst($filterCode), $data)
            ->willReturn($filterMock);

        $result = $this->factory->create($filterCode, $data);
        $this->assertInstanceOf(FilterInterface::class, $result);
    }

    public function testCreateWithException()
    {
        $this->expectException('InvalidArgumentException');
        $filterClassPrefix = 'Magento\\Framework\\Data\\Form\\Filter\\';
        $filterCode = 'Undefined';
        $data = [];

        $filter = new \stdClass();

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($filterClassPrefix . ucfirst($filterCode), $data)
            ->willReturn($filter);

        $this->factory->create($filterCode, $data);
    }
}
