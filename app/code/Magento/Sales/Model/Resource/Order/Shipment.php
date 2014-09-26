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

use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Model\Resource\Attribute;
use Magento\Framework\App\Resource as AppResource;
use Magento\Sales\Model\Increment as SalesIncrement;
use Magento\Sales\Model\Resource\Entity as SalesResource;
use Magento\Sales\Model\Resource\Order\Shipment\Grid as ShipmentGrid;

/**
 * Flat sales order shipment resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shipment extends SalesResource
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_resource';

    /**
     * Fields that should be serialized before persistence
     *
     * @var array
     */
    protected $_serializableFields = ['packages' => [[], []]];

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_flat_shipment', 'entity_id');
    }

    /**
     * Constructor
     *
     * @param AppResource $resource
     * @param DateTime $dateTime
     * @param Attribute $attribute
     * @param SalesIncrement $salesIncrement
     * @param ShipmentGrid $gridAggregator
     */
    public function __construct(
        AppResource $resource,
        DateTime $dateTime,
        Attribute $attribute,
        SalesIncrement $salesIncrement,
        ShipmentGrid $gridAggregator
    ) {
        parent::__construct($resource, $dateTime, $attribute, $salesIncrement, $gridAggregator);
    }
}
