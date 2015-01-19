<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

class Subscription implements SubscriptionInterface
{
    /**
     * Trigger name qualifier
     */
    const TRIGGER_NAME_QUALIFIER = 'trg';

    /**
     * Database write connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $write;

    /**
     * @var \Magento\Framework\DB\Ddl\Trigger
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
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory
     * @param \Magento\Framework\Mview\View\CollectionInterface $viewCollection
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param string $tableName
     * @param string $columnName
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory,
        \Magento\Framework\Mview\View\CollectionInterface $viewCollection,
        \Magento\Framework\Mview\ViewInterface $view,
        $tableName,
        $columnName
    ) {
        $this->write = $resource->getConnection('core_write');
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
        foreach (\Magento\Framework\DB\Ddl\Trigger::getListOfEvents() as $event) {
            $triggerName = $this->getTriggerName(
                $this->resource->getTableName($this->getTableName()),
                \Magento\Framework\DB\Ddl\Trigger::TIME_AFTER,
                $event
            );

            /** @var \Magento\Framework\DB\Ddl\Trigger $trigger */
            $trigger = $this->triggerFactory->create()->setName(
                $triggerName
            )->setTime(
                \Magento\Framework\DB\Ddl\Trigger::TIME_AFTER
            )->setEvent(
                $event
            )->setTable(
                $this->resource->getTableName($this->getTableName())
            );

            $trigger->addStatement($this->buildStatement($event, $this->getView()->getChangelog()));

            // Add statements for linked views
            foreach ($this->getLinkedViews() as $view) {
                /** @var \Magento\Framework\Mview\ViewInterface $view */
                $trigger->addStatement($this->buildStatement($event, $view->getChangelog()));
            }

            $this->write->dropTrigger($trigger->getName());
            $this->write->createTrigger($trigger);
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
        foreach (\Magento\Framework\DB\Ddl\Trigger::getListOfEvents() as $event) {
            $triggerName = $this->getTriggerName(
                $this->resource->getTableName($this->getTableName()),
                \Magento\Framework\DB\Ddl\Trigger::TIME_AFTER,
                $event
            );

            /** @var \Magento\Framework\DB\Ddl\Trigger $trigger */
            $trigger = $this->triggerFactory->create()->setName(
                $triggerName
            )->setTime(
                \Magento\Framework\DB\Ddl\Trigger::TIME_AFTER
            )->setEvent(
                $event
            )->setTable(
                $this->resource->getTableName($this->getTableName())
            );

            // Add statements for linked views
            foreach ($this->getLinkedViews() as $view) {
                /** @var \Magento\Framework\Mview\ViewInterface $view */
                $trigger->addStatement($this->buildStatement($event, $view->getChangelog()));
            }

            $this->write->dropTrigger($trigger->getName());

            // Re-create trigger if trigger used by linked views
            if ($trigger->getStatements()) {
                $this->write->createTrigger($trigger);
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
     * Build trigger statement for INSER, UPDATE, DELETE events
     *
     * @param string $event
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @return string
     */
    protected function buildStatement($event, $changelog)
    {
        switch ($event) {
            case \Magento\Framework\DB\Ddl\Trigger::EVENT_INSERT:
            case \Magento\Framework\DB\Ddl\Trigger::EVENT_UPDATE:
                return sprintf(
                    "INSERT IGNORE INTO %s (%s) VALUES (NEW.%s);",
                    $this->write->quoteIdentifier($this->resource->getTableName($changelog->getName())),
                    $this->write->quoteIdentifier($changelog->getColumnName()),
                    $this->write->quoteIdentifier($this->getColumnName())
                );

            case \Magento\Framework\DB\Ddl\Trigger::EVENT_DELETE:
                return sprintf(
                    "INSERT IGNORE INTO %s (%s) VALUES (OLD.%s);",
                    $this->write->quoteIdentifier($this->resource->getTableName($changelog->getName())),
                    $this->write->quoteIdentifier($changelog->getColumnName()),
                    $this->write->quoteIdentifier($this->getColumnName())
                );

            default:
                return '';
        }
    }

    /**
     * Retrieve trigger name
     *
     * Build a trigger name by concatenating trigger name prefix, table name,
     * trigger time and trigger event.
     *
     * @param string $tableName
     * @param string $time
     * @param string $event
     * @return string
     */
    protected function getTriggerName($tableName, $time, $event)
    {
        return self::TRIGGER_NAME_QUALIFIER . '_' . $tableName . '_' . $time . '_' . $event;
    }

    /**
     * Retrieve View related to subscription
     *
     * @return \Magento\Framework\Mview\ViewInterface
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Retrieve table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Retrieve table column name
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }
}
