<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Shipment;

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
    protected $gridTableName = 'sales_shipment_grid';

    /**
     * @var string
     */
    protected $shipmentTableName = 'sales_shipment';

    /**
     * Refreshes (adds new) grid rows.
     *
     * By default if $value parameter is omitted, order shipments created/updated
     * since the last method call will be refreshed.
     *
     * Otherwise single order shipment will be refreshed according to $value, $field
     * parameters.
     *
     * @param null|int|string $value
     * @param null|string $field
     * @return \Zend_Db_Statement_Interface
     */
    public function refresh($value = null, $field = null)
    {
        $select = $this->getGridOriginSelect();

        if (!$value) {
            $select->where(
                ($field ?: 'sfs.created_at') . ' >= ?',
                $this->getLastUpdatedAtValue()
            );
        } else {
            $select->where(
                ($field ?: 'sfs.entity_id') . ' = ?',
                $value
            );
        }

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
        return $this->getConnection()->select()
            ->from(['sfs' => $this->getTable($this->shipmentTableName)], [])
            ->join(['sfo' => $this->getTable($this->orderTableName)], 'sfs.order_id = sfo.entity_id', [])
            ->joinLeft(
                ['ssa' => $this->getTable($this->addressTableName)],
                'sfo.shipping_address_id = ssa.entity_id',
                []
            )
            ->columns(
                [
                    'entity_id' => 'sfs.entity_id',
                    'store_id' => 'sfs.store_id',
                    'total_qty' => 'sfs.total_qty',
                    'order_id' => 'sfs.order_id',
                    'shipment_status' => 'sfs.shipment_status',
                    'increment_id' => 'sfs.increment_id',
                    'order_increment_id' => 'sfo.increment_id',
                    'created_at' => 'sfs.created_at',
                    'updated_at' => 'sfs.updated_at',
                    'order_created_at' => 'sfo.created_at',
                    'shipping_name' => "trim(concat(ifnull(ssa.firstname, ''), ' ' ,ifnull(ssa.lastname, '')))",
                ]
            );
    }
}
