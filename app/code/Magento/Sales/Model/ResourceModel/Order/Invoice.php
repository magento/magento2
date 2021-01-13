<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order;

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;
use Magento\Sales\Model\Spi\InvoiceResourceInterface;

/**
 * Flat sales order invoice resource
 */
class Invoice extends SalesResource implements InvoiceResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_invoice_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_invoice', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param AbstractModel|DataObject $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $object */
        if (!$object->getOrderId() && $object->getOrder()) {
            $object->setOrderId($object->getOrder()->getId());
            $object->setBillingAddressId($object->getOrder()->getBillingAddress()->getId());
        }

        return parent::_beforeSave($object);
    }

    /**
     * Calculate refunded amount for invoice
     *
     * @param int $invoiceId
     * @param string $filed
     * @return float
     * @throws \InvalidArgumentException
     */
    public function calculateRefundedAmount(int $invoiceId, string $filed): float
    {
        if (empty($filed)) {
            throw new \InvalidArgumentException('The field param must be passed');
        }

        $select = $this->getConnection()->select();
        $select->from(
            ['credit_memo' => $this->getTable('sales_creditmemo')],
            ['total' => new \Zend_Db_Expr("SUM(credit_memo.{$filed})")]
        )->where(
            "credit_memo.invoice_id = ?", $invoiceId
        );

        return (float) $this->getConnection()->fetchOne($select);
    }
}
