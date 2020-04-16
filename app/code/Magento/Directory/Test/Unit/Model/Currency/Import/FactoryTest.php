<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Currency\Import;

use Magento\Directory\Model\Currency\Import\Config;
use Magento\Directory\Model\Currency\Import\Factory;
use Magento\Directory\Model\Currency\Import\ImportInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $_model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $_objectManager;

    /**
     * @var Config|MockObject
     */
    protected $_importConfig;

    protected function setUp(): void
    {
        $this->_objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->_importConfig = $this->createMock(Config::class);
        $this->_model = new Factory(
            $this->_objectManager,
            $this->_importConfig
        );
    }

    public function testCreate()
    {
        $expectedResult = $this->createMock(ImportInterface::class);
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

    public function testCreateUndefinedServiceClass()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Currency import service \'test\' is not defined');
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

    public function testCreateIrrelevantServiceClass()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage(
            'Class \'stdClass\' has to implement \Magento\Directory\Model\Currency\Import\ImportInterface'
        );
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
