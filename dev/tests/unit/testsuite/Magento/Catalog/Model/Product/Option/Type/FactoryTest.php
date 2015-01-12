<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Option\Type;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Catalog\Model\Product\Option\Type\Factory
     */
    protected $_factory;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_factory = $objectManagerHelper->getObject(
            'Magento\Catalog\Model\Product\Option\Type\Factory',
            ['objectManager' => $this->_objectManagerMock]
        );
    }

    public function testCreate()
    {
        $className = 'Magento\Catalog\Model\Product\Option\Type\DefaultType';

        $filterMock = $this->getMock($className, [], [], '', false);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            []
        )->will(
            $this->returnValue($filterMock)
        );

        $this->assertEquals($filterMock, $this->_factory->create($className));
    }

    public function testCreateWithArguments()
    {
        $className = 'Magento\Catalog\Model\Product\Option\Type\DefaultType';
        $arguments = ['foo', 'bar'];

        $filterMock = $this->getMock($className, [], [], '', false);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            $arguments
        )->will(
            $this->returnValue($filterMock)
        );

        $this->assertEquals($filterMock, $this->_factory->create($className, $arguments));
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage WrongClass doesn't extends \Magento\Catalog\Model\Product\Option\Type\DefaultType
     */
    public function testWrongTypeException()
    {
        $className = 'WrongClass';

        $filterMock = $this->getMock($className, [], [], '', false);
        $this->_objectManagerMock->expects($this->once())->method('create')->will($this->returnValue($filterMock));

        $this->_factory->create($className);
    }
}
