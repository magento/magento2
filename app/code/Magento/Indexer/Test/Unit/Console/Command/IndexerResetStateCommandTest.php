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

    public function testExecute()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getIndexerMock(
            ['invalidate'],
            ['indexer_id' => 'indexer_1', 'title' => 'Title_indexerOne']
        );
        $this->initIndexerCollectionByItems([$indexerOne]);

        $indexerOne->expects($this->once())
            ->method('invalidate');

        $this->command = new IndexerResetStateCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame(sprintf('Title_indexerOne indexer has been invalidated.') . PHP_EOL, $actualValue);
    }
}
