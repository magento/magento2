<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\Mview\ViewInterface;

class Subscription implements SubscriptionInterface
{
    /**
     * Database connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var TriggerFactory
     */
    protected $triggerFactory;

    /**
     * @var CollectionInterface
     */
    protected $viewCollection;

    /**
     * @var string
     */
    protected $view;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $columnName;

    /**
     * List of views linked to the same entity as the current view
     *
     * @var array
     */
    protected $linkedViews = [];

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * List of columns that can be updated in a subscribed table
     * without creating a new change log entry
     *
     * @var array
     */
    private $ignoredUpdateColumns = [];

    /**
     * @param ResourceConnection $resource
     * @param TriggerFactory $triggerFactory
     * @param CollectionInterface $viewCollection
     * @param ViewInterface $view
     * @param string $tableName
     * @param string $columnName
     * @param array $ignoredUpdateColumns
     */
    public function __construct(
        ResourceConnection $resource,
        TriggerFactory $triggerFactory,
        CollectionInterface $viewCollection,
        ViewInterface $view,
        $tableName,
        $columnName,
        array $ignoredUpdateColumns = []
    ) {
        $this->connection = $resource->getConnection();
        $this->triggerFactory = $triggerFactory;
        $this->viewCollection = $viewCollection;
        $this->view = $view;
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->resource = $resource;
        $this->ignoredUpdateColumns = $ignoredUpdateColumns;
    }

    /**
     * Create subscription
     *
     * @return SubscriptionInterface
     */
    public function create()
    {
        foreach (Trigger::getListOfEvents() as $event) {
            $triggerName = $this->getAfterEventTriggerName($event);
            /** @var Trigger $trigger */
            $trigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(Trigger::TIME_AFTER)
                ->setEvent($event)
                ->setTable($this->resource->getTableName($this->tableName));

            $trigger->addStatement($this->buildStatement($event, $this->getView()->getChangelog()));

            // Add statements for linked views
            foreach ($this->getLinkedViews() as $view) {
                /** @var ViewInterface $view */
                $trigger->addStatement($this->buildStatement($event, $view->getChangelog()));
            }

            $this->connection->dropTrigger($trigger->getName());
            $this->connection->createTrigger($trigger);
        }

        return $this;
    }

    /**
     * Remove subscription
     *
     * @return SubscriptionInterface
     */
    public function remove()
    {
        foreach (Trigger::getListOfEvents() as $event) {
            $triggerName = $this->getAfterEventTriggerName($event);
            /** @var Trigger $trigger */
            $trigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(Trigger::TIME_AFTER)
                ->setEvent($event)
                ->setTable($this->resource->getTableName($this->getTableName()));

            // Add statements for linked views
            foreach ($this->getLinkedViews() as $view) {
                /** @var ViewInterface $view */
                $trigger->addStatement($this->buildStatement($event, $view->getChangelog()));
            }

            $this->connection->dropTrigger($trigger->getName());

            // Re-create trigger if trigger used by linked views
            if ($trigger->getStatements()) {
                $this->connection->createTrigger($trigger);
            }
        }

        return $this;
    }

    /**
     * Retrieve list of linked views
     *
     * @return array
     */
    protected function getLinkedViews()
    {
        if (!$this->linkedViews) {
            $viewList = $this->viewCollection->getViewsByStateMode(StateInterface::MODE_ENABLED);

            foreach ($viewList as $view) {
                /** @var ViewInterface $view */
                // Skip the current view
                if ($view->getId() == $this->getView()->getId()) {
                    continue;
                }
                // Search in view subscriptions
                foreach ($view->getSubscriptions() as $subscription) {
                    if ($subscription['name'] != $this->getTableName()) {
                        continue;
                    }
                    $this->linkedViews[] = $view;
                }
            }
        }
        return $this->linkedViews;
    }

    /**
     * Build trigger statement for INSERT, UPDATE, DELETE events
     *
     * @param string $event
     * @param ChangelogInterface $changelog
     * @return string
     */
    protected function buildStatement($event, $changelog)
    {
        switch ($event) {
            case Trigger::EVENT_INSERT:
                $trigger = "INSERT IGNORE INTO %s (%s) VALUES (NEW.%s);";
                break;

            case Trigger::EVENT_UPDATE:
                $tableName = $this->resource->getTableName($this->getTableName());
                $trigger = "INSERT IGNORE INTO %s (%s) VALUES (NEW.%s);";
                if ($this->connection->isTableExists($tableName) &&
                    $describe = $this->connection->describeTable($tableName)
                ) {
                    $columnNames = array_column($describe, 'COLUMN_NAME');
                    $columnNames = array_diff($columnNames, $this->ignoredUpdateColumns);
                    if ($columnNames) {
                        $columns = [];
                        foreach ($columnNames as $columnName) {
                            $columns[] = sprintf(
                                'NEW.%1$s <=> OLD.%1$s',
                                $this->connection->quoteIdentifier($columnName)
                            );
                        }
                        $trigger = sprintf(
                            "IF (%s) THEN %s END IF;",
                            implode(' OR ', $columns),
                            $trigger
                        );
                    }
                }
                break;

            case Trigger::EVENT_DELETE:
                $trigger = "INSERT IGNORE INTO %s (%s) VALUES (OLD.%s);";
                break;

            default:
                return '';
        }

        return sprintf(
            $trigger,
            $this->connection->quoteIdentifier($this->resource->getTableName($changelog->getName())),
            $this->connection->quoteIdentifier($changelog->getColumnName()),
            $this->connection->quoteIdentifier($this->getColumnName())
        );
    }

    /**
     * Build an "after" event for the given table and event
     *
     * @param string $event The DB level event, like "update" or "insert"
     *
     * @return string
     */
    private function getAfterEventTriggerName($event)
    {
        return $this->resource->getTriggerName(
            $this->resource->getTableName($this->getTableName()),
            Trigger::TIME_AFTER,
            $event
        );
    }

    /**
     * Retrieve View related to subscription
     *
     * @return ViewInterface
     * @codeCoverageIgnore
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Retrieve table name
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Retrieve table column name
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getColumnName()
    {
        return $this->columnName;
    }
}
