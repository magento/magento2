<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\DB\Transaction;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Theme\Api\Data\DesignConfigDataInterface;
use Magento\Theme\Api\Data\DesignConfigExtensionInterface;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Model\Data\Design\ConfigFactory;
use Magento\Theme\Model\Design\BackendModelFactory;
use Magento\Theme\Model\Design\Config\Storage;
use Magento\Theme\Model\Design\Config\ValueChecker;
use Magento\Theme\Model\Design\Config\ValueProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorageTest extends TestCase
{
    use MockCreationTrait;

    /** @var Storage */
    protected $model;

    /** @var TransactionFactory|MockObject */
    protected $transactionFactoryMock;

    /** @var BackendModelFactory|MockObject */
    protected $backendModelFactoryMock;

    /** @var ValueChecker|MockObject */
    protected $valueCheckerMock;

    /** @var Transaction|MockObject */
    protected $transactionMock;

    /** @var Value|MockObject */
    protected $backendModelMock;

    /** @var DesignConfigInterface|MockObject */
    protected $designConfigMock;

    /** @var DesignConfigExtensionInterface|MockObject */
    protected $extensionAttributes;

    /** @var DesignConfigDataInterface|MockObject */
    protected $designConfigData;

    /** @var ConfigFactory|MockObject */
    protected $configFactory;

    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfig;

    /** @var ValueProcessor|MockObject */
    protected $valueProcessor;

    /**
     * @var DesignConfigInterface|MockObject
     */
    protected $designConfig;

    /**
     * @var DesignConfigExtensionInterface|MockObject
     */
    protected $designConfigExtension;

    protected function setUp(): void
    {
        $this->transactionFactoryMock = $this->createPartialMock(
            TransactionFactory::class,
            ['create']
        );
        $this->transactionMock = $this->createMock(Transaction::class);
        $this->backendModelFactoryMock = $this->createMock(BackendModelFactory::class);
        $this->backendModelMock = $this->createMock(Value::class);
        $this->valueCheckerMock = $this->createMock(ValueChecker::class);
        $this->designConfigMock = $this->createMock(DesignConfigInterface::class);
        $this->extensionAttributes = $this->createPartialMockWithReflection(
            DesignConfigExtensionInterface::class,
            ['getDesignConfigData', 'setDesignConfigData']
        );
        $this->designConfigData = $this->createMock(DesignConfigDataInterface::class);
        $this->configFactory = $this->createMock(ConfigFactory::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->valueProcessor = $this->createMock(ValueProcessor::class);
        $this->designConfig = $this->createMock(DesignConfigInterface::class);
        $this->designConfigExtension = $this->createPartialMockWithReflection(
            DesignConfigExtensionInterface::class,
            ['getDesignConfigData']
        );

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
        $backendModel = $this->getMockBuilder(Value::class)
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
