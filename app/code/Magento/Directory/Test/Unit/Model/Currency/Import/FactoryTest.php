<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Currency\Import;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Model\Currency\Import\Factory
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Directory\Model\Currency\Import\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importConfig;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_importConfig = $this->getMock(
            \Magento\Directory\Model\Currency\Import\Config::class,
            [],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Directory\Model\Currency\Import\Factory(
            $this->_objectManager,
            $this->_importConfig
        );
    }

    public function testCreate()
    {
        $expectedResult = $this->getMock(\Magento\Directory\Model\Currency\Import\ImportInterface::class);
        $this->_importConfig->expects(
            $this->once()
        )->method(
            'getServiceClass'
        )->with(
            'test'
        )->will(
            $this->returnValue('Test_Class')
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Test_Class',
            ['argument' => 'value']
        )->will(
            $this->returnValue($expectedResult)
        );
        $actualResult = $this->_model->create('test', ['argument' => 'value']);
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Currency import service 'test' is not defined
     */
    public function testCreateUndefinedServiceClass()
    {
        $this->_importConfig->expects(
            $this->once()
        )->method(
            'getServiceClass'
        )->with(
            'test'
        )->will(
            $this->returnValue(null)
        );
        $this->_objectManager->expects($this->never())->method('create');
        $this->_model->create('test');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Class 'stdClass' has to implement
     * \Magento\Directory\Model\Currency\Import\ImportInterface
     */
    public function testCreateIrrelevantServiceClass()
    {
        $this->_importConfig->expects(
            $this->once()
        )->method(
            'getServiceClass'
        )->with(
            'test'
        )->will(
            $this->returnValue('stdClass')
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'stdClass'
        )->will(
            $this->returnValue(new \stdClass())
        );
        $this->_model->create('test');
    }
}
