<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Status;

use Magento\Sales\Model\Order\Status\History\Validator;
use Magento\Sales\Model\ResourceModel\EntityAbstract;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Sales\Model\Spi\OrderStatusHistoryResourceInterface;

/**
 * Flat sales order status history resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class History extends EntityAbstract implements OrderStatusHistoryResourceInterface
{
    /**
     * @var Validator
     * @since 2.0.0
     */
    protected $validator;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Attribute $attribute
     * @param \Magento\SalesSequence\Model\Manager $sequenceManager
     * @param Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param Validator $validator
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Sales\Model\ResourceModel\Attribute $attribute,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        Validator $validator,
        $connectionName = null
    ) {
        $this->validator = $validator;
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $sequenceManager,
            $connectionName
        );
    }

    /**
     * Event prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_order_status_history_resource';

    /**
     * Model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('sales_order_status_history', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);
        $warnings = $this->validator->validate($object);
        if (!empty($warnings)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Cannot save comment:\n%1", implode("\n", $warnings))
            );
        }
        return $this;
    }
}
