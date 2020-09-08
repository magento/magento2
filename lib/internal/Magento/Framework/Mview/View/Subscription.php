<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\Mview\Config;
use Magento\Framework\Mview\View\StateInterface;

/**
 * Class Subscription
 *
 * @package Magento\Framework\Mview\View
 */
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
    private $ignoredUpdateColumns = [];

    /**
     * @var Resource
     */
    protected $resource;
    /**
     * @var Config
     */
    private $mviewConfig;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory
     * @param \Magento\Framework\Mview\View\CollectionInterface $viewCollection
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param Config $mviewConfig
     * @param string $tableName
     * @param string $columnName
     * @param array $ignoredUpdateColumns
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory,
        \Magento\Framework\Mview\View\CollectionInterface $viewCollection,
        \Magento\Framework\Mview\ViewInterface $view,
        $tableName,
        $columnName,
        $ignoredUpdateColumns = [],
        Config $mviewConfig = null
    ) {
        $this->connection = $resource->getConnection();
        $this->triggerFactory = $triggerFactory;
        $this->viewCollection = $viewCollection;
        $this->view = $view;
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->resource = $resource;
        $this->ignoredUpdateColumns = $ignoredUpdateColumns;
        $this->mviewConfig = $mviewConfig ?? ObjectManager::getInstance()->get(Config::class);
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
            $viewList = $this->viewCollection->getViewsByStateMode(StateInterface::MODE_ENABLED);

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
        $trigger = "INSERT IGNORE INTO %s (%s) VALUES (%s);";
        switch ($event) {
            case Trigger::EVENT_UPDATE:
                $tableName = $this->resource->getTableName($this->getTableName());
                if ($this->connection->isTableExists($tableName) &&
                    $describe = $this->connection->describeTable($tableName)
                ) {
                    $columnNames = array_column($describe, 'COLUMN_NAME');
                    $columnNames = array_diff($columnNames, $this->ignoredUpdateColumns);
                    if ($columnNames) {
                        $columns = [];
                        foreach ($columnNames as $columnName) {
                            $columns[] = sprintf(
                                'NOT(NEW.%1$s <=> OLD.%1$s)',
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
        }
        list($columnNames, $columnValues) = $this->prepareTriggerBody($changelog, $event);
        return sprintf(
            $trigger,
            $this->connection->quoteIdentifier($this->resource->getTableName($changelog->getName())),
            $columnNames,
            $columnValues
        );
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function getEntityColumn(string $prefix): string
    {
        return $prefix . $this->connection->quoteIdentifier($this->getColumnName());
    }

    /**
     * Prepare column names and column values for trigger body
     *
     * @param ChangelogInterface $changelog
     * @param string $eventType
     * @return array
     */
    public function prepareTriggerBody(ChangelogInterface $changelog, string $eventType)
    {
        $prefix = $eventType === Trigger::EVENT_DELETE ? 'OLD.' : 'NEW.';
        $describedSubscribedColumns = array_column(
            $this->connection->describeTable($this->getTableName()),
            'COLUMN_NAME'
        );
        $describedClColumns = array_column(
            $this->connection->describeTable($changelog->getName()),
            'COLUMN_NAME'
        );
        $viewConfig = $this->mviewConfig->getView($this->getView()->getId());
        $columnNames = [$this->connection->quoteIdentifier($changelog->getColumnName())];
        $columnValues = [$this->getEntityColumn($prefix)];
        //If we need to add attributes
        if ($viewConfig[ChangelogInterface::ATTRIBUTE_SCOPE_SUPPORT] &&
            array_search(Changelog::ATTRIBUTE_COLUMN, $describedSubscribedColumns) &&
            array_search(Changelog::ATTRIBUTE_COLUMN, $describedClColumns)

        ) {
            $columnValues[] = $prefix . $this->connection->quoteIdentifier(Changelog::ATTRIBUTE_COLUMN);
            $columnNames[] = $this->connection->quoteIdentifier(Changelog::ATTRIBUTE_COLUMN);
        }
        //If we need to add stores
        if ($viewConfig[ChangelogInterface::STORE_SCOPE_SUPPORT] &&
            array_search(Changelog::STORE_COLUMN, $describedSubscribedColumns) &&
            array_search(Changelog::STORE_COLUMN, $describedClColumns)
        ) {
            $columnValues[] = $prefix . $this->connection->quoteIdentifier(Changelog::STORE_COLUMN);
            $columnNames[] = $this->connection->quoteIdentifier(Changelog::STORE_COLUMN);
        }

        return [
            implode(",", $columnNames),
            implode(",", $columnValues)
        ];
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
