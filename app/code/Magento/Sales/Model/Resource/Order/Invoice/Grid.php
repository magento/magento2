<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Invoice;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Model\Resource\AbstractGrid;

/**
 * Class Grid
 */
class Grid extends AbstractGrid
{
    /**
     * @var string
     */
    protected $gridTableName = 'sales_invoice_grid';

    /**
     * @var string
     */
    protected $invoiceTableName = 'sales_invoice';

    /**
     * Adds new order invoices to the grid.
     *
     * Only order invoices that correspond to $value and $field
     * parameters will be added.
     *
     * @param int|string $value
     * @param null|string $field
     * @return \Zend_Db_Statement_Interface
     */
    public function refresh($value, $field = null)
    {
        $select = $this->getGridOriginSelect()
            ->where(($field ?: 'sfi.entity_id') . ' = ?', $value);

        return $this->getConnection()->query(
            $this->getConnection()
                ->insertFromSelect(
                    $select,
                    $this->getTable($this->gridTableName),
                    [],
                    AdapterInterface::INSERT_ON_DUPLICATE
                )
        );
    }

    /**
     * Adds new order invoices to the grid.
     *
     * Only order invoices created/updated since the last method call
     * will be added.
     *
     * @return \Zend_Db_Statement_Interface
     */
    public function refreshBySchedule()
    {
        $select = $this->getGridOriginSelect()
            ->where('sfi.updated_at >= ?', $this->getLastUpdatedAtValue());

        return $this->getConnection()->query(
            $this->getConnection()
                ->insertFromSelect(
                    $select,
                    $this->getTable($this->gridTableName),
                    [],
                    AdapterInterface::INSERT_ON_DUPLICATE
                )
        );
    }

    /**
     * Returns select object
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getGridOriginSelect()
    {
        $paymentMethodSelect = $this->getConnection()->select()->from(
            'sales_order_payment', ['method']
        )->where(
            '`parent_id` = sfi.order_id'
        )->limit(1);

        $customerName = "trim(concat(ifnull(sfo.customer_firstname, ''), ' ', ifnull(sfo.customer_lastname, '')))";

        $billingName = "trim(concat(ifnull(sba.firstname, ''), ' ', ifnull(sba.lastname, '')))";

        $billingAddress = "trim(concat(ifnull(sba.street, ''), '\n', ifnull(sba.city, ''), "
            . "',', ifnull(sba.region, ''), ',', ifnull(sba.postcode, '')))";

        $shippingAddress = "trim(concat(ifnull(ssa.street, ''), '\n', ifnull(ssa.city, ''), "
            . "',', ifnull(ssa.region, ''), ',', ifnull(ssa.postcode, '')))";

        return $this->getConnection()->select()->from(
            ['sfi' => $this->getTable($this->invoiceTableName)],
            []
        )->join(
            ['sfo' => $this->getTable($this->orderTableName)],
            'sfi.order_id = sfo.entity_id',
            []
        )->joinLeft(
            ['sba' => $this->getTable($this->addressTableName)],
            'sfo.billing_address_id = sba.entity_id',
            []
        )->joinLeft(
            ['ssa' => $this->getTable($this->addressTableName)],
            'sfo.shipping_address_id = ssa.entity_id',
            []
        )->columns(
            [
                'entity_id' => 'sfi.entity_id',
                'increment_id' => 'sfi.increment_id',
                'state' => 'sfi.state',
                'store_id' => 'sfi.store_id',
                'store_name' => 'sfo.store_name',
                'order_id' => 'sfi.order_id',
                'order_increment_id' => 'sfo.increment_id',
                'order_created_at' => 'sfo.created_at',
                'customer_id' => 'sfo.customer_id',
                'customer_name' => $customerName,
                'customer_email' => 'sfo.customer_email',
                'customer_group_id' => 'sfo.customer_group_id',
                'payment_method' => sprintf('(%s)', $paymentMethodSelect),
                'store_currency_code' => 'sfi.store_currency_code',
                'order_currency_code' => 'sfi.order_currency_code',
                'base_currency_code' => 'sfi.base_currency_code',
                'global_currency_code' => 'sfi.global_currency_code',
                'billing_name' => $billingName,
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress,
                'shipping_information' => 'sfo.shipping_description',
                'subtotal' => 'sfo.base_subtotal',
                'shipping_and_handling' => 'sfo.base_shipping_amount',
                'grand_total' => 'sfi.grand_total',
                'created_at' => 'sfi.created_at',
                'updated_at' => 'sfi.updated_at'
            ]
        );
    }
}
