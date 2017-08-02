<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Invoice;

use Magento\Sales\Model\ResourceModel\EntityAbstract;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Sales\Model\Spi\InvoiceCommentResourceInterface;

/**
 * Flat sales order invoice comment resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Comment extends EntityAbstract implements InvoiceCommentResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_order_invoice_comment_resource';

    /**
     * Validator
     *
     * @var \Magento\Sales\Model\Order\Invoice\Comment\Validator
     * @since 2.0.0
     */
    protected $validator;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Attribute $attribute
     * @param \Magento\SalesSequence\Model\Manager $sequenceManager
     * @param Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Magento\Sales\Model\Order\Invoice\Comment\Validator $validator
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Sales\Model\ResourceModel\Attribute $attribute,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Magento\Sales\Model\Order\Invoice\Comment\Validator $validator,
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
     * Model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('sales_invoice_comment', 'entity_id');
    }

    /**
     * Performs validation before save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Invoice\Comment $object */
        if (!$object->getParentId() && $object->getInvoice()) {
            $object->setParentId($object->getInvoice()->getId());
        }

        parent::_beforeSave($object);
        $errors = $this->validator->validate($object);
        if (!empty($errors)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Cannot save comment:\n%1", implode("\n", $errors))
            );
        }

        return $this;
    }
}
