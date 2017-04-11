<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigShow;

use Magento\Config\Console\Command\ConfigShow\ValueProcessor;
use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Config\ScopeInterface;

/**
 * Test for ValueProcessor.
 *
 * @see ValueProcessor
 */
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
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsGetValue
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsSetPath
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsSetScope
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsSetScopeId
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsSetValue
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $expectsAfterLoad
     * @param string $expectsValue
     * @param string $className
     * @param string $value
     * @dataProvider processDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testProcess(
        $hasBackendModel,
        $expectsGetBackendModel,
        $expectsCreate,
        $expectsGetValue,
        $expectsSetPath,
        $expectsSetScope,
        $expectsSetScopeId,
        $expectsSetValue,
        $expectsAfterLoad,
        $expectsValue,
        $className,
        $value
    ) {
        $scope = 'someScope';
        $scopeCode = 'someScopeCode';
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

        /** @var Value|Encrypted|\PHPUnit_Framework_MockObject_MockObject $valueMock */
        $backendModelMock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods(['setPath', 'setScope', 'setScopeId', 'setValue', 'getValue', 'afterLoad'])
            ->getMock();
        $backendModelMock->expects($expectsSetPath)
            ->method('setPath')
            ->with($path)
            ->willReturnSelf();
        $backendModelMock->expects($expectsSetScope)
            ->method('setScope')
            ->with($scope)
            ->willReturnSelf();
        $backendModelMock->expects($expectsSetScopeId)
            ->method('setScopeId')
            ->with($scopeCode)
            ->willReturnSelf();
        $backendModelMock->expects($expectsSetValue)
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();
        $backendModelMock->expects($expectsAfterLoad)
            ->method('afterLoad')
            ->willReturnSelf();
        $backendModelMock->expects($expectsGetValue)
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

        $this->assertSame($expectsValue, $this->valueProcessor->process($scope, $scopeCode, $value, $path));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function processDataProvider()
    {
        return [
            [
                'hasBackendModel' => true,
                'expectsGetBackendModel' => $this->once(),
                'expectsCreate' => $this->never(),
                'expectsGetValue' => $this->once(),
                'expectsSetPath' => $this->once(),
                'expectsSetScope' => $this->once(),
                'expectsSetScopeId' => $this->once(),
                'expectsSetValue' => $this->once(),
                'expectsAfterLoad' => $this->once(),
                'expectsValue' => 'someValue',
                'className' => Value::class,
                'value' => 'someValue'
            ],
            [
                'hasBackendModel' => false,
                'expectsGetBackendModel' => $this->never(),
                'expectsCreate' => $this->once(),
                'expectsGetValue' => $this->once(),
                'expectsSetPath' => $this->once(),
                'expectsSetScope' => $this->once(),
                'expectsSetScopeId' => $this->once(),
                'expectsSetValue' => $this->once(),
                'expectsAfterLoad' => $this->once(),
                'expectsValue' => 'someValue',
                'className' => Value::class,
                'value' => 'someValue'
            ],
            [
                'hasBackendModel' => true,
                'expectsGetBackendModel' => $this->once(),
                'expectsCreate' => $this->never(),
                'expectsGetValue' => $this->never(),
                'expectsSetPath' => $this->never(),
                'expectsSetScope' => $this->never(),
                'expectsSetScopeId' => $this->never(),
                'expectsSetValue' => $this->never(),
                'expectsAfterLoad' => $this->never(),
                'expectsValue' => ValueProcessor::SAFE_PLACEHOLDER,
                'className' => Encrypted::class,
                'value' => 'someValue'
            ],
            [
                'hasBackendModel' => true,
                'expectsGetBackendModel' => $this->once(),
                'expectsCreate' => $this->never(),
                'expectsGetValue' => $this->once(),
                'expectsSetPath' => $this->once(),
                'expectsSetScope' => $this->once(),
                'expectsSetScopeId' => $this->once(),
                'expectsSetValue' => $this->once(),
                'expectsAfterLoad' => $this->once(),
                'expectsValue' => null,
                'className' => Value::class,
                'value' => null
            ],
            [
                'hasBackendModel' => true,
                'expectsGetBackendModel' => $this->once(),
                'expectsCreate' => $this->never(),
                'expectsGetValue' => $this->never(),
                'expectsSetPath' => $this->never(),
                'expectsSetScope' => $this->never(),
                'expectsSetScopeId' => $this->never(),
                'expectsSetValue' => $this->never(),
                'expectsAfterLoad' => $this->never(),
                'expectsValue' => null,
                'className' => Encrypted::class,
                'value' => null
            ],
        ];
    }
}
