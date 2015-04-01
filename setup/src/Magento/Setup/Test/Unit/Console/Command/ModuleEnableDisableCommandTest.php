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
     * @var ModuleDisableCommand
     */
    protected function setUp()
    {
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->objectManagerProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($objectManager));
        $this->status = $this->getMock('Magento\Framework\Module\Status', [], [], '', false);
        $objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->status));
    }

    /**
     * @param bool $isEnable
     * @param string $expectedMessage
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute($isEnable, $expectedMessage)
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

        $commandTester = $isEnable
            ? new CommandTester(new ModuleEnableCommand($this->objectManagerProvider))
            : new CommandTester(new ModuleDisableCommand($this->objectManagerProvider));
        $commandTester->execute(['module' => ['Magento_Module1', 'Magento_Module2']]);
        $this->assertStringMatchesFormat($expectedMessage, $commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
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
            '%aYour store may not operate properly because of dependencies and conflicts' . $expectedMessage,
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
            'There have been no changes to any modules%a',
            $commandTester->getDisplay()
        );
    }
}
