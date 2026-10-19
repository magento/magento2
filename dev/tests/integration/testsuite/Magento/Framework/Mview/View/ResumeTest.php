<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Mview\ActionFactory;
use Magento\Framework\Mview\ActionInterface;
use Magento\Framework\Mview\Config\Converter;
use Magento\Framework\Mview\ConfigInterface;
use Magento\Framework\Mview\View;
use Magento\Framework\Mview\View\Changelog;
use Magento\Framework\Mview\View\StateInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Integration tests for resuming a partially processed changelog.
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

    public function testUpdateResumesFromLastSuccessfullyProcessedBatch(): void
    {
        $calls = [];
        $successfulBatches = [];
        $failOnThirdPage = true;
        $action = $this->createMock(ActionInterface::class);
        $action->expects($this->atLeastOnce())->method('execute')->willReturnCallback(
            static function (array $ids) use (&$calls, &$successfulBatches, &$failOnThirdPage): void {
                $ids = array_map('intval', $ids);
                $calls[] = $ids;
                if ($failOnThirdPage && count($calls) === 3) {
                    throw new RuntimeException('Simulated page failure');
                }
                $successfulBatches[] = $ids;
            }
        );

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
        $this->view = $objectManager->create(View::class, [
            'config' => $config,
            'actionFactory' => $actionFactory,
            'changelogBatchSize' => [$this->changelog->getViewId() => self::BATCH_SIZE],
        ]);
        $this->view->setId($this->changelog->getViewId());
        $this->view->setState($this->state);

        try {
            $this->view->update();
            $this->fail('The third page should fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated page failure', $exception->getMessage());
        }

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
}
