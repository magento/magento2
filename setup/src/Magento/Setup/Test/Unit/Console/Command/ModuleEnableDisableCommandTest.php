<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\ModuleDisableCommand;
use Magento\Setup\Console\Command\ModuleEnableCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ModuleEnableDisableCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    /**
     * @var \Magento\Framework\Module\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    private $status;

    /**
     * @var \Magento\Framework\App\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \Magento\Framework\App\State\CleanupFiles|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanupFiles;

    /**
     * @var \Magento\Framework\Module\FullModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fullModuleList;

    protected function setUp()
    {
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->objectManagerProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($objectManager));
        $this->status = $this->getMock('Magento\Framework\Module\Status', [], [], '', false);
        $this->cache = $this->getMock('Magento\Framework\App\Cache', [], [], '', false);
        $this->cleanupFiles = $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $this->fullModuleList = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                ['Magento\Framework\Module\Status', $this->status],
                ['Magento\Framework\App\Cache', $this->cache],
                ['Magento\Framework\App\State\CleanupFiles', $this->cleanupFiles],
                ['Magento\Framework\Module\FullModuleList', $this->fullModuleList],
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
        $this->status->expects($this->once())
            ->method('getModulesToChange')
            ->with($isEnable, ['Magento_Module1', 'Magento_Module2'])
            ->will($this->returnValue(['Magento_Module1']));

        $this->status->expects($this->any())
            ->method('checkConstraints')
            ->will($this->returnValue([]));

        $this->status->expects($this->once())
            ->method('setIsEnabled')
            ->with($isEnable, ['Magento_Module1']);

        $this->cache->expects($this->once())
            ->method('clean');
        $this->cleanupFiles->expects($this->once())
            ->method('clearCodeGeneratedClasses');
        $this->cleanupFiles->expects($clearStaticContent ? $this->once() : $this->never())
            ->method('clearMaterializedViewFiles');

        $commandTester = $isEnable
            ? new CommandTester(new ModuleEnableCommand($this->objectManagerProvider))
            : new CommandTester(new ModuleDisableCommand($this->objectManagerProvider));
        $input = ['module' => ['Magento_Module1', 'Magento_Module2']];
        if ($clearStaticContent) {
            $input['--clear-static-content'] = true;
        }
        $commandTester->execute($input);
        $this->assertStringMatchesFormat($expectedMessage, $commandTester->getDisplay());
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
                '%amodules have been enabled%aMagento_Module1%a' .
                'Info: Some modules might require static view files to be cleared.%a'
            ],
            'disable, do not clear static content' => [
                false,
                false,
                '%amodules have been disabled%aMagento_Module1%a' .
                'Info: Some modules might require static view files to be cleared.%a'
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
            ],
        ];
    }

    public function testExecuteEnableInvalidModule()
    {
        $this->status->expects($this->once())
            ->method('getModulesToChange')
            ->with(true, ['invalid'])
            ->willThrowException(new \LogicException('Unknown module(s): invalid'));
        $commandTester = new CommandTester(new ModuleEnableCommand($this->objectManagerProvider));
        $input = ['module' => ['invalid']];
        $commandTester->execute($input);
        $this->assertEquals('Unknown module(s): invalid' . PHP_EOL, $commandTester->getDisplay());
    }

    public function testExecuteDisableInvalidModule()
    {
        $this->status->expects($this->once())
            ->method('getModulesToChange')
            ->with(false, ['invalid'])
            ->willThrowException(new \LogicException('Unknown module(s): invalid'));
        $commandTester = new CommandTester(new ModuleDisableCommand($this->objectManagerProvider));
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
        $this->fullModuleList->expects($this->once())
            ->method('getNames')
            ->will($this->returnValue(['Magento_Module1', 'Magento_Module2']));

        $this->status->expects($this->once())
            ->method('getModulesToChange')
            ->with($isEnable, ['Magento_Module1', 'Magento_Module2'])
            ->will($this->returnValue(['Magento_Module1']));

        $this->status->expects($this->any())
            ->method('checkConstraints')
            ->will($this->returnValue([]));

        $this->status->expects($this->once())
            ->method('setIsEnabled')
            ->with($isEnable, ['Magento_Module1']);

        $commandTester = $isEnable
            ? new CommandTester(new ModuleEnableCommand($this->objectManagerProvider))
            : new CommandTester(new ModuleDisableCommand($this->objectManagerProvider));
        $input = ['--all' => true];
        $commandTester->execute($input);
        $this->assertStringMatchesFormat($expectedMessage, $commandTester->getDisplay());
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
        $this->status->expects($this->once())
            ->method('getModulesToChange')
            ->with($isEnable, ['Magento_Module1', 'Magento_Module2'])
            ->will($this->returnValue(['Magento_Module1']));

        $this->status->expects($this->any())
            ->method('checkConstraints')
            ->will($this->returnValue(['constraint1', 'constraint2']));

        $this->status->expects($this->never())
            ->method('setIsEnabled');

        $commandTester = $isEnable
            ? new CommandTester(new ModuleEnableCommand($this->objectManagerProvider))
            : new CommandTester(new ModuleDisableCommand($this->objectManagerProvider));
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
        $this->status->expects($this->once())
            ->method('getModulesToChange')
            ->with($isEnable, ['Magento_Module1', 'Magento_Module2'])
            ->will($this->returnValue(['Magento_Module1']));

        $this->status->expects($this->never())
            ->method('checkConstraints');

        $this->status->expects($this->once())
            ->method('setIsEnabled')
            ->with($isEnable, ['Magento_Module1']);

        $commandTester = $isEnable
            ? new CommandTester(new ModuleEnableCommand($this->objectManagerProvider))
            : new CommandTester(new ModuleDisableCommand($this->objectManagerProvider));
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
        $this->status->expects($this->once())
            ->method('getModulesToChange')
            ->with($isEnable, ['Magento_Module1', 'Magento_Module2'])
            ->will($this->returnValue([]));

        $this->status->expects($this->never())
            ->method('setIsEnabled');

        $commandTester = $isEnable
            ? new CommandTester(new ModuleEnableCommand($this->objectManagerProvider))
            : new CommandTester(new ModuleDisableCommand($this->objectManagerProvider));
        $commandTester->execute(['module' => ['Magento_Module1', 'Magento_Module2']]);
        $this->assertStringMatchesFormat(
            'No modules were changed%a',
            $commandTester->getDisplay()
        );
    }
}
