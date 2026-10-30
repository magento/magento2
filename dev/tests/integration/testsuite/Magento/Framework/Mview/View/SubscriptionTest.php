<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\Indexer\Action\Dummy;
use Magento\Framework\Mview\Config;
use Magento\Framework\Mview\Config\Data;
use Magento\Framework\Mview\View\AdditionalColumnsProcessor\DefaultProcessor;
use Magento\Framework\Mview\ViewInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for \Magento\Framework\Mview\View\Subscription
 *
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionTest extends TestCase
{
    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var TriggerFactory
     */
    private $triggerFactory;

    /**
     * @var CollectionInterface
     */
    private $viewCollection;

    /**
     * @var ViewInterface
     */
    private $view;

    /**
     * @var Config
     */
    private $mviewConfig;

    /**
     * @var SubscriptionStatementPostprocessorInterface
     */
    private $statementPostprocessor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->resource = $objectManager->get(ResourceConnection::class);
        $this->triggerFactory = $objectManager->get(TriggerFactory::class);
        $this->viewCollection = $objectManager->get(CollectionInterface::class);
        $this->mviewConfig = $objectManager->get(Config::class);
        $this->statementPostprocessor = $objectManager->get(SubscriptionStatementPostprocessorInterface::class);

        // Create a test view
        $this->view = $objectManager->create(ViewInterface::class);
        $this->view->setId('test_view')
            ->setData('subscriptions', [
                'catalog_product_entity' => [
                    'name' => 'catalog_product_entity',
                    'column' => 'entity_id',
                    'subscription_model' => null,
                    'processor' => DefaultProcessor::class
                ]
            ]);

        // Create changelog for the view
        $changelog = $objectManager->create(Changelog::class);
        $changelog->setViewId('test_view');
        $changelog->create();

        // Set up view state
        $state = $objectManager->create(StateInterface::class);
        $state->setViewId('test_view')
            ->setMode(StateInterface::MODE_ENABLED)
            ->setStatus(StateInterface::STATUS_IDLE)
            ->save();

        $this->view->setState($state);

        // Configure the view in Mview configuration
        $configData = $objectManager->get(Data::class);
        $configData->merge([
            'test_view' => [
                'view_id' => 'test_view',
                'action_class' => Dummy::class,
                'group' => 'indexer',
                'subscriptions' => [
                    'catalog_product_entity' => [
                        'name' => 'catalog_product_entity',
                        'column' => 'entity_id',
                        'subscription_model' => null,
                        'processor' => DefaultProcessor::class
                    ]
                ]
            ]
        ]);

        $this->subscription = new Subscription(
            $this->resource,
            $this->triggerFactory,
            $this->viewCollection,
            $this->view,
            'catalog_product_entity',
            'entity_id',
            ['updated_at'],
            [],
            $this->mviewConfig,
            $this->statementPostprocessor
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        // Clean up changelog table
        $changelog = $this->view->getChangelog();
        if ($changelog) {
            $changelog->drop();
        }

        // Clean up state
        $state = $this->view->getState();
        if ($state) {
            $state->delete();
        }
    }

    /**
     * Test creating database triggers
     */
    public function testCreateTriggers(): void
    {
        // Create triggers
        $this->subscription->create();

        // Verify triggers were created
        $connection = $this->resource->getConnection();
        $triggers = $this->subscription->getTriggers();

        foreach ($triggers as $trigger) {
            $triggerName = $trigger->getName();
            $result = $connection->fetchOne(
                "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_NAME = ?",
                [$triggerName]
            );
            $this->assertNotEmpty(
                $result,
                sprintf('Trigger %s was not created', $triggerName)
            );
        }
    }

    /**
     * Test removing database triggers
     */
    public function testRemoveTriggers(): void
    {
        // First create triggers
        $this->subscription->create();

        // Get trigger names before removal
        $triggers = $this->subscription->getTriggers();
        $triggerNames = array_map(function ($trigger) {
            return $trigger->getName();
        }, $triggers);

        // Remove triggers
        $this->subscription->remove();

        // Verify triggers were removed
        $connection = $this->resource->getConnection();
        foreach ($triggerNames as $triggerName) {
            $this->assertFalse(
                $connection->isTableExists($triggerName),
                sprintf('Trigger %s was not removed', $triggerName)
            );
        }
    }

    /**
     * Test trigger statements for ignored columns
     */
    public function testTriggerStatementsWithIgnoredColumns(): void
    {
        $this->subscription->create();
        $triggers = $this->subscription->getTriggers();

        // Find the UPDATE trigger
        $updateTrigger = null;
        foreach ($triggers as $trigger) {
            if ($trigger->getEvent() === Trigger::EVENT_UPDATE) {
                $updateTrigger = $trigger;
                break;
            }
        }

        $this->assertNotNull($updateTrigger, 'UPDATE trigger not found');

        // Verify the trigger statements contain the ignored column check
        $statements = $updateTrigger->getStatements();
        $this->assertNotEmpty($statements, 'Trigger has no statements');

        // Check that updated_at is NOT in the list of columns being checked
        $hasIgnoredColumnCheck = true;
        foreach ($statements as $statement) {
            if (strpos($statement, 'NOT(NEW.`updated_at` <=> OLD.`updated_at`)') !== false) {
                $hasIgnoredColumnCheck = false;
                break;
            }
        }

        $this->assertTrue(
            $hasIgnoredColumnCheck,
            'Trigger contains check for ignored column'
        );
    }
}
