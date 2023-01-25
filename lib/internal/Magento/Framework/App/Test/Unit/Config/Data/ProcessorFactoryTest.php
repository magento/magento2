<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config\Data;

use Magento\Framework\App\Config\Data\ProcessorFactory;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class ProcessorFactoryTest extends TestCase
{
    /**
     * @var ProcessorFactory
     */
    protected $_model;

    /**
     * @var ProcessorInterface
     */
    protected $_processorMock;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_model = new ProcessorFactory($this->_objectManager);
        $this->_processorMock = $this->getMockForAbstractClass(
            ProcessorInterface::class
        );
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
            \Magento\Framework\App\Config\Data\TestBackendModel::class
        )->willReturn(
            $this->_processorMock
        );

        $this->assertInstanceOf(
            ProcessorInterface::class,
            $this->_model->get(\Magento\Framework\App\Config\Data\TestBackendModel::class)
        );
    }

    /**
     * @covers \Magento\Framework\App\Config\Data\ProcessorFactory::get
     */
    public function testGetModelWithWrongInterface()
    {
        $this->expectException('InvalidArgumentException');
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Framework\App\Config\Data\WrongBackendModel::class
        )->willReturn(
            
                $this->getMockBuilder('WrongBackendModel')
                    ->getMock()
            
        );

        $this->_model->get(\Magento\Framework\App\Config\Data\WrongBackendModel::class);
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
            \Magento\Framework\App\Config\Data\TestBackendModel::class
        )->willReturn(
            $this->_processorMock
        );

        $this->_model->get(\Magento\Framework\App\Config\Data\TestBackendModel::class);
        $this->_model->get(\Magento\Framework\App\Config\Data\TestBackendModel::class);
    }
}
