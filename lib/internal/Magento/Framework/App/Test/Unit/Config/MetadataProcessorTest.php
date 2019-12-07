<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\MetadataProcessor;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Config\Data\ProcessorFactory;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use \PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * {@inheritdoc}
 */
class MetadataProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MetadataProcessor
     */
    protected $_model;

    /**
     * @var Initial|Mock
     */
    protected $_initialConfigMock;

    /**
     * @var ProcessorFactory|Mock
     */
    protected $_modelPoolMock;

    /**
     * @var ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendModelMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->_modelPoolMock = $this->getMockBuilder(ProcessorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_initialConfigMock = $this->getMockBuilder(Initial::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_backendModelMock = $this->getMockBuilder(ProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->_initialConfigMock->expects($this->any())
            ->method('getMetadata')
            ->willReturn(
                ['some/config/path' => ['backendModel' => 'Custom_Backend_Model']]
            );

        $this->_model = new \Magento\Framework\App\Config\MetadataProcessor(
            $this->_modelPoolMock,
            $this->_initialConfigMock
        );
    }

    public function testProcess()
    {
        $this->_modelPoolMock->expects($this->once())
            ->method('get')
            ->with('Custom_Backend_Model')
            ->willReturn($this->_backendModelMock);
        $this->_backendModelMock->expects($this->once())
            ->method('processValue')
            ->with('value')
            ->willReturn('processed_value');

        $data = ['some' => ['config' => ['path' => 'value']], 'active' => 1];
        $expectedResult = $data;
        $expectedResult['some']['config']['path'] = 'processed_value';
        $this->assertEquals($expectedResult, $this->_model->process($data));
    }
}
