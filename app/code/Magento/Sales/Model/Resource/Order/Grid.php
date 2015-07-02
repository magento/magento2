<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order;

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
    protected $gridTableName = 'sales_order_grid';

    /**
     * Adds new orders to the grid.
     *
     * Only orders that correspond to $value and $field parameters will be added.
     *
     * @param int|string $value
     * @param null|string $field
     * @return \Zend_Db_Statement_Interface
     */
    public function refresh($value, $field = null)
    {
        $select = $this->getGridOriginSelect()
            ->where(($field ?: 'sfo.entity_id') . ' = ?', $value);

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
     * Adds new orders to the grid.
     *
     * Only orders created/updated since the last method call will be added.
     *
     * @return \Zend_Db_Statement_Interface
     */
    public function refreshBySchedule()
    {
        $select = $this->getGridOriginSelect()
            ->where('sfo.updated_at >= ?', $this->getLastUpdatedAtValue());

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
        $billingAddress = "trim(concat(ifnull(sba.street, ''), '\n', ifnull(sba.city, ''), "
            . "',', ifnull(sba.region, ''), ',', ifnull(sba.postcode, '')))";
        $shippingAddress = "trim(concat(ifnull(ssa.street, ''), '\n', ifnull(ssa.city, ''), "
            . "',', ifnull(ssa.region, ''), ',', ifnull(ssa.postcode, '')))";
        return $this->getConnection()->select()
            ->from(['sfo' => $this->getTable($this->orderTableName)], [])
            ->joinLeft(
                ['sba' => $this->getTable($this->addressTableName)],
                'sfo.billing_address_id = sba.entity_id',
                []
            )
            ->joinLeft(
                ['ssa' => $this->getTable($this->addressTableName)],
                'sfo.shipping_address_id = ssa.entity_id',
                []
            )
            ->joinLeft(
                ['cg' => $this->getTable('customer_group')],
                'sfo.customer_group_id = cg.customer_group_id',
                []
            )
            ->columns(
                [
                    'entity_id' => 'sfo.entity_id',
                    'status' => 'sfo.status',
                    'store_id' => 'sfo.store_id',
                    'store_name' => 'sfo.store_name',
                    'customer_id' => 'sfo.customer_id',
                    'base_grand_total' => 'sfo.base_grand_total',
                    'base_total_paid' => 'sfo.base_total_paid',
                    'grand_total' => 'sfo.grand_total',
                    'total_paid' => 'sfo.total_paid',
                    'increment_id' => 'sfo.increment_id',
                    'base_currency_code' => 'sfo.base_currency_code',
                    'order_currency_code' => 'sfo.order_currency_code',
                    'shipping_name' => "trim(concat(ifnull(ssa.firstname, ''), ' ' ,ifnull(ssa.lastname, '')))",
                    'billing_name' => "trim(concat(ifnull(sba.firstname, ''), ' ', ifnull(sba.lastname, '')))",
                    'created_at' => 'sfo.created_at',
                    'updated_at' => 'sfo.updated_at',
                    'billing_address' => $billingAddress,
                    'shipping_address' => $shippingAddress,
                    'shipping_information' => 'sfo.shipping_description',
                    'customer_email' => 'sfo.customer_email',
                    'customer_group' => 'cg.customer_group_code',
                    'subtotal' => 'sfo.base_subtotal',
                    'shipping_and_handling' => 'sfo.base_shipping_amount'
                ]
            );
    }
}
