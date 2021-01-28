<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Currency\Import;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Model\Currency\Import\Factory
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Directory\Model\Currency\Import\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_importConfig;

    protected function setUp(): void
    {
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_importConfig = $this->createMock(\Magento\Directory\Model\Currency\Import\Config::class);
        $this->_model = new \Magento\Directory\Model\Currency\Import\Factory(
            $this->_objectManager,
            $this->_importConfig
        );
    }

    public function testCreate()
    {
        $expectedResult = $this->createMock(\Magento\Directory\Model\Currency\Import\ImportInterface::class);
        $this->_importConfig->expects(
            $this->once()
        )->method(
            'getServiceClass'
        )->with(
            'test'
        )->willReturn(
            'Test_Class'
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Test_Class',
            ['argument' => 'value']
        )->willReturn(
            $expectedResult
        );
        $actualResult = $this->_model->create('test', ['argument' => 'value']);
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     */
    public function testCreateUndefinedServiceClass()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency import service \'test\' is not defined');

        $this->_importConfig->expects(
            $this->once()
        )->method(
            'getServiceClass'
        )->with(
            'test'
        )->willReturn(
            null
        );
        $this->_objectManager->expects($this->never())->method('create');
        $this->_model->create('test');
    }

    /**
     */
    public function testCreateIrrelevantServiceClass()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Class \'stdClass\' has to implement \\Magento\\Directory\\Model\\Currency\\Import\\ImportInterface');

        $this->_importConfig->expects(
            $this->once()
        )->method(
            'getServiceClass'
        )->with(
            'test'
        )->willReturn(
            'stdClass'
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'stdClass'
        )->willReturn(
            new \stdClass()
        );
        $this->_model->create('test');
    }
}
