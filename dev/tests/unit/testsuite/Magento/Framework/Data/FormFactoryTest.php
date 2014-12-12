<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Data;

/**
 * Tests for \Magento\Framework\Data\FormFactory
 */
class FormFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @expectedExceptionMessage WrongClass doesn't extend \Magento\Framework\Data\Form
     */
    public function testWrongTypeException()
    {
        $className = 'WrongClass';

        $formMock = $this->getMock($className, [], [], '', false);
        $this->_objectManagerMock->expects($this->once())->method('create')->will($this->returnValue($formMock));

        $formFactory = new FormFactory($this->_objectManagerMock, $className);
        $formFactory->create();
    }

    public function testCreate()
    {
        $className = 'Magento\Framework\Data\Form';
        $formMock = $this->getMock($className, [], [], '', false);
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className
        )->will(
            $this->returnValue($formMock)
        );

        $formFactory = new FormFactory($this->_objectManagerMock, $className);
        $this->assertSame($formMock, $formFactory->create());
    }
}
