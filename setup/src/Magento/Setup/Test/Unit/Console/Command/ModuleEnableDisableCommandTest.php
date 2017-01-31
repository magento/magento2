<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\ModuleDisableCommand;
use Magento\Setup\Console\Command\ModuleEnableCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModuleEnableDisableCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProviderMock;

    /**
     * @var \Magento\Framework\Module\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusMock;

    /**
     * @var \Magento\Framework\App\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\App\State\CleanupFiles|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanupFilesMock;

    /**
     * @var \Magento\Framework\Module\FullModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fullModuleListMock;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var \Magento\Framework\Code\GeneratedFiles|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generatedFiles;

    protected function setUp()
    {
        $this->objectManagerProviderMock = $this->getMock(
            'Magento\Setup\Model\ObjectManagerProvider',
            [],
            [],
            '',
            false
        );
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->objectManagerProviderMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($objectManager));
        $this->statusMock = $this->getMock('Magento\Framework\Module\Status', [], [], '', false);
        $this->cacheMock = $this->getMock('Magento\Framework\App\Cache', [], [], '', false);
        $this->cleanupFilesMock = $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $this->fullModuleListMock = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $this->deploymentConfigMock = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $this->generatedFiles = $this->getMock('\Magento\Framework\Code\GeneratedFiles', [], [], '', false);
        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                ['Magento\Framework\Module\Status', $this->statusMock],
                ['Magento\Framework\App\Cache', $this->cacheMock],
                ['Magento\Framework\App\State\CleanupFiles', $this->cleanupFilesMock],
                ['Magento\Framework\Module\FullModuleList', $this->fullModuleListMock],
            ]));
    }

    /**
     * @param bool $isEnable
     * @param bool $clearStaticContent
     * @param string $expectedMessage
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute($isEnable, $clearStaticContent, $expectedMessage)
    {
        $this->statusMock->expects($this->once())
            ->method('getModulesToChange')
            ->with($isEnable, ['Magento_Module1', 'Magento_Module2'])
            ->will($this->returnValue(['Magento_Module1']));
        $this->statusMock->expects($this->any())
            ->method('checkConstraints')
            ->will($this->returnValue([]));
        $this->statusMock->expects($this->once())
            ->method('setIsEnabled')
            ->with($isEnable, ['Magento_Module1']);
        $this->cacheMock->expects($this->once())
            ->method('clean');
        $this->cleanupFilesMock->expects($this->once())
            ->method('clearCodeGeneratedClasses');
        $this->cleanupFilesMock->expects($clearStaticContent ? $this->once() : $this->never())
            ->method('clearMaterializedViewFiles');
        $commandTester = $this->getCommandTester($isEnable);
        $input = ['module' => ['Magento_Module1', 'Magento_Module2']];
        if ($clearStaticContent) {
            $input['--clear-static-content'] = true;
        }
        $commandTester->execute($input);
        $display = $commandTester->getDisplay();
        $this->assertStringMatchesFormat($expectedMessage, $display);
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'enable, do not clear static content' => [
                true,
                false,
                '%amodules have been enabled%aMagento_Module1%a'
                    . "Info: Some modules might require static view files to be cleared. To do this, run "
                    . "'module:enable' with the --clear-static-content%a"
            ],
            'disable, do not clear static content' => [
                false,
                false,
                '%amodules have been disabled%aMagento_Module1%a'
                    . "Info: Some modules might require static view files to be cleared. To do this, run "
                    . "'module:disable' with the --clear-static-content%a"
            ],
            'enable, clear static content' => [
                true,
                true,
                '%amodules have been enabled%aMagento_Module1%aGenerated static view files cleared%a'
            ],
            'disable, clear static content' => [
                false,
                true,
                '%amodules have been disabled%aMagento_Module1%aGenerated static view files cleared%a'
            ]
        ];
    }

    public function testExecuteEnableInvalidModule()
    {
        $this->statusMock->expects($this->once())
            ->method('getModulesToChange')
            ->with(true, ['invalid'])
            ->willThrowException(new \LogicException('Unknown module(s): invalid'));
        $commandTester = $this->getCommandTester(true);
        $input = ['module' => ['invalid']];
        $commandTester->execute($input);
        $this->assertEquals('Unknown module(s): invalid' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testExecuteDisableInvalidModule()
    {
        $this->statusMock->expects($this->once())
            ->method('getModulesToChange')
            ->with(false, ['invalid'])
            ->willThrowException(new \LogicException('Unknown module(s): invalid'));
        $commandTester = $this->getCommandTester(false);
        $input = ['module' => ['invalid']];
        $commandTester->execute($input);
        $this->assertEquals('Unknown module(s): invalid' . PHP_EOL, $commandTester->getDisplay());
    }

    /**
     * @param bool $isEnable
     * @param string $expectedMessage
     *
     * @dataProvider executeAllDataProvider
     */
    public function testExecuteAll($isEnable, $expectedMessage)
    {
        $setupUpgradeMessage = 'To make sure that the enabled modules are properly registered, run \'setup:upgrade\'.';
        $this->fullModuleListMock->expects($this->once())
            ->method('getNames')
            ->will($this->returnValue(['Magento_Module1', 'Magento_Module2']));
        $this->statusMock->expects($this->once())
            ->method('getModulesToChange')
            ->with($isEnable, ['Magento_Module1', 'Magento_Module2'])
            ->will($this->returnValue(['Magento_Module1']));
        $this->statusMock->expects($this->any())
            ->method('checkConstraints')
            ->will($this->returnValue([]));
        $this->statusMock->expects($this->once())
            ->method('setIsEnabled')
            ->with($isEnable, ['Magento_Module1']);
        if ($isEnable) {
            $this->deploymentConfigMock->expects($this->once())
                ->method('isAvailable')
                ->willReturn(['Magento_Module1']);
        } else {
            $this->deploymentConfigMock->expects($this->never())
                ->method('isAvailable');
        }
        $commandTester = $this->getCommandTester($isEnable);
        $input = ['--all' => true];
        $commandTester->execute($input);
        $output = $commandTester->getDisplay();
        $this->assertStringMatchesFormat($expectedMessage, $output);
        if ($isEnable) {
            $this->assertContains($setupUpgradeMessage, $output);
        } else {
            $this->assertNotContains($setupUpgradeMessage, $output);
        }
    }

    /**
     * @return array
     */
    public function executeAllDataProvider()
    {
        return [
            'enable'  => [true, '%amodules have been enabled%aMagento_Module1%a'],
            'disable' => [false, '%amodules have been disabled%aMagento_Module1%a'],
        ];
    }

    /**
     * @param bool $isEnable
     *
     * @dataProvider executeWithConstraintsDataProvider
     */
    public function testExecuteWithConstraints($isEnable)
    {
        $this->statusMock->expects($this->once())
            ->method('getModulesToChange')
            ->with($isEnable, ['Magento_Module1', 'Magento_Module2'])
            ->will($this->returnValue(['Magento_Module1']));
        $this->statusMock->expects($this->any())
            ->method('checkConstraints')
            ->will($this->returnValue(['constraint1', 'constraint2']));
        $this->statusMock->expects($this->never())
            ->method('setIsEnabled');
        $commandTester = $this->getCommandTester($isEnable);
        $commandTester->execute(['module' => ['Magento_Module1', 'Magento_Module2']]);
        $this->assertStringMatchesFormat(
            'Unable to change status of modules%aconstraint1%aconstraint2%a',
            $commandTester->getDisplay()
        );
    }

    /**
     * @return array
     */
    public function executeWithConstraintsDataProvider()
    {
        return [
            'enable'  => [true],
            'disable' => [false],
        ];
    }

    /**
     * @param bool $isEnable
     * @param string $expectedMessage
     *
     * @dataProvider executeExecuteForceDataProvider
     */
    public function testExecuteForce($isEnable, $expectedMessage)
    {
        $this->statusMock->expects($this->once())
            ->method('getModulesToChange')
            ->with($isEnable, ['Magento_Module1', 'Magento_Module2'])
            ->will($this->returnValue(['Magento_Module1']));
        $this->statusMock->expects($this->never())
            ->method('checkConstraints');
        $this->statusMock->expects($this->once())
            ->method('setIsEnabled')
            ->with($isEnable, ['Magento_Module1']);
        $commandTester = $this->getCommandTester($isEnable);
        $commandTester->execute(['module' => ['Magento_Module1', 'Magento_Module2'], '--force' => true]);
        $this->assertStringMatchesFormat(
            $expectedMessage . '%amodules might not function properly%a',
            $commandTester->getDisplay()
        );
    }

    /**
     * @return array
     */
    public function executeExecuteForceDataProvider()
    {
        return [
            'enable'  => [true, '%amodules have been enabled%aMagento_Module1%a'],
            'disable' => [false, '%amodules have been disabled%aMagento_Module1%a'],
        ];
    }

    /**
     * @param bool $isEnable
     *
     * @dataProvider executeWithConstraintsDataProvider
     */
    public function testExecuteNoChanges($isEnable)
    {
        $this->statusMock->expects($this->once())
            ->method('getModulesToChange')
            ->with($isEnable, ['Magento_Module1', 'Magento_Module2'])
            ->will($this->returnValue([]));
        $this->statusMock->expects($this->never())
            ->method('setIsEnabled');
        $commandTester = $this->getCommandTester($isEnable);
        $commandTester->execute(['module' => ['Magento_Module1', 'Magento_Module2']]);
        $this->assertStringMatchesFormat(
            'No modules were changed%a',
            $commandTester->getDisplay()
        );
    }

    /**
     * @param bool $isEnable
     * @return CommandTester
     */
    private function getCommandTester($isEnable)
    {
        $class = $isEnable ? ModuleEnableCommand::class : ModuleDisableCommand::class;
        $command = new $class($this->objectManagerProviderMock);
        $deploymentConfigProperty = new \ReflectionProperty($class, 'deploymentConfig');
        $deploymentConfigProperty->setAccessible(true);
        $deploymentConfigProperty->setValue($command, $this->deploymentConfigMock);
        $deploymentConfigProperty = new \ReflectionProperty($class, 'generatedFiles');
        $deploymentConfigProperty->setAccessible(true);
        $deploymentConfigProperty->setValue($command, $this->generatedFiles);
        return new CommandTester($command);
    }
}
