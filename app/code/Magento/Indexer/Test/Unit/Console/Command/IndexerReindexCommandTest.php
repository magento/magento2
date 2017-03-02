<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Indexer\Console\Command\IndexerReindexCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerReindexCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerReindexCommand
     */
    private $command;

    /**
     * @var \Magento\Framework\Indexer\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->configMock = $this->getMock(\Magento\Indexer\Model\Config::class, [], [], '', false);
        parent::setUp();
    }

    /**
     * Get return value map for object manager
     *
     * @return array
     */
    protected function getObjectManagerReturnValueMap()
    {
        $result = parent::getObjectManagerReturnValueMap();
        $result[] = [\Magento\Framework\Indexer\ConfigInterface::class, $this->configMock];
        return $result;
    }

    public function testGetOptions()
    {
        $this->stateMock->expects($this->never())->method('setAreaCode');
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $optionsList = $this->command->getInputList();
        $this->assertSame(1, sizeof($optionsList));
        $this->assertSame('index', $optionsList[0]->getName());
    }

    public function testExecuteAll()
    {
        $this->configMock->expects($this->once())->method('getIndexer')->will($this->returnValue([
            'title' => 'Title_indexerOne',
            'shared_index' => null
        ]));
        $this->configureAdminArea();
        $collection = $this->getMock(\Magento\Indexer\Model\Indexer\Collection::class, [], [], '', false);
        $indexerOne = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $collection->expects($this->once())->method('getItems')->willReturn([$indexerOne]);

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->indexerFactory->expects($this->never())->method('create');
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexerOne index has been rebuilt successfully in', $actualValue);
    }

    public function testExecuteWithIndex()
    {
        $this->configMock->expects($this->any())
            ->method('getIndexer')
            ->will($this->returnValueMap(
                [
                    ['id_indexerOne', ['title' => 'Title_indexerOne', 'shared_index' => null]],
                    ['id_indexerTwo', ['title' => 'Title_indexerTwo', 'shared_index' => 'with_indexer_3']],
                    ['id_indexer3', ['title' => 'Title_indexer3', 'shared_index' => 'with_indexer_3']]
                ]
            ));
        $this->configMock->expects($this->any())
            ->method('getIndexers')
            ->will($this->returnValue(
                [
                    'id_indexerOne' => [
                        'indexer_id' => 'id_indexerOne',
                        'title' => 'Title_indexerOne',
                        'shared_index' => null
                    ],
                    'id_indexerTwo' => [
                        'indexer_id' => 'id_indexerTwo',
                        'title' => 'Title_indexerTwo',
                        'shared_index' => 'with_indexer_3'
                    ],
                    'id_indexer3' => [
                        'indexer_id' => 'id_indexer3',
                        'title' => 'Title_indexer3',
                        'shared_index' => 'with_indexer_3'
                    ]
                ]
            ));

        $this->configureAdminArea();
        $indexerOne = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
        $indexerOne->expects($this->once())->method('reindexAll');
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $indexerOne->expects($this->any())->method('getId')->willReturn('id_indexerOne');
        $indexerOne->expects($this->any())->method('load')->with('id_indexerOne')->willReturn($indexerOne);

        $indexerTwo = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
        $indexerTwo->expects($this->once())->method('reindexAll');
        $indexerTwo->expects($this->once())->method('getTitle')->willReturn('Title_indexerTwo');
        $indexerTwo->expects($this->any())->method('getId')->willReturn('id_indexerTwo');
        $indexerTwo->expects($this->any())->method('load')->with('id_indexerTwo')->willReturn($indexerTwo);

        $indexer3 = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
        $indexer3->expects($this->never())->method('reindexAll');
        $indexer3->expects($this->once())->method('getTitle')->willReturn('Title_indexer3');
        $indexer3->expects($this->any())->method('getId')->willReturn('id_indexer3');
        $indexer3->expects($this->any())->method('load')->with('id_indexer3')->willReturn($indexer3);

        $stateMock = $this->getMock(\Magento\Indexer\Model\Indexer\State::class, [], [], '', false);
        $stateMock->expects($this->exactly(2))->method('setStatus')->will($this->returnSelf());
        $stateMock->expects($this->exactly(2))->method('save');

        $indexer3->expects($this->once())->method('getState')->willReturn($stateMock);
        $indexerTwo->expects($this->once())->method('getState')->willReturn($stateMock);

        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->at(0))->method('create')->willReturn($indexerOne);
        $this->indexerFactory->expects($this->at(1))->method('create')->willReturn($indexerTwo);
        $this->indexerFactory->expects($this->at(2))->method('create')->willReturn($indexer3);
        $this->indexerFactory->expects($this->at(3))->method('create')->willReturn($indexerTwo);
        $this->indexerFactory->expects($this->at(4))->method('create')->willReturn($indexer3);

        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexerOne', 'id_indexerTwo', 'id_indexer3']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexerOne index has been rebuilt successfully in', $actualValue);
    }

    public function testExecuteWithLocalizedException()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
        $localizedException = new LocalizedException(new Phrase('Some Exception Message'));
        $indexerOne->expects($this->once())->method('reindexAll')->will($this->throwException($localizedException));
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexerOne);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexerOne']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Some Exception Message', $actualValue);
    }

    public function testExecuteWithException()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
        $exception = new \Exception();
        $indexerOne->expects($this->once())->method('reindexAll')->will($this->throwException($exception));
        $indexerOne->expects($this->once())->method('getTitle')->willReturn('Title_indexerOne');
        $this->collectionFactory->expects($this->never())->method('create');
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexerOne);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexerOne']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertStringStartsWith('Title_indexerOne indexer process unknown error:', $actualValue);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp The following requested cache types are not supported:.*
     */
    public function testExecuteWithExceptionInLoad()
    {
        $this->configureAdminArea();
        $collection = $this->getMock(\Magento\Indexer\Model\Indexer\Collection::class, [], [], '', false);
        $indexerOne = $this->getMock(\Magento\Indexer\Model\Indexer::class, [], [], '', false);
        $indexerOne->expects($this->once())->method('getId')->willReturn('id_indexer1');
        $collection->expects($this->once())->method('getItems')->willReturn([$indexerOne]);

        $exception = new \Exception();
        $indexerOne->expects($this->once())->method('load')->will($this->throwException($exception));
        $indexerOne->expects($this->never())->method('getTitle');
        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->indexerFactory->expects($this->once())->method('create')->willReturn($indexerOne);
        $this->command = new IndexerReindexCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['id_indexerOne']]);
    }
}
