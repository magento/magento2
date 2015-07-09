<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Setup\Console\Command\ModuleUninstallCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ModuleUninstallCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Writer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writer;

    /**
     * @var \Magento\Framework\Module\FullModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fullModuleList;

    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var \Magento\Setup\Model\UninstallCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uninstallCollector;

    /**
     * @var \Magento\Framework\Module\PackageInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageInfo;

    /**
     * @var \Magento\Framework\Module\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleResource;

    /**
     * @var \Magento\Framework\Module\DependencyChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dependencyChecker;

    /**
     * @var \Magento\Setup\Module\DataSetup|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataSetup;

    /**
     * @var \Magento\Framework\App\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \Magento\Framework\App\State\CleanupFiles|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanupFiles;

    /**
     * @var \Magento\Setup\Module\Setup|\PHPUnit_Framework_MockObject_MockObject
     */
    private $setup;

    /**
     * @var \Magento\Framework\Setup\BackupRollback|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollback;

    /**
     * @var \Magento\Framework\Setup\BackupRollbackFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollbackFactory;

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loader;

    /**
     * @var \Magento\Framework\Composer\Remove|\PHPUnit_Framework_MockObject_MockObject
     */
    private $remove;

    /**
     * @var \Symfony\Component\Console\Helper\QuestionHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $question;

    /**
     * @var \Symfony\Component\Console\Helper\HelperSet|\PHPUnit_Framework_MockObject_MockObject
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function setUp()
    {
        $this->remove = $this->getMock('Magento\Framework\Composer\Remove', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->writer = $this->getMock('Magento\Framework\App\DeploymentConfig\Writer', [], [], '', false);
        $this->fullModuleList = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $this->uninstallCollector = $this->getMock('Magento\Setup\Model\UninstallCollector', [], [], '', false);
        $this->packageInfo = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $packageInfoFactory = $this->getMock('Magento\Framework\Module\PackageInfoFactory', [], [], '', false);
        $packageInfoFactory->expects($this->once())->method('create')->willReturn($this->packageInfo);
        $this->moduleResource = $this->getMock('Magento\Framework\Module\Resource', [], [], '', false);
        $this->dependencyChecker = $this->getMock('Magento\Framework\Module\DependencyChecker', [], [], '', false);
        $this->backupRollback = $this->getMock('Magento\Framework\Setup\BackupRollback', [], [], '', false);
        $this->backupRollbackFactory = $this->getMock(
            'Magento\Framework\Setup\BackupRollbackFactory',
            [],
            [],
            '',
            false
        );
        $this->backupRollbackFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->backupRollback);
        $this->dataSetup = $this->getMock('Magento\Setup\Module\DataSetup', [], [], '', false);
        $this->cache = $this->getMock('Magento\Framework\App\Cache', [], [], '', false);
        $this->cleanupFiles = $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $this->setup = $this->getMock('Magento\Setup\Module\Setup', [], [], '', false);
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($objectManager);
        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                ['Magento\Framework\Module\PackageInfoFactory', $packageInfoFactory],
                ['Magento\Framework\Module\Resource', $this->moduleResource],
                ['Magento\Framework\Module\DependencyChecker', $this->dependencyChecker],
                ['Magento\Setup\Module\DataSetup', $this->dataSetup],
                ['Magento\Framework\App\Cache', $this->cache],
                ['Magento\Framework\App\State\CleanupFiles', $this->cleanupFiles],
                ['Magento\Setup\Module\Setup', $this->setup],
                ['Magento\Framework\App\State', $this->getMock('Magento\Framework\App\State', [], [], '', false)],
                ['Magento\Framework\Setup\BackupRollbackFactory', $this->backupRollbackFactory],
            ]));
        $composer = $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false);
        $composer->expects($this->any())
            ->method('getRootRequiredPackages')
            ->willReturn(['magento/package-a', 'magento/package-b']);
        $this->loader = $this->getMock('Magento\Framework\Module\ModuleList\Loader', [], [], '', false);
        $this->command = new ModuleUninstallCommand(
            $composer,
            $this->deploymentConfig,
            $this->writer,
            $this->fullModuleList,
            $this->loader,
            $this->maintenanceMode,
            $objectManagerProvider,
            $this->remove,
            $this->uninstallCollector
        );
        $this->question = $this->getMock('Symfony\Component\Console\Helper\QuestionHelper', [], [], '', false);
        $this->question
            ->expects($this->any())
            ->method('ask')
            ->will($this->returnValue(true));
        $this->helperSet = $this->getMock('Symfony\Component\Console\Helper\HelperSet', [], [], '', false);
        $this->helperSet
            ->expects($this->any())
            ->method('get')
            ->with('question')
            ->will($this->returnValue($this->question));
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
            ->will($this->returnValueMap($packageInfoMap));
        $this->fullModuleList->expects($this->exactly(count($input['module'])))
            ->method('has')
            ->will($this->returnValueMap($fullModuleListMap));
        $this->tester->execute($input);
        foreach ($expect as $message) {
            $this->assertContains($message, $this->tester->getDisplay());
        }
    }

    /**
     * @return array
     */
    public function executeFailedValidationDataProvider()
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
            ->will($this->returnValueMap($packageMap));
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
            $this->assertContains($message, $this->tester->getDisplay());
        }
    }

    /**
     * @return array
     */
    public function executeFailedDependenciesDataProvider()
    {
        return [
            [
                ['Magento_A' => ['Magento_D' => ['Magento_D', 'Magento_A']]],
                ['module' => ['Magento_A']],
                [
                    "Cannot uninstall module 'Magento_A' because the following module(s) depend on it:" .
                    PHP_EOL .  "\tMagento_D"
                ]
            ],
            [
                ['Magento_A' => ['Magento_D' => ['Magento_D', 'Magento_A']]],
                ['module' => ['Magento_A', 'Magento_B']],
                [
                    "Cannot uninstall module 'Magento_A' because the following module(s) depend on it:" .
                    PHP_EOL .  "\tMagento_D"
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
                    PHP_EOL .  "\tMagento_D",
                    "Cannot uninstall module 'Magento_B' because the following module(s) depend on it:" .
                    PHP_EOL .  "\tMagento_E"
                ]
            ],
        ];
    }

    private function setUpExecute($input)
    {
        $this->setUpPassValidation();
        $this->remove->expects($this->once())->method('remove')->with(['magento/package-a', 'magento/package-b']);
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependenciesWhenDisableModules')
            ->willReturn(['Magento_A' => [], 'Magento_B' => []]);
        $this->dataSetup->expects($this->exactly(count($input['module'])))->method('deleteTableRow');
        $this->deploymentConfig->expects($this->once())
            ->method('getConfigData')
            ->with(ConfigOptionsListConstants::KEY_MODULES)
            ->willReturn(['Magento_A' => 1, 'Magento_B' => 1, 'Magento_C' => 0, 'Magento_D' => 1]);

        $this->loader->expects($this->once())
            ->method('load')
            ->with($input['module'])
            ->willReturn(['Magento_C' => [], 'Magento_D' => []]);
        $this->writer->expects($this->once())
            ->method('saveConfig')
            ->with(
                [
                    ConfigFilePool::APP_CONFIG =>
                        [ConfigOptionsListConstants::KEY_MODULES => ['Magento_C' => 0, 'Magento_D' => 1]]
                ]
            );
        $this->cache->expects($this->once())->method('clean');
        $this->cleanupFiles->expects($this->once())->method('clearCodeGeneratedClasses');

    }

    public function testExecute()
    {
        $input = ['module' => ['Magento_A', 'Magento_B']];
        $this->setUpExecute($input);
        $this->tester->execute($input);
    }

    public function testExecuteClearStaticContent()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '-c' => true];
        $this->setUpExecute($input);
        $this->cleanupFiles->expects($this->once())->method('clearMaterializedViewFiles');
        $this->tester->execute($input);
    }

    public function testExecuteRemoveData()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '-r' => true];
        $this->setUpExecute($input);
        $uninstallMock = $this->getMockForAbstractClass('Magento\Framework\Setup\UninstallInterface', [], '', false);
        $uninstallMock->expects($this->once())
            ->method('uninstall')
            ->with($this->setup, $this->isInstanceOf('Magento\Setup\Model\ModuleContext'));
        $this->uninstallCollector->expects($this->once())
            ->method('collectUninstall')
            ->willReturn(['Magento_A' => $uninstallMock]);
        $this->tester->execute($input);
    }

    public function testExecuteAll()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '-c' => true, '-r' => true];
        $this->setUpExecute($input);
        $this->cleanupFiles->expects($this->once())->method('clearMaterializedViewFiles');
        $uninstallMock = $this->getMockForAbstractClass('Magento\Framework\Setup\UninstallInterface', [], '', false);
        $uninstallMock->expects($this->once())
            ->method('uninstall')
            ->with($this->setup, $this->isInstanceOf('Magento\Setup\Model\ModuleContext'));
        $this->uninstallCollector->expects($this->once())
            ->method('collectUninstall')
            ->willReturn(['Magento_A' => $uninstallMock]);
        $this->tester->execute($input);
    }

    public function testExecuteCodeBackup()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '--backup-code' => true];
        $this->setUpExecute($input);
        $this->backupRollback->expects($this->once())
            ->method('codeBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute($input);
    }

    public function testExecuteMediaBackup()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '--backup-media' => true];
        $this->setUpExecute($input);
        $this->backupRollback->expects($this->once())
            ->method('codeBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute($input);
    }

    public function testExecuteDBBackup()
    {
        $input = ['module' => ['Magento_A', 'Magento_B'], '--backup-db' => true];
        $this->setUpExecute($input);
        $this->backupRollback->expects($this->once())
            ->method('dbBackup')
            ->willReturn($this->backupRollback);
        $this->tester->execute($input);
    }

    public function testInteraction()
    {
        $input = ['module' => ['Magento_A', 'Magento_B']];
        $this->setUpExecute($input);
        $this->question
            ->expects($this->once())
            ->method('ask')
            ->will($this->returnValue(false));
        $this->helperSet
            ->expects($this->once())
            ->method('get')
            ->with('question')
            ->will($this->returnValue($this->question));
        $this->command->setHelperSet($this->helperSet);
        $this->tester = new CommandTester($this->command);
        $this->tester->execute($input);
    }
}
