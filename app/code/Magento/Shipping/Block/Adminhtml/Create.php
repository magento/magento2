<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context as WidgetContext;
use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Shipment as OrderShipment;

/**
 * Adminhtml shipment create
 *
 * @api
 * @since 100.0.2
 */
class Create extends FormContainer
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param WidgetContext $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        WidgetContext $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'order_id';
        $this->_mode = 'create';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
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
     * @return string
     */
    public function getHeaderText()
    {
        $header = __('New Shipment for Order #%1', $this->getShipment()->getOrder()->getRealOrderId());
        return $header;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            'sales/order/view',
            ['order_id' => $this->getShipment() ? $this->getShipment()->getOrderId() : null]
        );
    }
}
