<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel\Source;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Inventory\Model\ResourceModel\Source as ResourceSource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Inventory\Model\Source as SourceModel;
use Magento\Inventory\Model\SourceCarrierLinkManagementInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var SourceCarrierLinkManagementInterface
     */
    private $sourceCarrierLinkManagement;

    /**
     * Collection constructor
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param SourceCarrierLinkManagementInterface $sourceCarrierLinkManagement
     * @param AdapterInterface $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        SourceCarrierLinkManagementInterface $sourceCarrierLinkManagement,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->sourceCarrierLinkManagement = $sourceCarrierLinkManagement;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SourceModel::class, ResourceSource::class);
    }

    /**
     * @inheritdoc
     */
    public function load($printQuery = false, $logQuery = false)
    {
        parent::load($printQuery, $logQuery);

        foreach ($this->_items as $item) {
            /** @var SourceInterface $item */
            $this->sourceCarrierLinkManagement->loadCarrierLinksBySource($item);
        }
        return $this;
    }
}
