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

namespace Magento\Sales\Model\Resource;

use Magento\Sales\Model\Resource\GridInterface;
use Magento\Sales\Model\Resource\Order\Grid as OrderGrid;
use Magento\Sales\Model\Resource\Order\Invoice\Grid as InvoiceGrid;
use Magento\Sales\Model\Resource\Order\Shipment\Grid as ShipmentGrid;
use Magento\Sales\Model\Resource\Order\Creditmemo\Grid as CreditmemoGrid;

class GridPool
{
    /**
     * @var GridInterface[]
     */
    protected $grids;

    /**
     * @param OrderGrid $orderGrid
     * @param InvoiceGrid $invoiceGrid
     * @param ShipmentGrid $shipmentGrid
     * @param CreditmemoGrid $creditmemoGrid
     */
    public function __construct(
        OrderGrid $orderGrid,
        InvoiceGrid $invoiceGrid,
        ShipmentGrid $shipmentGrid,
        CreditmemoGrid $creditmemoGrid
    ) {
        $this->grids = [
            'order_grid' => $orderGrid,
            'invoice_grid' => $invoiceGrid,
            'shipment_grid' => $shipmentGrid,
            'creditmemo_grid' => $creditmemoGrid
        ];
    }

    /**
     * Refresh grids list
     *
     * @param int $orderId
     * @return $this
     */
    public function refreshByOrderId($orderId)
    {
        foreach ($this->grids as $grid) {
            $grid->refresh($orderId, 'sfo.entity_id');
        }
        return $this;
    }
}
