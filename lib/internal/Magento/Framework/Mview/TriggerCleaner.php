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
        $viewCollection = $this->viewCollectionFactory->create();
        $viewList = $viewCollection->getViewsByStateMode(StateInterface::MODE_ENABLED);

        // Unsubscribe mviews
        foreach ($viewList as $view) {
            $view->unsubscribe();
        }

        // Unsubscribe old views that still have triggers in db
        $triggerTableNames = $this->getTableNamesWithTriggers();
        foreach ($triggerTableNames as $tableName) {
            $view = $this->createViewByTableName($tableName);
            $view->unsubscribe();
            $view->getState()->delete();
        }

        // Re-subscribe mviews
        foreach ($viewList as $view) {
            $view->subscribe();
        }

        return true;
    }

    /**
     * Retrieve list of table names that have triggers
     *
     * @return array
     */
    private function getTableNamesWithTriggers(): array
    {
        $connection = $this->resource->getConnection();
        $dbName = $this->resource->getSchemaName(ResourceConnection::DEFAULT_CONNECTION);
        $sql = $connection->select()
            ->from(
                ['information_schema.TRIGGERS'],
                ['EVENT_OBJECT_TABLE']
            )
            ->distinct(true)
            ->where('TRIGGER_SCHEMA = ?', $dbName);
        return $connection->fetchCol($sql);
    }

    /**
     * Create view by db table name
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
}
