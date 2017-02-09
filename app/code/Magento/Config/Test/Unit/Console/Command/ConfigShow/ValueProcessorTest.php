<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigShow;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\App\Area;
use Magento\Config\Console\Command\ConfigShow\ValueProcessor;

class ValueProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $valueFactoryMock;

    /**
     * @var ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeMock;

    /**
     * @var StructureFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureFactoryMock;

    /**
     * @var ValueProcessor
     */
    private $valueProcessor;

    protected function setUp()
    {
        $this->valueFactoryMock = $this->getMockBuilder(ValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $this->structureFactoryMock = $this->getMockBuilder(StructureFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->valueProcessor = new ValueProcessor(
            $this->scopeMock,
            $this->structureFactoryMock,
            $this->valueFactoryMock
        );
    }

    /**
     * @param bool $hasBackendModel
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsGetBackendModel
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsCreate
     * @dataProvider processDataProvider
     */
    public function testProcess($hasBackendModel, $expectsGetBackendModel, $expectsCreate)
    {
        $scope = 'someScope';
        $scopeCode = 'someScopeCode';
        $value = 'someValue';
        $path = 'some/config/path';
        $oldConfigScope = 'oldConfigScope';

        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn($oldConfigScope);
        $this->scopeMock->expects($this->at(1))
            ->method('setCurrentScope')
            ->with(Area::AREA_ADMINHTML);
        $this->scopeMock->expects($this->at(2))
            ->method('setCurrentScope')
            ->with($oldConfigScope);

        /** @var Structure|\PHPUnit_Framework_MockObject_MockObject $structureMock */
        $structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($structureMock);

        /** @var Value|\PHPUnit_Framework_MockObject_MockObject $valueMock */
        $backendModelMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath', 'setScope', 'setScopeId', 'setValue', 'getValue', 'afterLoad'])
            ->getMock();
        $backendModelMock->expects($this->once())
            ->method('setPath')
            ->with($path)
            ->willReturnSelf();
        $backendModelMock->expects($this->once())
            ->method('setScope')
            ->with($scope)
            ->willReturnSelf();
        $backendModelMock->expects($this->once())
            ->method('setScopeId')
            ->with($scopeCode)
            ->willReturnSelf();
        $backendModelMock->expects($this->once())
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();
        $backendModelMock->expects($this->once())
            ->method('afterLoad')
            ->willReturnSelf();
        $backendModelMock->expects($this->once())
            ->method('getValue')
            ->willReturn($value);

        /** @var Field|\PHPUnit_Framework_MockObject_MockObject $fieldMock */
        $fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn($hasBackendModel);
        $fieldMock->expects($expectsGetBackendModel)
            ->method('getBackendModel')
            ->willReturn($backendModelMock);
        $this->valueFactoryMock->expects($expectsCreate)
            ->method('create')
            ->willReturn($backendModelMock);

        $structureMock->expects($this->once())
            ->method('getElement')
            ->with($path)
            ->willReturn($fieldMock);

        $this->assertSame($value, $this->valueProcessor->process($scope, $scopeCode, $value, $path));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            ['hasBackendModel' => true, 'expectsGetBackendModel' => $this->once(), 'expectsCreate' => $this->never()],
            ['hasBackendModel' => false, 'expectsGetBackendModel' => $this->never(), 'expectsCreate' => $this->once()],
        ];
    }
}
