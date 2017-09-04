<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel;

use Exception;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Inventory\Model\SourceCarrierLinkManagementInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Implementation of basic operations for Source entity for specific db layer
 */
class Source extends AbstractDb
{
    /**
     * Provide possibility of save entity with predefined id
     */
    use PredefinedId;

    /**#@+
     * Constants related to specific db layer
     */
    const TABLE_NAME_SOURCE = 'inventory_source';
    /**#@-*/

    /**
     * @var SourceCarrierLinkManagementInterface
     */
    private $sourceCarrierLinkManagement;

    /**
     * Source constructor
     *
     * @param Context $context
     * @param SourceCarrierLinkManagementInterface $sourceCarrierLinkManagement
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        SourceCarrierLinkManagementInterface $sourceCarrierLinkManagement,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->sourceCarrierLinkManagement = $sourceCarrierLinkManagement;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME_SOURCE, SourceInterface::SOURCE_ID);
    }

    /**
     * @inheritdoc
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        parent::load($object, $value, $field);
        /** @var SourceInterface $object */
        $this->sourceCarrierLinkManagement->loadCarrierLinksBySource($object);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function save(AbstractModel $object)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            parent::save($object);
            /** @var SourceInterface $object */
            $this->sourceCarrierLinkManagement->saveCarrierLinksBySource($object);
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $this;
    }
}
