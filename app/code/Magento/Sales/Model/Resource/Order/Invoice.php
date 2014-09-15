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

use Magento\Framework\App\Resource;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Model\Resource\Attribute;
use Magento\Sales\Model\Increment as SalesIncrement;
use Magento\Sales\Model\Resource\Entity as SalesResource;
use Magento\Sales\Model\Resource\Order\Invoice\Grid as InvoiceGrid;

/**
 * Flat sales order invoice resource
 */
class Invoice extends SalesResource
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
        $this->_init('sales_flat_invoice', 'entity_id');
    }

    /**
     * @param Resource $resource
     * @param DateTime $dateTime
     * @param Attribute $attribute
     * @param SalesIncrement $salesIncrement
     * @param InvoiceGrid $gridAggregator
     */
    public function __construct(
        Resource $resource,
        DateTime $dateTime,
        Attribute $attribute,
        SalesIncrement $salesIncrement,
        InvoiceGrid $gridAggregator
    ) {
        parent::__construct($resource, $dateTime, $attribute, $salesIncrement, $gridAggregator);
    }
}
