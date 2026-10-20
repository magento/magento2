<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\ActionFactory;
use Magento\Framework\Mview\ActionInterface;
use Magento\Framework\Mview\Config\Converter;
use Magento\Framework\Mview\ConfigInterface;
use Magento\Framework\Mview\View;
use Magento\Indexer\Model\ProcessManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresFunction;
use PHPUnit\Framework\TestCase;

/**
 * Exercise inherited MView iterators and state with real child processes.
 *
 * The fixture coordinates production walkers, workers, database state and IPC.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @magentoDbIsolation disabled
 */
#[RequiresFunction('pcntl_fork')]
#[RequiresFunction('posix_kill')]
class ForkTest extends TestCase
{
    private const ENTITY_COUNT = 8;
    private const BATCH_SIZE = 2;

    /** @var ResourceConnection */
    private ResourceConnection $resource;

    /** @var Changelog */
    private Changelog $changelog;

    /** @var StateInterface */
    private StateInterface $state;

    /** @var string */
    private string $logPath;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->resource = $objectManager->get(ResourceConnection::class);
        $this->changelog = $objectManager->create(Changelog::class);
        $this->changelog->setViewId('test_mview_fork_' . bin2hex(random_bytes(4)));
        $this->changelog->create();
        $this->resource->getConnection()->insertArray(
            $this->resource->getTableName($this->changelog->getName()),
            ['entity_id'],
            array_map(static fn (int $id): array => [$id], range(1, self::ENTITY_COUNT))
        );
        $configReader = $this->createStub(DeploymentConfig::class);
        $configReader->method('get')->willReturn(false);
        $this->state = $objectManager->create(StateInterface::class, ['configReader' => $configReader]);
        $this->state->setViewId($this->changelog->getViewId())
            ->setMode(StateInterface::MODE_ENABLED)
            ->setStatus(StateInterface::STATUS_IDLE)
            ->setVersionId(0)
            ->save();
        $logPath = tempnam(sys_get_temp_dir(), 'mview_fork_');
        $this->assertNotFalse($logPath);
        $this->logPath = $logPath;
    }

    protected function tearDown(): void
    {
        if (isset($this->changelog)) {
            $this->changelog->drop();
        }
        if (isset($this->state)) {
            $this->state->delete();
        }
        if (isset($this->logPath) && is_file($this->logPath)) {
            unlink($this->logPath);
        }
    }

    public function testForkedWalkerKeepsPagingInParent(): void
    {
        $walker = Bootstrap::getObjectManager()->create(ChangelogBatchWalker::class);
        $parentPid = getmypid();
        $tablesBefore = $this->resource->getConnection()->listTables();
        $processed = [];
        $forked = false;
        try {
            foreach ($walker->walk($this->changelog, 0, self::ENTITY_COUNT, self::BATCH_SIZE) as $batch) {
                $ids = $batch instanceof ChangelogBatch ? $batch->getIds() : $batch;
                array_push($processed, ...array_map('intval', $ids));
                if (!$forked) {
                    $forked = true;
                    $this->assertSame($tablesBefore, $this->resource->getConnection()->listTables());
                    // Match ProcessManager: do not share an open database socket across fork.
                    $this->resource->closeConnection(null);
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $pid = pcntl_fork();
                    if ($pid === -1) {
                        throw new \RuntimeException('Unable to fork the walker test.');
                    }
                    if ($pid > 0) {
                        $this->waitForChild($pid);
                    }
                }
            }
        } catch (\Throwable $exception) {
            if (getmypid() !== $parentPid) {
                $this->finishChild(['error' => $exception->getMessage()]);
            }
            throw $exception;
        }
        if (getmypid() !== $parentPid) {
            $this->finishChild(['ids' => $processed, 'pid' => getmypid()]);
        }

        $child = json_decode(file_get_contents($this->logPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayNotHasKey('error', $child);
        $this->assertNotSame($parentPid, $child['pid']);
        $this->assertSame([1, 2], $child['ids'], 'The child must not request another page.');
        $this->assertSame(range(1, self::ENTITY_COUNT), $processed);
        $this->assertSame($tablesBefore, $this->resource->getConnection()->listTables());
    }

    public function testPartialUpdateWithProcessManagerIndexesEveryIdOnce(): void
    {
        $processManager = Bootstrap::getObjectManager()->create(ProcessManager::class, ['threadsCount' => 2]);
        $this->assertTrue($processManager->isMultiThreadsExecute());
        $parentPid = getmypid();
        $actionPids = [];
        $action = $this->createMock(ActionInterface::class);
        $action->expects($this->exactly(4))->method('execute')->willReturnCallback(
            function (array $ids) use ($processManager, &$actionPids): void {
                $actionPids[] = getmypid();
                $processManager->execute($this->createWorkers($ids));
            }
        );

        $this->createView($action)->update();

        $records = array_map(
            static fn (string $line): array => json_decode($line, true, 512, JSON_THROW_ON_ERROR),
            file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        );
        $ids = array_column($records, 'id');
        sort($ids);
        $this->assertSame(range(1, self::ENTITY_COUNT), $ids);
        $this->assertNotContains($parentPid, array_column($records, 'pid'));
        $this->assertSame([$parentPid, $parentPid, $parentPid, $parentPid], $actionPids);
        $this->state->loadByView($this->changelog->getViewId());
        $this->assertSame(self::ENTITY_COUNT, (int)$this->state->getVersionId());
        $this->assertSame(StateInterface::STATUS_IDLE, $this->state->getStatus());
    }

    #[DataProvider('childOutcomeProvider')]
    public function testForkedActionCannotCheckpointOrFinalizeView(bool $throwInChild): void
    {
        $parentPid = getmypid();
        $childPid = null;
        $observedState = [];
        $processed = [];
        $action = $this->createMock(ActionInterface::class);
        $action->expects($this->atLeastOnce())->method('execute')->willReturnCallback(
            function (array $ids) use (
                &$childPid,
                &$observedState,
                &$processed,
                $throwInChild
            ): void {
                array_push($processed, ...array_map('intval', $ids));
                if ($childPid === null) {
                    $this->resource->closeConnection(null);
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $childPid = pcntl_fork();
                    if ($childPid === -1) {
                        throw new \RuntimeException('Unable to fork the action test.');
                    }
                    if ($childPid > 0) {
                        $this->waitForChild($childPid);
                        $connection = $this->resource->getConnection();
                        $observedState = $connection->fetchRow(
                            $connection->select()->from($this->resource->getTableName('mview_state'))
                                ->where('view_id = ?', $this->changelog->getViewId())
                        );
                    } elseif ($throwInChild) {
                        throw new \RuntimeException('Child action failure');
                    }
                }
            }
        );

        $childError = null;
        try {
            $this->createView($action)->update();
        } catch (\Throwable $exception) {
            if (getmypid() === $parentPid) {
                throw $exception;
            }
            $childError = $exception->getMessage();
        } finally {
            if (getmypid() !== $parentPid) {
                $this->finishChild(['ids' => $processed, 'error' => $childError]);
            }
        }

        $child = json_decode(file_get_contents($this->logPath), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($throwInChild ? 'Child action failure' : null, $child['error']);
        $this->assertSame([1, 2], $child['ids']);
        $this->assertSame(0, (int)$observedState['version_id'], 'Child must not save a checkpoint.');
        $this->assertSame(StateInterface::STATUS_WORKING, $observedState['status']);
        $this->assertSame(range(1, self::ENTITY_COUNT), $processed);
        $this->state->loadByView($this->changelog->getViewId());
        $this->assertSame(self::ENTITY_COUNT, (int)$this->state->getVersionId());
        $this->assertSame(StateInterface::STATUS_IDLE, $this->state->getStatus());
    }

    public function testParallelWorkerFailureResumesAfterCompletedPage(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $processManager = $objectManager->create(ProcessManager::class, ['threadsCount' => 2]);
        $this->assertTrue($processManager->isMultiThreadsExecute());
        $failureId = 3;
        $calls = [];
        $action = $this->createMock(ActionInterface::class);
        $action->expects($this->exactly(5))->method('execute')->willReturnCallback(
            function (array $ids) use (&$processManager, &$calls, &$failureId): void {
                $calls[] = array_map('intval', $ids);
                $processManager->execute($this->createWorkers($ids, $failureId));
            }
        );
        $view = $this->createView($action);

        try {
            $view->update();
            $this->fail('The worker for ID 3 must fail on page two.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Fail in child process', $exception->getMessage());
        }
        $this->state->loadByView($this->changelog->getViewId());
        $this->assertSame(2, (int)$this->state->getVersionId());
        $this->assertSame(StateInterface::STATUS_IDLE, $this->state->getStatus());

        // A new run gets a fresh manager, including its child-failure flag.
        $processManager = $objectManager->create(ProcessManager::class, ['threadsCount' => 2]);
        $failureId = 0;
        $view->update();

        $this->assertSame([3, 4], $calls[2]);
        $this->assertSame(range(3, self::ENTITY_COUNT), array_merge(...array_slice($calls, 2)));
        $records = array_map(
            static fn (string $line): array => json_decode($line, true, 512, JSON_THROW_ON_ERROR),
            file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        );
        $counts = array_count_values(array_column($records, 'id'));
        $this->assertSame(1, $counts[1]);
        $this->assertSame(1, $counts[2]);
        $this->state->loadByView($this->changelog->getViewId());
        $this->assertSame(self::ENTITY_COUNT, (int)$this->state->getVersionId());
        $this->assertSame(StateInterface::STATUS_IDLE, $this->state->getStatus());
    }

    public static function childOutcomeProvider(): array
    {
        return [
            'child returns' => [false],
            'child throws' => [true],
        ];
    }

    /**
     * Create real workers that record IDs and can fail at a selected changelog entry.
     *
     * @param array $ids
     * @param int $failureId
     * @return callable[]
     */
    private function createWorkers(array $ids, int $failureId = 0): array
    {
        $logPath = $this->logPath;
        return array_map(static fn ($id): callable => static function () use ($logPath, $id, $failureId): void {
            if ((int)$id === $failureId) {
                throw new \RuntimeException('Simulated parallel worker failure');
            }
            file_put_contents(
                $logPath,
                json_encode(['id' => (int)$id, 'pid' => getmypid()], JSON_THROW_ON_ERROR) . "\n",
                FILE_APPEND | LOCK_EX
            );
        }, $ids);
    }

    /**
     * Wait for this test's child, terminating it if it cannot complete in time.
     *
     * @param int $pid
     * @return void
     */
    private function waitForChild(int $pid): void
    {
        $deadline = microtime(true) + 10;
        do {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $result = pcntl_waitpid($pid, $status, WNOHANG);
            if ($result === $pid) {
                $this->assertTrue(pcntl_wifexited($status));
                $this->assertSame(0, pcntl_wexitstatus($status));
                return;
            }
            if ($result === -1) {
                $this->fail('Unable to wait for the forked test worker.');
            }
            usleep(10000);
        } while (microtime(true) < $deadline);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        posix_kill($pid, SIGKILL);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        pcntl_waitpid($pid, $status);
        $this->fail('Forked test worker did not finish within ten seconds.');
    }

    /**
     * Report observations and exit before the child can re-enter PHPUnit.
     *
     * @param array $result
     * @return void
     */
    private function finishChild(array $result): void
    {
        $this->resource->closeConnection(null);
        file_put_contents($this->logPath, json_encode($result, JSON_THROW_ON_ERROR));
        // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
        exit(0);
    }

    /**
     * Create a lightweight view with the default walker and test action.
     *
     * @param ActionInterface $action
     * @return View
     */
    private function createView(ActionInterface $action): View
    {
        $actionFactory = $this->createStub(ActionFactory::class);
        $actionFactory->method('get')->willReturn($action);
        $xml = new \DOMDocument();
        $xml->loadXML(sprintf(
            '<config><view id="%s" class="%s" group="indexer" /></config>',
            $this->changelog->getViewId(),
            ActionInterface::class
        ));
        $viewConfig = (new Converter())->convert($xml)[$this->changelog->getViewId()];
        $config = $this->createStub(ConfigInterface::class);
        $config->method('getView')->willReturn($viewConfig);
        $view = Bootstrap::getObjectManager()->create(View::class, [
            'config' => $config,
            'actionFactory' => $actionFactory,
            'changelogBatchSize' => [$this->changelog->getViewId() => self::BATCH_SIZE],
        ]);
        $view->setId($this->changelog->getViewId());
        $view->setState($this->state);
        return $view;
    }
}
