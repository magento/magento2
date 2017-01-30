<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Theme\Model\Design\Config\Storage;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /** @var Storage */
    protected $model;

    /** @var \Magento\Framework\DB\TransactionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $transactionFactoryMock;

    /** @var \Magento\Theme\Model\Design\BackendModelFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModelFactoryMock;

    /** @var \Magento\Theme\Model\Design\Config\ValueChecker|\PHPUnit_Framework_MockObject_MockObject */
    protected $valueCheckerMock;

    /** @var \Magento\Framework\DB\Transaction|\PHPUnit_Framework_MockObject_MockObject */
    protected $transactionMock;

    /** @var \Magento\Framework\App\Config\Value|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModelMock;

    /** @var \Magento\Theme\Api\Data\DesignConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfigMock;

    /** @var \Magento\Theme\Api\Data\DesignConfigExtensionInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $extensionAttributes;

    /** @var \Magento\Theme\Api\Data\DesignConfigDataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfigData;

    /** @var \Magento\Theme\Model\Data\Design\ConfigFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $configFactory;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Theme\Model\Design\Config\ValueProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $valueProcessor;

    /**
     * @var \Magento\Theme\Api\Data\DesignConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designConfig;

    /**
     * @var \Magento\Theme\Api\Data\DesignConfigExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designConfigExtension;

    protected function setUp()
    {
        $this->transactionFactoryMock = $this->getMockBuilder('Magento\Framework\DB\TransactionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->transactionMock = $this->getMockBuilder('Magento\Framework\DB\Transaction')
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModelFactoryMock = $this->getMockBuilder('Magento\Theme\Model\Design\BackendModelFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModelMock = $this->getMockBuilder('Magento\Framework\App\Config\Value')
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueCheckerMock = $this->getMockBuilder('Magento\Theme\Model\Design\Config\ValueChecker')
            ->disableOriginalConstructor()
            ->getMock();
        $this->designConfigMock = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributes = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigExtensionInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getDesignConfigData', 'setDesignConfigData'])
            ->getMock();
        $this->designConfigData = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigDataInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configFactory = $this->getMockBuilder('Magento\Theme\Model\Data\Design\ConfigFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();
        $this->valueProcessor = $this->getMockBuilder('Magento\Theme\Model\Design\Config\ValueProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->designConfig = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigInterface')
            ->getMockForAbstractClass();
        $this->designConfigExtension = $this->getMockBuilder('Magento\Theme\Api\Data\DesignConfigExtensionInterface')
            ->setMethods(['getDesignConfigData'])
            ->getMockForAbstractClass();

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
        $backendModel = $this->getMockBuilder('Magento\Framework\App\Config\Value')
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
