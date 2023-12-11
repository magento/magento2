<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\View\CollectionFactory;
use Magento\Framework\Mview\View\StateInterface;
use Magento\Framework\Mview\View\Subscription;
use Magento\Framework\DB\Ddl\Trigger;

/**
 * Class for removing old triggers that were created by mview
 */
class TriggerCleaner
{
    /**
     * @var CollectionFactory
     */
    private $viewCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ViewFactory
     */
    private $viewFactory;

    /**
     * @var array
     */
    private $processedTriggers = [];

    /**
     * @var array
     */
    private $DbTriggers = [];

    /**
     * @param CollectionFactory $viewCollectionFactory
     * @param ResourceConnection $resource
     * @param ViewFactory $viewFactory
     */
    public function __construct(
        CollectionFactory $viewCollectionFactory,
        ResourceConnection $resource,
        ViewFactory $viewFactory
    ) {
        $this->viewCollectionFactory = $viewCollectionFactory;
        $this->resource = $resource;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Remove the outdated trigger from the system
     *
     * @return bool
     * @throws \Exception
     */
    public function removeTriggers(): bool
    {
        $this->getDbTriggers();

        // Get list of views that are enabled
        $viewCollection = $this->viewCollectionFactory->create();
        $viewList = $viewCollection->getViewsByStateMode(StateInterface::MODE_ENABLED);

        // Check triggers declaration for the enabled views and update them if any changes
        foreach ($viewList as $view) {
            $subscriptions = $view->getSubscriptions();
            foreach ($subscriptions as $subscriptionConfig) {
                /* @var $subscription Subscription */
                $subscription = $view->initSubscriptionInstance($subscriptionConfig);
                $viewTriggers = $subscription->create(false)->getTriggers();
                $this->processViewTriggers($viewTriggers, $subscription);
            }
        }

        // Remove any remaining triggers from db that are not linked to a view
        $remainingTriggers = array_diff_key($this->DbTriggers, $this->processedTriggers);
        foreach ($remainingTriggers as $trigger) {
            $view = $this->createViewByTableName($trigger['EVENT_OBJECT_TABLE']);
            $view->unsubscribe();
            $view->getState()->delete();
        }

        return true;
    }

    /**
     * Process and update View Triggers if changes were made
     *
     * @param array $viewTriggers
     * @param Subscription $subscription
     * @return void
     */
    private function processViewTriggers(array $viewTriggers, Subscription $subscription): void
    {
        foreach ($viewTriggers as $viewTrigger) {
            if (array_key_exists($viewTrigger->getName(), $this->DbTriggers)) {
                foreach ($this->getStatementsFromViewTrigger($viewTrigger) as $statement) {
                    if (!empty($statement) &&
                        !str_contains($this->DbTriggers[$viewTrigger->getName()]['ACTION_STATEMENT'], $statement)
                    ) {
                        $subscription->saveTrigger($viewTrigger);
                        break;
                    }
                }
            } else {
                $subscription->saveTrigger($viewTrigger);
            }
            $this->processedTriggers[$viewTrigger->getName()] = true;
        }
    }

    /**
     * Retrieve list of all triggers from DB
     *
     * @return void
     */
    private function getDbTriggers(): void
    {
        $connection = $this->resource->getConnection();
        $dbName = $this->resource->getSchemaName(ResourceConnection::DEFAULT_CONNECTION);
        $sql = $connection->select()
            ->from(
                ['information_schema.TRIGGERS'],
                ['TRIGGER_NAME', 'ACTION_STATEMENT', 'EVENT_OBJECT_TABLE']
            )
            ->where('TRIGGER_SCHEMA = ?', $dbName);
        $this->DbTriggers = $connection->fetchAssoc($sql);
    }

    /**
     * Create view by db table name
     *
     * Create a view that has the table name so that unsubscribe can be used to
     * remove triggers with the correct naming structure from the db
     *
     * @param string $tableName
     * @return ViewInterface
     */
    private function createViewByTableName(string $tableName): ViewInterface
    {
        $subscription[$tableName] = [
            'name' => $tableName,
            'column' => '',
            'subscription_model' => null
        ];
        $data['data'] = [
            'subscriptions' => $subscription,
        ];

        $view = $this->viewFactory->create($data);
        $view->setId('old_view');
        $view->getState()->setMode(StateInterface::MODE_ENABLED);

        return $view;
    }

    /**
     * Get trigger statements for further analyze
     *
     * @param Trigger $trigger
     * @return string[]
     */
    private function getStatementsFromViewTrigger(Trigger $trigger): array
    {
        $statements = $trigger->getStatements();

        //Check for staged entity attribute subscription
        $statement = array_shift($statements);
        if (str_contains($statement, 'SET')) {
            $splitStatements = explode(PHP_EOL, $statement);
            $statements += $splitStatements;
        } else {
            array_unshift($statements, $statement);
        }

        return $statements;
    }
}
