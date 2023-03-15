<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml sales shipment comment view block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Shipping\Block\Adminhtml\View;

use Magento\Backend\Block\Text\ListText;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Context as ElementContext;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment as OrderShipment;

/**
 * @api
 * @since 100.0.2
 */
class Comments extends ListText
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param ElementContext $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        ElementContext $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve shipment model instance
     *
     * @return OrderShipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * Retrieve invoice order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }

    /**
     * Retrieve source
     *
     * @return OrderShipment
     */
    public function getSource()
    {
        return $this->getShipment();
    }
}
