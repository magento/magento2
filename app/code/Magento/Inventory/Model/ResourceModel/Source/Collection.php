<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel\Source;

use Magento\Inventory\Model\ResourceModel\Source as ResourceSource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Inventory\Model\Source as SourceModel;
use Magento\Inventory\Model\SourceCarrierLinkManagementInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

class Collection extends AbstractCollection
{
    /**
     * @var SourceCarrierLinkManagementInterface
     */
    private $sourceCarrierLinkManagement;

    /**
     * Collection constructor
     *
     * @param SourceCarrierLinkManagementInterface $sourceCarrierLinkManagement
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        SourceCarrierLinkManagementInterface $sourceCarrierLinkManagement,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
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
