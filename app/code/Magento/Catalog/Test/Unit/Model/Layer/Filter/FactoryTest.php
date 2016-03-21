<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Layer\Filter;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Factory
     */
    protected $_factory;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_factory = $objectManagerHelper->getObject(
            'Magento\Catalog\Model\Layer\Filter\Factory',
            ['objectManager' => $this->_objectManagerMock]
        );
    }

    public function testCreate()
    {
        $className = 'Magento\Catalog\Model\Layer\Filter\AbstractFilter';

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
        $className = 'Magento\Catalog\Model\Layer\Filter\AbstractFilter';
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage WrongClass doesn't extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    public function testWrongTypeException()
    {
        $className = 'WrongClass';

        $filterMock = $this->getMock($className, [], [], '', false);
        $this->_objectManagerMock->expects($this->once())->method('create')->will($this->returnValue($filterMock));

        $this->_factory->create($className);
    }
}
