<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Theme\Model\Design\Config\Storage;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorageTest extends \PHPUnit\Framework\TestCase
{
    /** @var Storage */
    protected $model;

    /** @var \Magento\Framework\DB\TransactionFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $transactionFactoryMock;

    /** @var \Magento\Theme\Model\Design\BackendModelFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $backendModelFactoryMock;

    /** @var \Magento\Theme\Model\Design\Config\ValueChecker|\PHPUnit\Framework\MockObject\MockObject */
    protected $valueCheckerMock;

    /** @var \Magento\Framework\DB\Transaction|\PHPUnit\Framework\MockObject\MockObject */
    protected $transactionMock;

    /** @var \Magento\Framework\App\Config\Value|\PHPUnit\Framework\MockObject\MockObject */
    protected $backendModelMock;

    /** @var \Magento\Theme\Api\Data\DesignConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $designConfigMock;

    /** @var \Magento\Theme\Api\Data\DesignConfigExtensionInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $extensionAttributes;

    /** @var \Magento\Theme\Api\Data\DesignConfigDataInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $designConfigData;

    /** @var \Magento\Theme\Model\Data\Design\ConfigFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $configFactory;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $scopeConfig;

    /** @var \Magento\Theme\Model\Design\Config\ValueProcessor|\PHPUnit\Framework\MockObject\MockObject */
    protected $valueProcessor;

    /**
     * @var \Magento\Theme\Api\Data\DesignConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $designConfig;

    /**
     * @var \Magento\Theme\Api\Data\DesignConfigExtensionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $designConfigExtension;

    protected function setUp(): void
    {
        $this->transactionFactoryMock = $this->getMockBuilder(\Magento\Framework\DB\TransactionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->transactionMock = $this->getMockBuilder(\Magento\Framework\DB\Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModelFactoryMock = $this->getMockBuilder(\Magento\Theme\Model\Design\BackendModelFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModelMock = $this->getMockBuilder(\Magento\Framework\App\Config\Value::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueCheckerMock = $this->getMockBuilder(\Magento\Theme\Model\Design\Config\ValueChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->designConfigMock = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributes = $this->getMockBuilder(
            \Magento\Theme\Api\Data\DesignConfigExtensionInterface::class
        )->disableOriginalConstructor()->setMethods(['getDesignConfigData', 'setDesignConfigData'])->getMock();
        $this->designConfigData = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigDataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configFactory = $this->getMockBuilder(\Magento\Theme\Model\Data\Design\ConfigFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->valueProcessor = $this->getMockBuilder(\Magento\Theme\Model\Design\Config\ValueProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->designConfig = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigInterface::class)
            ->getMockForAbstractClass();
        $this->designConfigExtension = $this->getMockBuilder(
            \Magento\Theme\Api\Data\DesignConfigExtensionInterface::class
        )->setMethods(['getDesignConfigData'])->getMockForAbstractClass();

        $this->model = new Storage(
            $this->transactionFactoryMock,
            $this->backendModelFactoryMock,
            $this->valueCheckerMock,
            $this->configFactory,
            $this->scopeConfig,
            $this->valueProcessor
        );
    }

    public function testSave()
    {
        $scope = 'website';
        $scopeId = 1;

        $this->transactionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->transactionMock);
        $this->designConfigMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$this->designConfigData]);
        $this->designConfigData->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('value');
        $this->designConfigMock->expects($this->exactly(2))
            ->method('getScope')
            ->willReturn($scope);
        $this->designConfigMock->expects($this->exactly(2))
            ->method('getScopeId')
            ->willReturn($scopeId);
        $this->designConfigData->expects($this->exactly(2))
            ->method('getFieldConfig')
            ->willReturn(['path' => 'design/head/default_title']);
        $this->backendModelFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with([
                'value' => 'value',
                'scope' => $scope,
                'scopeId' => $scopeId,
                'config' => ['path' => 'design/head/default_title']
            ])
            ->willReturn($this->backendModelMock);
        $this->valueCheckerMock->expects($this->once())
            ->method('isDifferentFromDefault')
            ->with('value', $scope, $scopeId, ['path' => 'design/head/default_title'])
            ->willReturn(true);
        $this->transactionMock->expects($this->once())
            ->method('addObject')
            ->with($this->backendModelMock);
        $this->transactionMock->expects($this->once())
            ->method('save');
        $this->transactionMock->expects($this->once())
            ->method('delete');
        $this->model->save($this->designConfigMock);
    }

    public function testLoad()
    {
        $scope = 'website';
        $scopeId = 1;

        $this->configFactory->expects($this->once())
            ->method('create')
            ->with($scope, $scopeId)
            ->willReturn($this->designConfig);
        $this->designConfig->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->designConfigExtension);
        $this->designConfigExtension->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$this->designConfigData]);
        $this->designConfigData->expects($this->atLeastOnce())
            ->method('getPath')
            ->willReturn('path');
        $this->designConfigData->expects($this->atLeastOnce())
            ->method('getFieldConfig')
            ->willReturn(['path' => 'path']);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('path', $scope, $scopeId)
            ->willReturn('value');
        $this->valueProcessor->expects($this->once())
            ->method('process')
            ->with('value', 'website', 1, ['path' => 'path'])
            ->willReturnArgument(0);
        $this->designConfigData->expects($this->once())
            ->method('setValue')
            ->with('value');
        $this->assertSame($this->designConfig, $this->model->load($scope, $scopeId));
    }

    public function testDelete()
    {
        $scope = 'website';
        $scopeId = 1;
        $backendModel = $this->getMockBuilder(\Magento\Framework\App\Config\Value::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->designConfig->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->designConfigExtension);
        $this->designConfigExtension->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$this->designConfigData]);
        $this->transactionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->transactionMock);

        $this->designConfigData->expects($this->once())
            ->method('getValue')
            ->willReturn('value');
        $this->designConfigData->expects($this->once())
            ->method('getFieldConfig')
            ->willReturn([]);
        $this->designConfig->expects($this->once())
            ->method('getScope')
            ->willReturn($scope);
        $this->designConfig->expects($this->once())
            ->method('getScopeId')
            ->willReturn($scopeId);
        $this->backendModelFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'value' => 'value',
                'scope' => $scope,
                'scopeId' => $scopeId,
                'config' => []
            ])
            ->willReturn($backendModel);
        $this->transactionMock->expects($this->once())
            ->method('addObject')
            ->with($backendModel);
        $this->transactionMock->expects($this->once())
            ->method('delete');
        $this->model->delete($this->designConfig);
    }
}
