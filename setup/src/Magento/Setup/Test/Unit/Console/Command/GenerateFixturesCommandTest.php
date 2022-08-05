<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\GenerateFixturesCommand;
use Magento\Setup\Fixtures\FixtureModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateFixturesCommandTest extends TestCase
{
    /**
     * @var FixtureModel|MockObject
     */
    private $fixtureModel;

    /**
     * @var GenerateFixturesCommand|MockObject
     */
    private $command;

    protected function setUp(): void
    {
        $this->fixtureModel = $this->createMock(FixtureModel::class);
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

    public function testExecuteInvalidLanguageArgument()
    {
        $this->expectException('RuntimeException');
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
