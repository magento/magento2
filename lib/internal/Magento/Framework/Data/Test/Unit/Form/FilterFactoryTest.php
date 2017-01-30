<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Form;

use Magento\Framework\Data\Form\FilterFactory;

class FilterFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterFactory
     */
    protected $factory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->getMockForAbstractClass();

        $this->factory = new FilterFactory($this->objectManager);
    }

    public function testCreate()
    {
        $filterClassPrefix = 'Magento\\Framework\\Data\\Form\\Filter\\';
        $filterCode = 'Date';
        $data = [];

        $filterMock = $this->getMockBuilder('Magento\Framework\Data\Form\Filter\Date')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($filterClassPrefix . ucfirst($filterCode), $data)
            ->willReturn($filterMock);

        $result = $this->factory->create($filterCode, $data);
        $this->assertInstanceOf('\Magento\Framework\Data\Form\Filter\FilterInterface', $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithException()
    {
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
