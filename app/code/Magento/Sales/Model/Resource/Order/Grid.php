<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Resource\Order;

use Magento\Sales\Model\Resource\AbstractGrid;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\Resource as AppResource;

/**
 * Class Grid
 */
class Grid extends AbstractGrid
{
    /**
     * @var string
     */
    protected $gridTableName = 'sales_flat_order_grid';

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
     * Returns select object
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getGridOriginSelect()
    {
        return $this->getConnection()->select()
            ->from(['sfo' => $this->getTable($this->orderTableName)], [])
            ->joinLeft(
                ['sba' => $this->getTable($this->addressTableName)],
                'sfo.billing_address_id = sba.entity_id',
                []
            )
            ->joinLeft(
                ['ssa' => $this->getTable($this->addressTableName)],
                'sfo.billing_address_id = ssa.entity_id',
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
                    'updated_at' => 'sfo.updated_at'
                ]
            );
    }
}
