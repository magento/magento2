<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Console\Command\UninstallCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Setup\Model\Installer;

class UninstallCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InstallerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $installerFactory;

    /**
     * @var Installer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $installer;

    /**
     * @var UninstallCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $command;

    public function setUp()
    {
        $this->installerFactory = $this->createMock(\Magento\Setup\Model\InstallerFactory::class);
        $this->installer = $this->createMock(\Magento\Setup\Model\Installer::class);
        $this->command = new UninstallCommand($this->installerFactory);
    }

    public function testExecuteInteractionYes()
    {
        $this->installer->expects($this->once())->method('uninstall');
        $this->installerFactory->expects($this->once())->method('create')->will($this->returnValue($this->installer));

        $this->checkInteraction(true);
    }

    public function testExecuteInteractionNo()
    {
        $this->installer->expects($this->exactly(0))->method('uninstall');
        $this->installerFactory->expects($this->exactly(0))->method('create');

        $this->checkInteraction(false);
    }

    /**
     * @param $answer
     */
    public function checkInteraction($answer)
    {
        $question = $this->createMock(\Symfony\Component\Console\Helper\QuestionHelper::class);
        $question
            ->expects($this->once())
            ->method('ask')
            ->will($this->returnValue($answer));

        /** @var \Symfony\Component\Console\Helper\HelperSet|\PHPUnit_Framework_MockObject_MockObject $helperSet */
        $helperSet = $this->createMock(\Symfony\Component\Console\Helper\HelperSet::class);
        $helperSet
            ->expects($this->once())
            ->method('get')
            ->with('question')
            ->will($this->returnValue($question));
        $this->command->setHelperSet($helperSet);

        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }
}
