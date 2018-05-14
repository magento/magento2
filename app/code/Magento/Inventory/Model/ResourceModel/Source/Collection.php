<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\Source;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\Inventory\Model\Source as SourceModel;
use Magento\InventoryApi\Model\SourceCarrierLinkManagementInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Psr\Log\LoggerInterface;

/**
 * Resource Collection of Source entities
 *
 * @api
 */
class Collection extends AbstractCollection
{
    /**
     * @var SourceCarrierLinkManagementInterface
     */
    private $sourceCarrierLinkManagement;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param SourceCarrierLinkManagementInterface $sourceCarrierLinkManagement
     * @param AdapterInterface $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
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
        $this->_init(SourceModel::class, SourceResourceModel::class);
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
