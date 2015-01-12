<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Data;

class BackendModelPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Data\ProcessorFactory
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\Data\ProcessorInterface
     */
    protected $_processorMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_model = new \Magento\Framework\App\Config\Data\ProcessorFactory($this->_objectManager);
        $this->_processorMock = $this->getMockForAbstractClass('Magento\Framework\App\Config\Data\ProcessorInterface');
        $this->_processorMock->expects($this->any())->method('processValue')->will($this->returnArgument(0));
    }

    /**
     * @covers \Magento\Framework\App\Config\Data\ProcessorFactory::get
     */
    public function testGetModelWithCorrectInterface()
    {
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\Config\Data\TestBackendModel'
        )->will(
            $this->returnValue($this->_processorMock)
        );

        $this->assertInstanceOf(
            'Magento\Framework\App\Config\Data\ProcessorInterface',
            $this->_model->get('Magento\Framework\App\Config\Data\TestBackendModel')
        );
    }

    /**
     * @covers \Magento\Framework\App\Config\Data\ProcessorFactory::get
     * @expectedException \InvalidArgumentException
     */
    public function testGetModelWithWrongInterface()
    {
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\Config\Data\WrongBackendModel'
        )->will(
            $this->returnValue(
                $this->getMock('Magento\Framework\App\Config\Data\WrongBackendModel', [], [], '', false)
            )
        );

        $this->_model->get('Magento\Framework\App\Config\Data\WrongBackendModel');
    }

    /**
     * @covers \Magento\Framework\App\Config\Data\ProcessorFactory::get
     */
    public function testGetMemoryCache()
    {
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\Config\Data\TestBackendModel'
        )->will(
            $this->returnValue($this->_processorMock)
        );

        $this->_model->get('Magento\Framework\App\Config\Data\TestBackendModel');
        $this->_model->get('Magento\Framework\App\Config\Data\TestBackendModel');
    }
}
