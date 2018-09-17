<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Indexer\Console\Command\IndexerInfoCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerInfoCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerInfoCommand
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
        $indexerOne->expects($this->once())->method('getId')->willReturn('id_indexerOne');
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $collection->expects($this->once())->method('getItems')->willReturn([$indexerOne]);

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->command = new IndexerInfoCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame(sprintf('%-40s %s', 'id_indexerOne', 'Title_indexerOne') . PHP_EOL, $actualValue);
    }
}
