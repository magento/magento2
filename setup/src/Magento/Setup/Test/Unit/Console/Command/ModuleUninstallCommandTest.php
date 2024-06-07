<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\Cache;
use Magento\Framework\App\Console\MaintenanceModeEnabler;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\App\State;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\DependencyChecker;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Framework\Setup\Patch\PatchApplier;
use Magento\Setup\Console\Command\ModuleUninstallCommand;
use Magento\Setup\Model\ModuleRegistryUninstaller;
use Magento\Setup\Model\ModuleUninstaller;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\UninstallCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ModuleUninstallCommandTest extends TestCase
{
    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var FullModuleList|MockObject
     */
    private $fullModuleList;

    /**
     * @var MaintenanceMode|MockObject
     */
    private $maintenanceMode;

    /**
     * @var UninstallCollector|MockObject
     */
    private $uninstallCollector;

    /**
     * @var PackageInfo|MockObject
     */
    private $packageInfo;

    /**
     * @var DependencyChecker|MockObject
     */
    private $dependencyChecker;

    /**
     * @var ModuleUninstaller|MockObject
     */
    private $moduleUninstaller;

    /**
     * @var ModuleRegistryUninstaller|MockObject
     */
    private $moduleRegistryUninstaller;

    /**
     * @var Cache|MockObject
     */
    private $cache;

    /**
     * @var CleanupFiles|MockObject
     */
    private $cleanupFiles;

    /**
     * @var BackupRollback|MockObject
     */
    private $backupRollback;

    /**
     * @var BackupRollbackFactory|MockObject
     */
    private $backupRollbackFactory;

    /**
     * @var QuestionHelper|MockObject
     */
    private $question;

    /**
     * @var HelperSet|MockObject
     */
    private $helperSet;

    /**
     * @var ModuleUninstallCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var MockObject
     */
    private $patchApplierMock;

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->fullModuleList = $this->createMock(FullModuleList::class);
        $this->maintenanceMode = $this->createMock(MaintenanceMode::class);
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $this->uninstallCollector = $this->createMock(UninstallCollector::class);
        $this->packageInfo = $this->createMock(PackageInfo::class);
        $packageInfoFactory = $this->createMock(PackageInfoFactory::class);
        $packageInfoFactory->expects($this->once())->method('create')->willReturn($this->packageInfo);
        $this->dependencyChecker = $this->createMock(DependencyChecker::class);
        $this->backupRollback = $this->createMock(BackupRollback::class);
        $this->backupRollbackFactory = $this->createMock(BackupRollbackFactory::class);
        $this->backupRollbackFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->backupRollback);
        $this->cache = $this->createMock(Cache::class);
        $this->cleanupFiles = $this->createMock(CleanupFiles::class);
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($objectManager);
        $configLoader = $this->getMockForAbstractClass(
            ConfigLoaderInterface::class,
            [],
            '',
            false
        );
        $this->patchApplierMock = $this->getMockBuilder(PatchApplier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configLoader->expects($this->any())->method('load')->willReturn([]);
        $objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [PackageInfoFactory::class, $packageInfoFactory],
                [DependencyChecker::class, $this->dependencyChecker],
                [Cache::class, $this->cache],
                [CleanupFiles::class, $this->cleanupFiles],
                [
                    State::class,
                    $this->createMock(State::class)
                ],
                [BackupRollbackFactory::class, $this->backupRollbackFactory],
                [PatchApplier::class, $this->patchApplierMock],
                [ConfigLoaderInterface::class, $configLoader],
            ]);
        $composer = $this->createMock(ComposerInformation::class);
        $composer->expects($this->any())
            ->method('getRootRequiredPackages')
            ->willReturn(['magento/package-a', 'magento/package-b']);
        $this->moduleUninstaller = $this->createMock(ModuleUninstaller::class);
        $this->moduleRegistryUninstaller = $this->createMock(ModuleRegistryUninstaller::class);
        $this->command = new ModuleUninstallCommand(
            $composer,
            $this->deploymentConfig,
            $this->fullModuleList,
            $this->maintenanceMode,
            $objectManagerProvider,
            $this->uninstallCollector,
            $this->moduleUninstaller,
            $this->moduleRegistryUninstaller,
            new MaintenanceModeEnabler($this->maintenanceMode)
        );
        $this->question = $this->createMock(QuestionHelper::class);
        $this->question
            ->expects($this->any())
            ->method('ask')
            ->willReturn(true);
        $this->helperSet = $this->createMock(HelperSet::class);
        $this->helperSet
            ->expects($this->any())
            ->method('get')
            ->with('question')
            ->willReturn($this->question);
        $this->command->setHelperSet($this->helperSet);
        $this->tester = new CommandTester($this->command);
    }

    public function testExecuteApplicationNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->tester->execute(['module' => ['Magento_A']]);
        $this->assertEquals(
            'You cannot run this command because the Magento application is not installed.' . PHP_EOL,
            $this->tester->getDisplay()
        );
    }

    /**
     * @dataProvider executeFailedValidationDataProvider
     * @param array $packageInfoMap
     * @param array $fullModuleListMap
     * @param array $input
     * @param array $expect
     */
    public function testExecuteFailedValidation(
        array $packageInfoMap,
        array $fullModuleListMap,
        array $input,
        array $expect
    ) {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->packageInfo->expects($this->exactly(count($input['module'])))
            ->method('getPackageName')
            ->willReturnMap($packageInfoMap);
        $this->fullModuleList->expects($this->exactly(count($input['module'])))
            ->method('has')
            ->willReturnMap($fullModuleListMap);
        $this->tester->execute($input);
        foreach ($expect as $message) {
            $this->assertStringContainsString($message, $this->tester->getDisplay());
        }
    }

    /**
     * @return array
     */
    public static function executeFailedValidationDataProvider()
    {
        return [
            'one non-composer package' => [
                [['Magento_C', 'magento/package-c']],
                [['Magento_C', true]],
                ['module' => ['Magento_C']],
                ['Magento_C is not an installed composer package']
            ],
            'one non-composer package, one valid' => [
                [['Magento_A', 'magento/package-a'], ['Magento_C', 'magento/package-c']],
                [['Magento_A', true], ['Magento_C', true]],
                ['module' => ['Magento_A', 'Magento_C']],
                ['Magento_C is not an installed composer package']
            ],
            'two non-composer packages' => [
                [['Magento_C', 'magento/package-c'], ['Magento_D', 'magento/package-d']],
                [['Magento_C', true], ['Magento_D', true]],
                ['module' => ['Magento_C', 'Magento_D']],
                ['Magento_C, Magento_D are not installed composer packages']
            ],
            'one unknown module' => [
                [['Magento_C', '']],
                [['Magento_C', false]],
                ['module' => ['Magento_C']],
                ['Unknown module(s): Magento_C']
            ],
            'two unknown modules' => [
                [['Magento_C', ''], ['Magento_D', '']],
                [['Magento_C', false], ['Magento_D', false]],
                ['module' => ['Magento_C', 'Magento_D']],
                ['Unknown module(s): Magento_C, Magento_D']
            ],
            'one unknown module, one valid' => [
                [['Magento_C', ''], ['Magento_B', 'magento/package-b']],
                [['Magento_C', false], ['Magento_B', true]],
                ['module' => ['Magento_C', 'Magento_B']],
                ['Unknown module(s): Magento_C']
            ],
            'one non-composer package, one unknown module' => [
                [['Magento_C', 'magento/package-c'], ['Magento_D', '']],
                [['Magento_C', true], ['Magento_D', false]],
                ['module' => ['Magento_C', 'Magento_D']],
                ['Magento_C is not an installed composer package', 'Unknown module(s): Magento_D']
            ],
            'two non-composer package, one unknown module' => [
                [['Magento_C', 'magento/package-c'], ['Magento_D', ''], ['Magento_E', 'magento/package-e']],
                [['Magento_C', true], ['Magento_D', false], ['Magento_E', true]],
                ['module' => ['Magento_C', 'Magento_D', 'Magento_E']],
                ['Magento_C, Magento_E are not installed composer packages', 'Unknown module(s): Magento_D']
            ],
            'two non-composer package, two unknown module' => [
                [
                    ['Magento_C', 'magento/package-c'],
                    ['Magento_D', ''],
                    ['Magento_E', 'magento/package-e'],
                    ['Magento_F', '']
                ],
                [['Magento_C', true], ['Magento_D', false], ['Magento_E', true], ['Magento_F', false]],
                ['module' => ['Magento_C', 'Magento_D', 'Magento_E', 'Magento_F']],
                ['Magento_C, Magento_E are not installed composer packages', 'Unknown module(s): Magento_D, Magento_F']
            ],
            'two non-composer package, two unknown module, two valid' => [
                [
                    ['Magento_C', 'magento/package-c'],
                    ['Magento_D', ''],
                    ['Magento_E', 'magento/package-e'],
                    ['Magento_F', ''],
                    ['Magento_A', 'magento/package-a'],
                    ['Magento_B', 'magento/package-b'],
                ],
                [
                    ['Magento_A', true],
                    ['Magento_B', true],
                    ['Magento_C', true],
                    ['Magento_D', false],
                    ['Magento_E', true],
                    ['Magento_F', false]
                ],
                ['module' => ['Magento_A', 'Magento_B', 'Magento_C', 'Magento_D', 'Magento_E', 'Magento_F']],
                ['Magento_C, Magento_E are not installed composer packages', 'Unknown module(s): Magento_D, Magento_F']
            ]
        ];
    }

    private function setUpPassValidation()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $packageMap = [
            ['Magento_A', 'magento/package-a'],
            ['Magento_B', 'magento/package-b'],
        ];
        $this->packageInfo->expects($this->any())
            ->method('getPackageName')
            ->willReturnMap($packageMap);
        $this->fullModuleList->expects($this->any())
            ->method('has')
            ->willReturn(true);
    }

    /**
     * @dataProvider executeFailedDependenciesDataProvider
     * @param array $dependencies
     * @param array $input
     * @param array $expect
     */
    public function testExecuteFailedDependencies(
        array $dependencies,
        array $input,
        array $expect
    ) {
        $this->setUpPassValidation();
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenDisableModules')
            ->willReturn($dependencies);
        $this->tester->execute($input);
        foreach ($expect as $message) {
            $this->assertStringContainsString($message, $this->tester->getDisplay());
        }
    }

    /**
     * @return array
     */
    public static function executeFailedDependenciesDataProvider()
    {
        return [
            [
                ['Magento_A' => ['Magento_D' => ['Magento_D', 'Magento_A']]],
                ['module' => ['Magento_A']],
                [
                    "Cannot uninstall module 'Magento_A' because the following module(s) depend on it:" .
                    PHP_EOL . "\tMagento_D"
                ]
            ],
            [
                ['Magento_A' => ['Magento_D' => ['Magento_D', 'Magento_A']]],
                ['module' => ['Magento_A', 'Magento_B']],
                [
                    "Cannot uninstall module 'Magento_A' because the following module(s) depend on it:" .
                    PHP_EOL . "\tMagento_D"
                ]
            ],
            [
                [
                    'Magento_A' => ['Magento_D' => ['Magento_D', 'Magento_A']],
                    'Magento_B' => ['Magento_E' => ['Magento_E', 'Magento_A']]
                ],
                ['module' => ['Magento_A', 'Magento_B']],
                [
                    "Cannot uninstall module 'Magento_A' because the following module(s) depend on it:" .
                    PHP_EOL . "\tMagento_D",
                    "Cannot uninstall module 'Magento_B' because the following module(s) depend on it:" .
                    PHP_EOL . "\tMagento_E"
                ]
            ],
        ];
    }

    private function setUpExecute()
    {
        $this->setUpPassValidation();
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenDisableModules')
            ->willReturn(['Magento_A' => [], 'Magento_B' => []]);
        $this->cache->expects($this->once())->method('clean');
        $this->cleanupFiles->expects($this->once())->method('clearCodeGeneratedClasses');
    }

    public function testExecute()
    {
        $input = ['module' => ['Magento_A', 'Magento_B']];
        $this->setUpExecute();
        $this->moduleUninstaller->expects($this->once())
            ->method('uninstallCode')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDb')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->tester->execute($input);
    }

    public function testExecuteClearStaticContent()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '-c' => true];
        $this->setUpExecute();
        $this->moduleUninstaller->expects($this->once())
            ->method('uninstallCode')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDb')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->cleanupFiles->expects($this->once())->method('clearMaterializedViewFiles');
        $this->tester->execute($input);
    }

    public function testExecuteRemoveData()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '-r' => true];
        $this->setUpExecute();
        $this->moduleUninstaller->expects($this->once())
            ->method('uninstallData')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleUninstaller->expects($this->once())
            ->method('uninstallCode')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDb')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->tester->execute($input);
    }

    public function testExecuteNonComposerModules()
    {
        $this->deploymentConfig->expects(self::once())
            ->method('isAvailable')
            ->willReturn(true);
        $input = ['module' => ['Magento_A'], '-c' => true, '-r' => true, '--non-composer' => true];
        $this->patchApplierMock->expects(self::once())
            ->method('revertDataPatches')
            ->with('Magento_A');
        self::assertEquals(0, $this->tester->execute($input));
    }

    public function testExecuteAll()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '-c' => true, '-r' => true];
        $this->setUpExecute();
        $this->moduleUninstaller->expects($this->once())
            ->method('uninstallData')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleUninstaller->expects($this->once())
            ->method('uninstallCode')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDb')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->cleanupFiles->expects($this->once())->method('clearMaterializedViewFiles');
        $this->tester->execute($input);
    }

    public function testExecuteCodeBackup()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '--backup-code' => true];
        $this->setUpExecute();
        $this->moduleUninstaller->expects($this->once())
            ->method('uninstallCode')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDb')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->backupRollback->expects($this->once())
            ->method('codeBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute($input);
    }

    public function testExecuteMediaBackup()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '--backup-media' => true];
        $this->setUpExecute();
        $this->moduleUninstaller->expects($this->once())
            ->method('uninstallCode')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDb')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->backupRollback->expects($this->once())
            ->method('codeBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute($input);
    }

    public function testExecuteDBBackup()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '--backup-db' => true];
        $this->setUpExecute();
        $this->moduleUninstaller->expects($this->once())
            ->method('uninstallCode')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDb')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->backupRollback->expects($this->once())
            ->method('dbBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute($input);
    }

    public function testInteraction()
    {
        $input = ['module' => ['Magento_A', 'Magento_B']];
        $this->setUpExecute();
        $this->moduleUninstaller->expects($this->once())
            ->method('uninstallCode')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDb')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->moduleRegistryUninstaller->expects($this->once())
            ->method('removeModulesFromDeploymentConfig')
            ->with($this->isInstanceOf(OutputInterface::class), $input['module']);
        $this->question
            ->expects($this->once())
            ->method('ask')
            ->willReturn(false);
        $this->helperSet
            ->expects($this->once())
            ->method('get')
            ->with('question')
            ->willReturn($this->question);
        $this->command->setHelperSet($this->helperSet);
        $this->tester = new CommandTester($this->command);
        $this->tester->execute($input);
    }
}
