<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Fixtures\FixtureModel;
use Magento\Setup\Console\Command\GenerateFixturesCommand;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateFixturesCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FixtureModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fixtureModel;

    /**
     * @var GenerateFixturesCommand|\PHPUnit\Framework\MockObject\MockObject
     */
    private $command;

    protected function setUp(): void
    {
        $this->fixtureModel = $this->createMock(\Magento\Setup\Fixtures\FixtureModel::class);
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
     */
    public function testExecuteInvalidLanguageArgument()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');


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
