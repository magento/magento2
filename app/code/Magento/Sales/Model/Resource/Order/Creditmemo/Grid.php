<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Creditmemo;

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
    protected $gridTableName = 'sales_creditmemo_grid';

    /**
     * @var string
     */
    protected $creditmemoTableName = 'sales_creditmemo';

    /**
     * Refresh grid row
     *
     * @param int|string $value
     * @param null|string $field
     * @return \Zend_Db_Statement_Interface
     */
    public function refresh($value, $field = null)
    {
        $select = $this->getGridOriginSelect()
            ->where(($field ?: 'sfc.entity_id') . ' = ?', $value);
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
            ->from(['sfc' => $this->getTable($this->creditmemoTableName)], [])
            ->join(['sfo' => $this->getTable($this->orderTableName)], 'sfc.order_id = sfo.entity_id', [])
            ->joinLeft(
                ['sba' => $this->getTable($this->addressTableName)],
                'sfo.billing_address_id = sba.entity_id',
                []
            )
            ->columns(
                [
                    'entity_id' => 'sfc.entity_id',
                    'store_id' => 'sfc.store_id',
                    'store_to_order_rate' => 'sfc.store_to_order_rate',
                    'base_to_order_rate' => 'sfc.base_to_order_rate',
                    'grand_total' => 'sfc.grand_total',
                    'store_to_base_rate' => 'sfc.store_to_base_rate',
                    'base_to_global_rate' => 'sfc.base_to_global_rate',
                    'base_grand_total' => 'sfc.base_grand_total',
                    'order_id' => 'sfc.order_id',
                    'creditmemo_status' => 'sfc.creditmemo_status',
                    'state' => 'sfc.state',
                    'invoice_id' => 'sfc.invoice_id',
                    'store_currency_code' => 'sfc.store_currency_code',
                    'order_currency_code' => 'sfc.order_currency_code',
                    'base_currency_code' => 'sfc.base_currency_code',
                    'global_currency_code' => 'sfc.global_currency_code',
                    'increment_id' => 'sfc.increment_id',
                    'order_increment_id' => 'sfo.increment_id',
                    'created_at' => 'sfc.created_at',
                    'order_created_at' => 'sfo.created_at',
                    'billing_name' => "trim(concat(ifnull(sba.firstname, ''), ' ', ifnull(sba.lastname, '')))",
                ]
            );
    }
}
