<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Console\Command\IndexerSetStatusCommand;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\ResourceModel\Indexer\State as StateResourceModel;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerSetStatusCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerSetStatusCommand
     */
    private $command;

    /**
     * @var StateResourceModel|MockObject
     */
    private $stateResourceModelMock;

    /**
     * @var State|MockObject
     */
    private $state;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateResourceModelMock = $this->createMock(StateResourceModel::class);
        $this->state = $this->createMock(State::class);

        $this->command = new IndexerSetStatusCommand(
            $this->stateResourceModelMock,
            $this->objectManagerFactory
        );
    }

    public function testGetOptions()
    {
        $optionsList = $this->command->getInputList();
        $this->assertCount(2, $optionsList);
        $this->assertSame('status', $optionsList[0]->getName());
        $this->assertSame('index', $optionsList[1]->getName());
    }

    public function testExecuteFailsDueToMissingStatusArgument()
    {
        $this->expectException('Symfony\Component\Console\Exception\RuntimeException');
        $this->expectExceptionMessage("Not enough arguments (missing: \"status\").");
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    public function testExecuteInvalidStatus()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Invalid status "wrong_status". Accepted values are "invalid", "suspended", "valid".'
        );
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['status' => 'wrong_status']);
    }

    public function testExecuteAll()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getIndexerMock(
            ['getState'],
            ['indexer_id' => 'indexer_1', 'title' => 'Indexer One Title']
        );

        $this->state->method('setStatus')
            ->willReturnCallback(function () {
                return $this->state;
            });
        $indexerOne->expects($this->once())->method('getState')->willReturn($this->state);
        $this->state->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturnOnConsecutiveCalls(
                StateInterface::STATUS_VALID,
                StateInterface::STATUS_SUSPENDED
            );

        $this->stateResourceModelMock->expects($this->once())
            ->method('save')
            ->with($this->state)
            ->willReturnSelf();

        $this->initIndexerCollectionByItems([$indexerOne]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['status' => StateInterface::STATUS_SUSPENDED]);

        $actualValue = $commandTester->getDisplay();
        $this->assertSame(
            sprintf(
                "Index status for Indexer 'Indexer One Title' was changed from '%s' to '%s'.%s",
                StateInterface::STATUS_VALID,
                StateInterface::STATUS_SUSPENDED,
                PHP_EOL
            ),
            $actualValue
        );
    }

    public function testExecuteAllWithoutStatusChange()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getIndexerMock(
            ['getState'],
            ['indexer_id' => 'indexer_1', 'title' => 'Indexer One Title']
        );

        $this->state->method('setStatus')
            ->willReturnCallback(function () {
                return $this->state;
            });
        $indexerOne->expects($this->once())->method('getState')->willReturn($this->state);
        $this->state->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturnOnConsecutiveCalls(
                StateInterface::STATUS_INVALID,
                StateInterface::STATUS_INVALID
            );

        $this->stateResourceModelMock->expects($this->once())
            ->method('save')
            ->with($this->state)
            ->willReturnSelf();

        $this->initIndexerCollectionByItems([$indexerOne]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['status' => StateInterface::STATUS_SUSPENDED]);

        $actualValue = $commandTester->getDisplay();
        $this->assertSame(
            sprintf(
                "Index status for Indexer 'Indexer One Title' has not been changed.%s",
                PHP_EOL
            ),
            $actualValue
        );
    }

    /**
     * @param string $previousStatus
     * @param string $newStatus
     *
     * @dataProvider executeWithIndexDataProvider
     * @throws \Exception
     */
    public function testExecuteWithIndex(string $previousStatus, string $newStatus)
    {
        $this->configureAdminArea();
        $indexerOne = $this->getIndexerMock(
            ['getState'],
            ['indexer_id' => 'indexer_1', 'title' => 'Indexer One Title']
        );

        $this->state->method('setStatus')
            ->willReturnCallback(function () {
                return $this->state;
            });
        $indexerOne->expects($this->once())->method('getState')->willReturn($this->state);
        $this->state->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturnOnConsecutiveCalls(
                $previousStatus,
                $newStatus
            );

        $this->stateResourceModelMock->expects($this->once())
            ->method('save')
            ->with($this->state)
            ->willReturnSelf();

        $this->initIndexerCollectionByItems([$indexerOne]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['status' => $newStatus, 'index' => ['indexer_1']]);

        $actualValue = $commandTester->getDisplay();
        $this->assertSame(
            sprintf(
                "Index status for Indexer 'Indexer One Title' was changed from '%s' to '%s'.%s",
                $previousStatus,
                $newStatus,
                PHP_EOL
            ),
            $actualValue
        );
    }

    public static function executeWithIndexDataProvider(): array
    {
        return [
            [
                StateInterface::STATUS_SUSPENDED,
                StateInterface::STATUS_VALID,
            ],
            [
                StateInterface::STATUS_VALID,
                StateInterface::STATUS_SUSPENDED,
            ],
            [
                StateInterface::STATUS_VALID,
                StateInterface::STATUS_INVALID,
            ],
            [
                StateInterface::STATUS_INVALID,
                StateInterface::STATUS_VALID
            ],
            [
                StateInterface::STATUS_INVALID,
                StateInterface::STATUS_SUSPENDED
            ],
            [
                StateInterface::STATUS_SUSPENDED,
                StateInterface::STATUS_INVALID
            ]
        ];
    }

    public function testExecuteAllWithException()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getIndexerMock(
            ['getState'],
            ['indexer_id' => 'indexer_1', 'title' => 'Indexer One Title']
        );

        $this->state->method('setStatus')
            ->willReturnCallback(function () {
                return $this->state;
            });
        $indexerOne->expects($this->once())->method('getState')->willReturn($this->state);

        $this->stateResourceModelMock->expects($this->once())
            ->method('save')
            ->with($this->state)
            ->willThrowException(new AlreadyExistsException(__('Exception while indexer status update.')));

        $this->initIndexerCollectionByItems([$indexerOne]);

        $commandTester = new CommandTester($this->command);
        $returnValue = $commandTester->execute(['status' => StateInterface::STATUS_SUSPENDED]);

        $actualValue = $commandTester->getDisplay();
        $this->assertSame(
            sprintf(
                "Exception while indexer status update.%s",
                PHP_EOL
            ),
            $actualValue
        );
        $this->assertSame(Cli::RETURN_FAILURE, $returnValue);
    }
}
