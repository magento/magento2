<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Mview\View;

class Subscription implements SubscriptionInterface
{
    /**
     * Trigger name qualifier
     */
    const TRIGGER_NAME_QUALIFIER = 'trg';

    /**
     * Database write connection
     *
     * @var \Magento\DB\Adapter\AdapterInterface
     */
    protected $write;

    /**
     * @var \Magento\DB\Ddl\Trigger
     */
    protected $triggerFactory;

    /**
     * @var \Magento\Mview\View\CollectionInterface
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
    protected $linkedViews = array();

    /**
     * @var \Magento\App\Resource
     */
    protected $resource;

    /**
     * @param \Magento\App\Resource $resource
     * @param \Magento\DB\Ddl\TriggerFactory $triggerFactory
     * @param \Magento\Mview\View\CollectionInterface $viewCollection
     * @param \Magento\Mview\ViewInterface $view
     * @param string $tableName
     * @param string $columnName
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\DB\Ddl\TriggerFactory $triggerFactory,
        \Magento\Mview\View\CollectionInterface $viewCollection,
        $view,
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

        // Force collection clear
        $this->viewCollection->clear();
    }

    /**
     * Create subsciption
     *
     * @return \Magento\Mview\View\SubscriptionInterface
     */
    public function create()
    {
        foreach (\Magento\DB\Ddl\Trigger::getListOfEvents() as $event) {
            $triggerName = $this->getTriggerName(
                $this->resource->getTableName($this->getTableName()),
                \Magento\DB\Ddl\Trigger::TIME_AFTER,
                $event
            );

            /** @var \Magento\DB\Ddl\Trigger $trigger */
            $trigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(\Magento\DB\Ddl\Trigger::TIME_AFTER)
                ->setEvent($event)
                ->setTable($this->resource->getTableName($this->getTableName()));

            $trigger->addStatement(
                $this->buildStatement($event, $this->getView()->getChangelog())
            );

            // Add statements for linked views
            foreach ($this->getLinkedViews() as $view) {
                /** @var \Magento\Mview\ViewInterface $view */
                $trigger->addStatement(
                    $this->buildStatement($event, $view->getChangelog())
                );
            }

            $this->write->dropTrigger($trigger->getName());
            $this->write->createTrigger($trigger);
        }

        return $this;
    }

    /**
     * Remove subscription
     *
     * @return \Magento\Mview\View\SubscriptionInterface
     */
    public function remove()
    {
        foreach (\Magento\DB\Ddl\Trigger::getListOfEvents() as $event) {
            $triggerName = $this->getTriggerName(
                $this->resource->getTableName($this->getTableName()),
                \Magento\DB\Ddl\Trigger::TIME_AFTER,
                $event
            );

            /** @var \Magento\DB\Ddl\Trigger $trigger */
            $trigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(\Magento\DB\Ddl\Trigger::TIME_AFTER)
                ->setEvent($event)
                ->setTable($this->resource->getTableName($this->getTableName()));

            // Add statements for linked views
            foreach ($this->getLinkedViews() as $view) {
                /** @var \Magento\Mview\ViewInterface $view */
                $trigger->addStatement(
                    $this->buildStatement($event, $view->getChangelog())
                );
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
            $viewList = $this->viewCollection
                ->getViewsByStateMode(\Magento\Mview\View\StateInterface::MODE_ENABLED);

            foreach ($viewList as $view) {
                /** @var \Magento\Mview\ViewInterface $view */
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
     * @param \Magento\Mview\View\ChangelogInterface $changelog
     * @return string
     */
    protected function buildStatement($event, $changelog)
    {
        switch ($event) {
            case \Magento\DB\Ddl\Trigger::EVENT_INSERT:
            case \Magento\DB\Ddl\Trigger::EVENT_UPDATE:
                return sprintf("INSERT IGNORE INTO %s (%s) VALUES (NEW.%s);",
                    $this->write->quoteIdentifier($this->resource->getTableName($changelog->getName())),
                    $this->write->quoteIdentifier($changelog->getColumnName()),
                    $this->write->quoteIdentifier($this->getColumnName())
                );

            case \Magento\DB\Ddl\Trigger::EVENT_DELETE:
                return sprintf("INSERT IGNORE INTO %s (%s) VALUES (OLD.%s);",
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
        return self::TRIGGER_NAME_QUALIFIER . '_' . $tableName
            . '_' . $time
            . '_' . $event;
    }

    /**
     * Retrieve View related to subscription
     *
     * @return \Magento\Mview\ViewInterface
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
