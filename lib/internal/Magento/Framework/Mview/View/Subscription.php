<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Trigger;

class Subscription implements SubscriptionInterface
{
    /**
     * Database connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\DB\Ddl\TriggerFactory
     */
    protected $triggerFactory;

    /**
     * @var \Magento\Framework\Mview\View\CollectionInterface
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
     * List of columns that can be updated in a subscribed table
     * without creating a new change log entry
     *
     * @var array
     */
    protected $ignoredUpdateColumns = ['updated_at'];

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory
     * @param \Magento\Framework\Mview\View\CollectionInterface $viewCollection
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param string $tableName
     * @param string $columnName
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory,
        \Magento\Framework\Mview\View\CollectionInterface $viewCollection,
        \Magento\Framework\Mview\ViewInterface $view,
        $tableName,
        $columnName
    ) {
        $this->connection = $resource->getConnection();
        $this->triggerFactory = $triggerFactory;
        $this->viewCollection = $viewCollection;
        $this->view = $view;
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->resource = $resource;
    }

    /**
     * Create subsciption
     *
     * @return \Magento\Framework\Mview\View\SubscriptionInterface
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
                /** @var \Magento\Framework\Mview\ViewInterface $view */
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
     * @return \Magento\Framework\Mview\View\SubscriptionInterface
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
                /** @var \Magento\Framework\Mview\ViewInterface $view */
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
            $viewList = $this->viewCollection->getViewsByStateMode(\Magento\Framework\Mview\View\StateInterface::MODE_ENABLED);

            foreach ($viewList as $view) {
                /** @var \Magento\Framework\Mview\ViewInterface $view */
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
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @return string
     */
    protected function buildStatement($event, $changelog)
    {
        $columns = [];
        if ($describe = $this->connection->describeTable($this->getTableName())) {
            foreach ($describe as $column) {
                if (in_array($column['COLUMN_NAME'], $this->ignoredUpdateColumns)) {
                    continue;
                }
                $columns[] = sprintf(
                    'NEW.%1$s != OLD.%1$s',
                    $this->connection->quoteIdentifier($column['COLUMN_NAME'])
                );
            }
        }

        switch ($event) {
            case Trigger::EVENT_INSERT:
                $trigger = "INSERT IGNORE INTO %s (%s) VALUES (NEW.%s);";
                break;
            case Trigger::EVENT_UPDATE:
                $trigger = "INSERT IGNORE INTO %s (%s) VALUES (NEW.%s);";
                if ($columns) {
                    $trigger = sprintf(
                        "IF (%s) THEN %s END IF;",
                        implode(' OR ', $columns),
                        $trigger
                    );
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
     * @return \Magento\Framework\Mview\ViewInterface
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
