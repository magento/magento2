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
        $collection = $this->getMock('Magento\Indexer\Model\Indexer\Collection', [], [], '', false);
        $indexerOne = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);

        $indexerOne->expects($this->once())
            ->method('getTitle')
            ->willReturn('Title_indexerOne');

        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$indexerOne]);

        $stateMock = $this->getMock(\Magento\Indexer\Model\Indexer\State::class, [], [], '', false);
        $stateMock->expects($this->exactly(1))
            ->method('setStatus')
            ->with(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID)
            ->will($this->returnSelf());

        $stateMock->expects($this->exactly(1))
            ->method('save');

        $indexerOne->expects($this->once())
            ->method('getState')
            ->willReturn($stateMock);

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($collection));

        $this->command = new IndexerResetStateCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame(sprintf('Title_indexerOne indexer has been invalidated.') . PHP_EOL, $actualValue);
    }
}
