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
class OldViews
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
     * Unsubscribe old views by existing triggers
     */
    public function unsubscribe(): void
    {
        $viewCollection = $this->viewCollectionFactory->create();
        $viewList = $viewCollection->getViewsByStateMode(StateInterface::MODE_ENABLED);

        // Unsubscribe mviews
        foreach ($viewList as $view) {
            /** @var ViewInterface $view */
            $view->unsubscribe();
        }

        // Unsubscribe old views that still have triggers in db
        $triggerTableNames = $this->getTriggerTableNames();
        foreach ($triggerTableNames as $tableName) {
            $this->createViewByTableName($tableName)->unsubscribe();
        }

        // Re-subscribe mviews
        foreach ($viewList as $view) {
            /** @var ViewInterface $view */
            $view->subscribe();
        }
    }

    /**
      * Retrieve trigger table name list
      *
      * @return array
      */
    private function getTriggerTableNames(): array
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
            'id' => '0',
            'subscriptions' => $subscription,
        ];

        $view = $this->viewFactory->create($data);
        $view->getState()->setMode(StateInterface::MODE_ENABLED);

        return $view;
    }
}
