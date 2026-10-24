<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Unit\Console;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Mview\TriggerCleaner;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\Patch\PatchApplier;
use Magento\Framework\Setup\Patch\PatchApplierFactory;
use Magento\Framework\Setup\SchemaListener;
use Magento\Framework\Setup\SchemaPersistor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MessageQueue\Setup\Recurring;
use Magento\Setup\Model\DeclarationInstaller;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Module\SetupFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PoisonPillApplyDuringSetupUpgradeTest extends TestCase
{
    /**
     * @var Installer
     */
    private $installer;
    /**
     * @var object
     */
    private $objectManagerProvider;
    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|MockObject
     */
    private $objectManagerMock;
    /**
     * @var object
     */
    private $registry;
    /**
     * @var MockObject
     */
    private $deploymentConfig;
    /**
     * @var ModuleContextInterface|mixed|MockObject
     */
    private $schemaSetupInterface;
    /**
     * @var SetupFactory|mixed|MockObject
     */
    private $setupFactory;
    /**
     * @var AdapterInterface|mixed|MockObject
     */
    private $adapterInterface;
    /**
     * @var object
     */
    private $resourceConnection;
    /**
     * @var object
     */
    private $declarationInstaller;
    /**
     * @var object
     */
    private $schemaPersistor;
    /**
     * @var object
     */
    private $triggerCleaner;
    /**
     * @var object
     */
    private $moduleListInterface;
    /**
     * @var object
     */
    private $schemaListener;
    /**
     * @var object
     */
    private $patchApplierFactory;
    /**
     * @var object
     */
    private $patchApplier;
    /**
     * @var object
     */
    private $recurring;
    /**
     * @var PoisonPillPutInterface|MockObject
     */
    private $poisonPillPut;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->registry = $objectManager->getObject(Registry::class);
        $this->moduleListInterface = $this->createMock(ModuleListInterface::class);
        $this->moduleListInterface->method('getNames')->willReturn(['Magento_MessageQueue']);
        $this->moduleListInterface->method('getOne')->with('Magento_MessageQueue')->willReturn(['setup_version'=>'']);
        $this->declarationInstaller = $this->createMock(DeclarationInstaller::class);
        $this->declarationInstaller->method('installSchema')->willReturn(true);
        $this->schemaListener = $this->createMock(SchemaListener::class);
        $this->schemaPersistor = $objectManager->getObject(SchemaPersistor::class);
        $this->triggerCleaner = $objectManager->getObject(TriggerCleaner::class);
        $this->patchApplierFactory = $this->createMock(PatchApplierFactory::class);
        $this->patchApplier = $this->createMock(PatchApplier::class);
        $this->patchApplier->method('applySchemaPatch')->willReturn(true);
        $this->patchApplierFactory->method('create')->willReturn($this->patchApplier);
        $this->objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->deploymentConfig->method('get')->willReturn(['host'=>'localhost', 'dbname' => 'magento']);
        $this->objectManagerMock->method('get')
        ->willReturnCallback(fn($param) => match ([$param]) {
            [SchemaPersistor::class] => $this->schemaPersistor,
            [TriggerCleaner::class] => $this->triggerCleaner,
            [Registry::class] => $this->registry,
            [DeclarationInstaller::class] => $this->declarationInstaller
        });

        $this->poisonPillPut = $this->createMock(\Magento\MessageQueue\Model\ResourceModel\PoisonPill::class);
        $this->recurring = new Recurring($this->poisonPillPut);

        $this->objectManagerMock->method('create')
        ->willReturnCallback(fn($param) => match ([$param]) {
            [PatchApplierFactory::class] => $this->patchApplierFactory,
            [Recurring::class] => $this->recurring
        });

        $this->objectManagerProvider->method('get')->willReturn($this->objectManagerMock);
        $this->adapterInterface = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->adapterInterface->method('isTableExists')->willReturn(true);
        $this->adapterInterface->method('getTables')->willReturn([]);
        $this->adapterInterface->method('getSchemaListener')->willReturn($this->schemaListener);
        $this->adapterInterface->method('describeTable')->willReturn(['flag_data'=>['DATA_TYPE'=>'mediumtext']]);
        $this->resourceConnection = $objectManager->getObject(\Magento\Framework\App\ResourceConnection::class);
        $this->schemaSetupInterface = $this->createMock(\Magento\Framework\Setup\SchemaSetupInterface::class);
        $this->schemaSetupInterface->method('getConnection')->willReturn($this->adapterInterface);
        $this->schemaSetupInterface
            ->method('getTable')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['setup_module'] => 'setup_module',
                ['session'] => 'session',
                ['cache'] => 'cache',
                ['cache_tag'] => 'cache_tag',
                ['flag'] => 'flag'
            });
        $this->setupFactory = $this->createMock(SetupFactory::class);
        $this->setupFactory->method('create')->willReturn($this->schemaSetupInterface);

        try {
            AppObjectManager::getInstance();
        } catch (\RuntimeException) {
            // Installer class creates instance of DeploymentConfig in the constructor
            // so the object manager should be defined.
            $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
            $objectManagerMock->method('get')
                ->willReturnCallback(fn ($type) => $this->createMock($type));
            AppObjectManager::setInstance($objectManagerMock);
        }

        $this->installer = $objectManager->getObject(
            Installer::class,
            [
                'objectManagerProvider' => $this->objectManagerProvider,
                'deploymentConfig'=>$this->deploymentConfig,
                'setupFactory'=>$this->setupFactory,
                'moduleList'=>$this->moduleListInterface,
            ]
        );
    }

    /**
     * @covers \Magento\MessageQueue\Setup\Recurring
     */
    public function testChangeVersion(): void
    {
        $this->poisonPillPut->expects(self::once())->method('put');
        $this->installer->installSchema(
            [
                'keep-generated'=>false,
                'convert-old-scripts'=>false,
                'help'=>false,
                'quiet'=>false,
                'verbose'=>false,
                'version'=>false,
                'ansi'=>false,
                'no-ansi'=>false,
                'no-interaction'=>false,
            ]
        );
    }
}
