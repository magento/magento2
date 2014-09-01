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
namespace Magento\Sales\Model\Resource\Order\Status\History;

use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order;

/**
 * Flat sales order status history collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\Resource\Order\Collection\AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_status_history_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'order_status_history_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Order\Status\History', 'Magento\Sales\Model\Resource\Order\Status\History');
    }

    /**
     * Get history object collection for specified instance (order, shipment, invoice or credit memo)
     * Parameter instance may be one of the following types: \Magento\Sales\Model\Order,
     * \Magento\Sales\Model\Order\Creditmemo, \Magento\Sales\Model\Order\Invoice, \Magento\Sales\Model\Order\Shipment
     *
     * @param AbstractModel $instance
     * @return \Magento\Sales\Model\Order\Status\History|null
     */
    public function getUnnotifiedForInstance($instance)
    {
        if (!$instance instanceof Order) {
            $instance = $instance->getOrder();
        }
        $this->setOrderFilter(
            $instance
        )->setOrder(
            'created_at',
            'desc'
        )->addFieldToFilter(
            'entity_name',
            $instance->getEntityType()
        )->addFieldToFilter(
            'is_customer_notified',
            0
        )->setPageSize(
            1
        );
        foreach ($this->getItems() as $historyItem) {
            return $historyItem;
        }
        return null;
    }
}
