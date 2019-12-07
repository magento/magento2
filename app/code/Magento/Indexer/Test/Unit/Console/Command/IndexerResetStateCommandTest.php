<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Indexer\Console\Command\IndexerResetStateCommand;

class IndexerResetStateCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerResetStateCommand
     */
    private $command;

    protected function setUp()
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

        $stateMock = $this->createMock(\Magento\Indexer\Model\Indexer\State::class);
        $stateMock->expects($this->exactly(1))
            ->method('setStatus')
            ->with(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID)
            ->will($this->returnSelf());

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
