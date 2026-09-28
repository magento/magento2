<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Persistent\Model\Factory;
use Magento\Persistent\Model\Session;
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
        $helper = new ObjectManager($this);

        $this->_objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->_factory = $helper->getObject(
            Factory::class,
            ['objectManager' => $this->_objectManagerMock]
        );
    }

    public function testCreate()
    {
        $className = Session::class;
        $classMock = $this->createMock(Session::class);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            []
        )->willReturn(
            $classMock
        );

        $this->assertEquals($classMock, $this->_factory->create($className));
    }

    public function testCreateWithArguments()
    {
        $className = Session::class;
        $data = ['param1', 'param2'];
        $classMock = $this->createMock(Session::class);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            $data
        )->willReturn(
            $classMock
        );

        $this->assertEquals($classMock, $this->_factory->create($className, $data));
    }
}
