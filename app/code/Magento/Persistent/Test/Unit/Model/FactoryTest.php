<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Model;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Persistent\Model\Factory
     */
    protected $_factory;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_factory = $helper->getObject(
            \Magento\Persistent\Model\Factory::class,
            ['objectManager' => $this->_objectManagerMock]
        );
    }

    public function testCreate()
    {
        $className = 'SomeModel';

        $classMock = $this->getMockBuilder('SomeModel')
            ->disableOriginalConstructor()
            ->getMock();
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
        $className = 'SomeModel';
        $data = ['param1', 'param2'];

        $classMock = $this->createMock('SomeModel');
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
