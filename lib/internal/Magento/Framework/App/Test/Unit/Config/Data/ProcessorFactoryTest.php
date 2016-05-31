<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config\Data;

class ProcessorFactoryTest extends \PHPUnit_Framework_TestCase
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
     * @expectedExceptionMessageRegExp /\w+\\WrongBackendModel is not instance of \w+\\ProcessorInterface/
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
