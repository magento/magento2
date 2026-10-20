<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Mview\ActionFactory;
use Magento\Framework\Mview\ActionInterface;
use Magento\Framework\Mview\Config\Converter;
use Magento\Framework\Mview\ConfigInterface;
use Magento\Framework\Mview\View;
use Magento\Framework\Mview\View\Changelog;
use Magento\Framework\Mview\View\StateInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Integration tests for resuming a partially processed changelog.
 *
 * This fixture combines the View, EAV payload builder and persisted state.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @magentoDbIsolation disabled
 */
class ResumeTest extends TestCase
{
    private const BATCH_SIZE = 10;
    private const ENTITY_COUNT = 100;
    private const CHANGES_PER_ENTITY = 10;

    /** @var ResourceConnection */
    private ResourceConnection $resource;

    /** @var Changelog */
    private Changelog $changelog;

    /** @var StateInterface */
    private StateInterface $state;

    /** @var View */
    private View $view;

    /** @var \stdClass */
    private \stdClass $lockState;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->resource = $objectManager->get(ResourceConnection::class);
        $this->changelog = $objectManager->create(Changelog::class);
        $this->changelog->setViewId('test_mview_resume_' . bin2hex(random_bytes(4)));
        $this->changelog->create();

        $this->lockState = (object)['active' => false];
        $lockManager = $this->createStub(LockManagerInterface::class);
        $lockManager->method('lock')->willReturnCallback(function (): bool {
            $this->lockState->active = true;
            return true;
        });
        $lockManager->method('unlock')->willReturnCallback(function (): bool {
            $this->lockState->active = false;
            return true;
        });
        $lockManager->method('isLocked')->willReturnCallback(function (): bool {
            return $this->lockState->active;
        });
        $configReader = $this->createStub(DeploymentConfig::class);
        $configReader->method('get')->willReturn(true);
        $this->state = $objectManager->create(StateInterface::class, [
            'lockManager' => $lockManager,
            'configReader' => $configReader,
        ]);
        $this->state->setViewId($this->changelog->getViewId())
            ->setMode(StateInterface::MODE_ENABLED)
            ->setStatus(StateInterface::STATUS_IDLE)
            ->setVersionId(0)
            ->save();

        $rows = [];
        for ($entityId = 1; $entityId <= self::ENTITY_COUNT; $entityId++) {
            for ($change = 0; $change < self::CHANGES_PER_ENTITY; $change++) {
                $rows[] = ['entity_id' => $entityId];
            }
        }
        $this->resource->getConnection()->insertArray(
            $this->resource->getTableName($this->changelog->getName()),
            ['entity_id'],
            $rows
        );
    }

    protected function tearDown(): void
    {
        if (isset($this->changelog)) {
            $this->changelog->drop();
        }
        if (isset($this->state)) {
            $this->state->delete();
        }
    }

    /**
     * @given Two pages complete before the third page fails.
     * @when The view update is retried after the worker lock is released.
     * @then Processing resumes at the failed page with completed checkpoints retained.
     */
    public function testUpdateResumesFromLastSuccessfullyProcessedBatch(): void
    {
        $calls = [];
        $successfulBatches = [];
        $runtimeVersions = [];
        $state = $this->state;
        $failOnThirdPage = true;
        $action = $this->createMock(ActionInterface::class);
        $action->expects($this->atLeastOnce())->method('execute')->willReturnCallback(
            static function (array $ids) use (
                &$calls,
                &$successfulBatches,
                &$runtimeVersions,
                &$failOnThirdPage,
                $state
            ): void {
                $ids = array_map('intval', $ids);
                $calls[] = $ids;
                $runtimeVersions[] = (int)$state->getVersionId();
                if ($failOnThirdPage && count($calls) === 3) {
                    throw new RuntimeException('Simulated page failure');
                }
                $successfulBatches[] = $ids;
            }
        );

        $this->view = $this->createView($action);

        try {
            $this->view->update();
            $this->fail('The third page should fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated page failure', $exception->getMessage());
        }

        $this->assertSame([0, 100, 200], $runtimeVersions);
        $this->state->loadByView($this->changelog->getViewId());
        $this->assertSame(200, (int)$this->state->getVersionId());
        $this->assertSame(range(1, 10), $calls[0]);
        $this->assertSame(range(11, 20), $calls[1]);

        // Model the state left by a terminated worker: the page cursor is durable, the lock is gone.
        $this->state->setStatus(StateInterface::STATUS_WORKING)->save();
        $this->lockState->active = false;
        $this->state->loadByView($this->changelog->getViewId());
        $this->assertSame(200, (int)$this->state->getVersionId());
        $this->assertSame(StateInterface::STATUS_IDLE, $this->state->getStatus());

        $failOnThirdPage = false;
        $this->view->update();

        $this->assertSame(range(21, 30), $calls[3]);
        $this->assertSame(self::ENTITY_COUNT, count(array_merge(...$successfulBatches)));
        $this->assertSame(range(1, self::ENTITY_COUNT), array_merge(...$successfulBatches));

        $this->state->loadByView($this->changelog->getViewId());
        $this->assertSame(1000, (int)$this->state->getVersionId());
    }

    /**
     * @given Repeated changelog entries cover multiple pages of entity IDs.
     * @when The walker reads the complete version range.
     * @then Cursor batch objects cover every entity ID.
     */
    public function testWalkerYieldsCursorBatches(): void
    {
        $walker = Bootstrap::getObjectManager()->create(ChangelogBatchWalker::class);
        $batches = iterator_to_array($walker->walk($this->changelog, 0, 1000, self::BATCH_SIZE), false);

        $this->assertCount(intdiv(self::ENTITY_COUNT, self::BATCH_SIZE), $batches);
        foreach ($batches as $ids) {
            $this->assertInstanceOf(ChangelogBatch::class, $ids);
        }
        $ids = array_merge(...array_map(static fn (ChangelogBatch $batch): array => $batch->getIds(), $batches));
        $this->assertSame(range(1, self::ENTITY_COUNT), array_map('intval', $ids));
    }

    /**
     * @given The walker receives only its original three constructor arguments.
     * @when It reads the changelog.
     * @then The fallback factory produces a batch with its maximum version.
     */
    public function testWalkerAcceptsOriginalConstructorArguments(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $walker = new ChangelogBatchWalker(
            $this->resource,
            $objectManager->get(\Magento\Framework\DB\Query\Generator::class),
            $objectManager->get(ChangelogBatchWalker\IdsContext::class)
        );
        $batches = iterator_to_array($walker->walk($this->changelog, 0, 1000, self::BATCH_SIZE), false);

        $this->assertInstanceOf(ChangelogBatch::class, $batches[0]);
        $this->assertSame(100, $batches[0]->getMaxVersionId());
    }

    /**
     * @given A customized batch builder returns legacy ID arrays.
     * @when The view updates all pages.
     * @then Every page executes before the final cursor is saved.
     */
    public function testLegacyArrayBatchBuilderProcessesEveryPageWithoutCheckpointing(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $baseWalker = $objectManager->create(ChangelogBatchWalker::class);
        $walker = $this->getMockBuilder(ChangelogBatchWalker::class)
            ->setConstructorArgs([
                $this->resource,
                $objectManager->get(\Magento\Framework\DB\Query\Generator::class),
                $objectManager->get(ChangelogBatchWalker\IdsContext::class),
            ])
            ->onlyMethods(['buildBatch'])
            ->getMock();
        $walker->method('buildBatch')->willReturnCallback(
            static function (Select $select, string $columnName, bool $hasCustomGrouping) use ($baseWalker): iterable {
                $batch = $baseWalker->buildBatch($select, $columnName, $hasCustomGrouping);
                return $batch instanceof ChangelogBatch ? $batch->getIds() : $batch;
            }
        );
        $processed = [];
        $runtimeVersions = [];
        $state = $this->state;
        $action = $this->createMock(ActionInterface::class);
        $action->expects($this->exactly(intdiv(self::ENTITY_COUNT, self::BATCH_SIZE)))->method('execute')
            ->willReturnCallback(static function (array $ids) use (&$processed, &$runtimeVersions, $state): void {
                array_push($processed, ...array_map('intval', $ids));
                $runtimeVersions[] = (int)$state->getVersionId();
            });

        $this->createView($action, self::BATCH_SIZE, $walker)->update();

        $this->assertSame(
            range(1, self::ENTITY_COUNT),
            $processed,
            'Array batches must execute before saving cursor ' . $this->state->getVersionId()
        );
        $this->assertSame(array_fill(0, intdiv(self::ENTITY_COUNT, self::BATCH_SIZE), 0), $runtimeVersions);
        $this->state->loadByView($this->changelog->getViewId());
        $this->assertSame(1000, (int)$this->state->getVersionId());
        $this->assertSame(StateInterface::STATUS_IDLE, $this->state->getStatus());
    }

    /**
     * @given Grouped entities have interleaved attributes and optional repeated changes.
     * @when A later group fails and the update is retried.
     * @then Retry retains earlier attributes and the complete input window.
     */
    #[DataProvider('groupedPayloadProvider')]
    public function testGroupedPayloadRetainsEarlierAttributesAfterFailure(bool $repeatChanges): void
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName($this->changelog->getName());
        $connection->truncateTable($table);
        foreach (['attribute_id', 'store_id'] as $column) {
            $connection->addColumn($table, $column, [
                'type' => Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => 0,
                'comment' => $column,
            ]);
        }
        $rows = [
            [2, 10, 0],
            [1, 20, 0],
            [2, 30, 0],
        ];

        $objectManager = Bootstrap::getObjectManager();
        $selectBuilder = $objectManager->create(
            \Magento\Eav\Model\Mview\ChangelogBatchWalker\IdsSelectBuilder::class,
            ['entityTypeCodes' => [$this->changelog->getViewId() => 'catalog_product']]
        );
        if ($repeatChanges) {
            // Use the real EAV limit and exceed it with repeated changes to one attribute.
            $selectBuilder->build($this->changelog);
            $limit = (int)$connection->fetchOne('SELECT @@SESSION.group_concat_max_len');
            $rows = array_merge(array_fill(0, $limit, [2, 10, 0]), $rows);
        }
        $connection->insertArray($table, ['entity_id', 'attribute_id', 'store_id'], $rows);
        $context = $objectManager->create(
            'Magento\Eav\Model\Mview\ChangelogBatchWalker\IdsContext',
            ['selectBuilder' => $selectBuilder]
        );
        $walker = $objectManager->create(ChangelogBatchWalker::class, ['idsContext' => $context]);
        $received = [];
        $fail = true;
        $action = $this->createMock(ActionInterface::class);
        $action->expects($this->atLeastOnce())->method('execute')->willReturnCallback(
            static function (array $ids) use (&$received, &$fail): void {
                $payload = $ids[0];
                $entityId = (int)$payload['entity_id'];
                $received[$entityId] = array_merge(
                    $received[$entityId] ?? [],
                    array_map('intval', explode(',', $payload['attribute_ids']))
                );
                if ($fail && (int)$payload['entity_id'] === 2) {
                    throw new RuntimeException('Simulated grouped page failure');
                }
            }
        );
        $this->view = $this->createView($action, 1, $walker);

        try {
            $this->view->update();
            $this->fail('The second group should fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated grouped page failure', $exception->getMessage());
        }
        $savedVersion = (int)$this->state->getVersionId();
        $received = [];
        $fail = false;
        $this->view->update();

        $attributes = array_values(array_unique($received[2]));
        sort($attributes);
        $this->assertSame([10, 30], $attributes);
        $this->assertSame(0, $savedVersion, 'Grouped payloads must retain their full retry window.');
        $this->assertSame(count($rows), (int)$this->state->getVersionId());
    }

    public static function groupedPayloadProvider(): array
    {
        return [
            'interleaved attributes' => [false],
            'repeated attributes exceed aggregation limit' => [true],
        ];
    }

    /**
     * @given A view is processing its first batch.
     * @when Another state instance advances the cursor and suspends the view.
     * @then The suspended status and external cursor are preserved.
     */
    public function testBatchCheckpointDoesNotOverwriteSuspendedViewCursor(): void
    {
        $action = $this->createMock(ActionInterface::class);
        $action->expects($this->once())->method('execute')->willReturnCallback(
            function (array $ids): void {
                $this->assertCount(self::BATCH_SIZE, $ids);
                $this->resource->getConnection()->insert(
                    $this->resource->getTableName($this->changelog->getName()),
                    ['entity_id' => self::ENTITY_COUNT + 1]
                );
                $otherState = Bootstrap::getObjectManager()->create(StateInterface::class);
                $otherState->loadByView($this->changelog->getViewId())
                    ->setVersionId($this->changelog->getVersion())
                    ->setStatus(StateInterface::STATUS_SUSPENDED)
                    ->save();
            }
        );

        $this->view = $this->createView($action);

        $this->view->update();

        $this->state->loadByView($this->changelog->getViewId());
        $this->assertSame(1001, (int)$this->state->getVersionId());
        $this->assertSame(StateInterface::STATUS_SUSPENDED, $this->state->getStatus());
    }

    /**
     * Create a view using the default configured walker or an explicit test walker.
     *
     * @param ActionInterface $action
     * @param int $batchSize
     * @param ChangelogBatchWalkerInterface|null $walker
     * @return View
     */
    private function createView(
        ActionInterface $action,
        int $batchSize = self::BATCH_SIZE,
        ?ChangelogBatchWalkerInterface $walker = null
    ): View {
        $actionFactory = $this->createStub(ActionFactory::class);
        $actionFactory->method('get')->willReturn($action);
        $configXml = new \DOMDocument();
        $configXml->loadXML(sprintf(
            '<config><view id="%s" class="%s" group="indexer" /></config>',
            $this->changelog->getViewId(),
            ActionInterface::class
        ));
        $viewConfig = (new Converter())->convert($configXml)[$this->changelog->getViewId()];
        $config = $this->createStub(ConfigInterface::class);
        $config->method('getView')->willReturn($viewConfig);
        $objectManager = Bootstrap::getObjectManager();
        $arguments = [
            'config' => $config,
            'actionFactory' => $actionFactory,
            'changelogBatchSize' => [$this->changelog->getViewId() => $batchSize],
        ];
        if ($walker !== null) {
            $factory = $this->createStub(ChangelogBatchWalkerFactory::class);
            $factory->method('create')->willReturn($walker);
            $arguments['changelogBatchWalkerFactory'] = $factory;
        }
        $view = $objectManager->create(View::class, $arguments);
        $view->setId($this->changelog->getViewId());
        $view->setState($this->state);
        return $view;
    }
}
