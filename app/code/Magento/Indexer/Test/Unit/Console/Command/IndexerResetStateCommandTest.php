<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Console\Command\IndexerResetStateCommand;
use Magento\Indexer\Model\Indexer\State;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerResetStateCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerResetStateCommand
     */
    private $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMock->expects($this->once())->method('setAreaCode')->with(FrontNameResolver::AREA_CODE);
    }

    public function testExecute()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getIndexerMock(
            ['getState'],
            ['indexer_id' => 'indexer_1', 'title' => 'Title_indexerOne']
        );
        $this->initIndexerCollectionByItems([$indexerOne]);

        $stateMock = $this->createMock(State::class);
        $stateMock->expects($this->exactly(1))
            ->method('setStatus')
            ->with(StateInterface::STATUS_INVALID)->willReturnSelf();

        $stateMock->expects($this->exactly(1))
            ->method('save');

        $indexerOne->expects($this->once())
            ->method('getState')
            ->willReturn($stateMock);

        $this->command = new IndexerResetStateCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame(sprintf('Title_indexerOne indexer has been invalidated.') . PHP_EOL, $actualValue);
    }
}
