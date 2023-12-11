<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Template\Filter;

use Magento\Catalog\Model\Template\Filter\Factory;
use Magento\Framework\Filter\Template;
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

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->_objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->_factory = $objectManagerHelper->getObject(
            Factory::class,
            ['objectManager' => $this->_objectManagerMock]
        );
    }

    /**
     * Test create
     *
     * @return void
     */
    public function testCreate()
    {
        $className = Template::class;

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

    /**
     * Test create with arguments
     *
     * @return void
     */
    public function testCreateWithArguments()
    {
        $className = Template::class;
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
     * Test wrong type exception
     *
     * @return void
     */
    public function testWrongTypeException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('WrongClass doesn\'t extend \Magento\Framework\Filter\Template');
        $className = 'WrongClass';

        $filterMock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManagerMock->expects($this->once())->method('create')->willReturn($filterMock);

        $this->_factory->create($className);
    }
}
