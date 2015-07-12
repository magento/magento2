<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Fixtures\FixtureModel;
use Magento\Setup\Console\Command\GenerateFixturesCommand;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateFixturesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FixtureModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fixtureModel;

    /**
     * @var GenerateFixturesCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $command;

    public function setUp()
    {
        $this->fixtureModel = $this->getMock('Magento\Setup\Fixtures\FixtureModel', [], [], '', false);
        $this->command = new GenerateFixturesCommand($this->fixtureModel);
    }

    public function testExecute()
    {
        $this->fixtureModel->expects($this->once())->method('loadConfig')->with('path_to_profile.xml');
        $this->fixtureModel->expects($this->once())->method('initObjectManager');
        $this->fixtureModel->expects($this->once())->method('loadFixtures');
        
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['profile' => 'path_to_profile.xml']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not enough arguments.
     */
    public function testExecuteInvalidLanguageArgument()
    {

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    public function testSkipReindexOption()
    {
        $this->fixtureModel->expects($this->never())->method('reindex');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['profile' => 'path_to_profile.xml', '--skip-reindex' => true]);
    }
}
