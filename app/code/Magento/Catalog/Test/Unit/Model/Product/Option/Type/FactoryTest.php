<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Option\Type;

use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Model\Product\Option\Type\Factory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var Factory
     */
    protected $_factory;

    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->_factory = $objectManagerHelper->getObject(
            Factory::class,
            ['objectManager' => $this->_objectManagerMock]
        );
    }

    public function testCreate()
    {
        $className = DefaultType::class;

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
        $className = DefaultType::class;
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

    public function testWrongTypeException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'WrongClass doesn\'t extends \Magento\Catalog\Model\Product\Option\Type\DefaultType'
        );
        $className = 'WrongClass';

        $filterMock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManagerMock->expects($this->once())->method('create')->willReturn($filterMock);

        $this->_factory->create($className);
    }
}
