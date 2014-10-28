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
namespace Magento\Sales\Model\Resource\Order\Invoice;

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
    protected $gridTableName = 'sales_flat_invoice_grid';

    /**
     * @var string
     */
    protected $invoiceTableName = 'sales_flat_invoice';

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
     * Returns select object
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getGridOriginSelect()
    {
        return $this->getConnection()->select()
            ->from(['sfi' => $this->getTable($this->invoiceTableName)], [])
            ->join(['sfo' => $this->getTable($this->orderTableName)], 'sfi.order_id = sfo.entity_id', [])
            ->joinLeft(
                ['sba' => $this->getTable($this->addressTableName)],
                'sfo.billing_address_id = sba.entity_id',
                []
            )
            ->columns(
                [
                    'entity_id' => 'sfi.entity_id',
                    'store_id' => 'sfi.store_id',
                    'base_grand_total' => 'sfi.base_grand_total',
                    'grand_total' => 'sfi.grand_total',
                    'order_id' => 'sfi.order_id',
                    'state' => 'sfi.state',
                    'store_currency_code' => 'sfi.store_currency_code',
                    'order_currency_code' => 'sfi.order_currency_code',
                    'base_currency_code' => 'sfi.base_currency_code',
                    'global_currency_code' => 'sfi.global_currency_code',
                    'increment_id' => 'sfi.increment_id',
                    'order_increment_id' => 'sfo.increment_id',
                    'created_at' => 'sfi.created_at',
                    'order_created_at' => 'sfo.created_at',
                    'billing_name' => "trim(concat(ifnull(sba.firstname, ''), ' ', ifnull(sba.lastname, '')))"
                ]
            );
    }
}
