<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Option\Type;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Catalog\Model\Product\Option\Type\Factory
     */
    protected $_factory;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_factory = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product\Option\Type\Factory::class,
            ['objectManager' => $this->_objectManagerMock]
        );
    }

    public function testCreate()
    {
        $className = \Magento\Catalog\Model\Product\Option\Type\DefaultType::class;

        $filterMock = $this->createMock($className);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            []
        )->willReturn(
            $filterMock
        );

        $this->assertEquals($filterMock, $this->_factory->create($className));
    }

    public function testCreateWithArguments()
    {
        $className = \Magento\Catalog\Model\Product\Option\Type\DefaultType::class;
        $arguments = ['foo', 'bar'];

        $filterMock = $this->createMock($className);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            $arguments
        )->willReturn(
            $filterMock
        );

        $this->assertEquals($filterMock, $this->_factory->create($className, $arguments));
    }

    /**
     */
    public function testWrongTypeException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('WrongClass doesn\'t extends \\Magento\\Catalog\\Model\\Product\\Option\\Type\\DefaultType');

        $className = 'WrongClass';

        $filterMock = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $this->_objectManagerMock->expects($this->once())->method('create')->willReturn($filterMock);

        $this->_factory->create($className);
    }
}
