<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Method;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Payment\Model\Method\Factory
     */
    protected $_factory;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_factory = $objectManagerHelper->getObject(
            \Magento\Payment\Model\Method\Factory::class,
            ['objectManager' => $this->_objectManagerMock]
        );
    }

    public function testCreateMethod()
    {
        $className = \Magento\Payment\Model\Method\AbstractMethod::class;
        $methodMock = $this->getMock($className, [], [], '', false);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            []
        )->will(
            $this->returnValue($methodMock)
        );

        $this->assertEquals($methodMock, $this->_factory->create($className));
    }

    public function testCreateMethodWithArguments()
    {
        $className = \Magento\Payment\Model\Method\AbstractMethod::class;
        $data = ['param1', 'param2'];
        $methodMock = $this->getMock($className, [], [], '', false);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            $data
        )->will(
            $this->returnValue($methodMock)
        );

        $this->assertEquals($methodMock, $this->_factory->create($className, $data));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage WrongClass class doesn't implement \Magento\Payment\Model\MethodInterface
     */
    public function testWrongTypeException()
    {
        $className = 'WrongClass';
        $methodMock = $this->getMock($className, [], [], '', false);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            []
        )->will(
            $this->returnValue($methodMock)
        );

        $this->_factory->create($className);
    }
}
