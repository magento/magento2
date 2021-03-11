<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Form;

use Magento\Framework\Data\Form\FilterFactory;

class FilterFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FilterFactory
     */
    protected $factory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->factory = new FilterFactory($this->objectManager);
    }

    public function testCreate()
    {
        $filterClassPrefix = 'Magento\\Framework\\Data\\Form\\Filter\\';
        $filterCode = 'Date';
        $data = [];

        $filterMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Filter\Date::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($filterClassPrefix . ucfirst($filterCode), $data)
            ->willReturn($filterMock);

        $result = $this->factory->create($filterCode, $data);
        $this->assertInstanceOf(\Magento\Framework\Data\Form\Filter\FilterInterface::class, $result);
    }

    /**
     */
    public function testCreateWithException()
    {
        $this->expectException(\InvalidArgumentException::class);

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
