<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order;

/**
 * Edit order address form container block
 *
 * @api
 * @since 2.0.0
 */
class Address extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_order';
        $this->_mode = 'address';
        $this->_blockGroup = 'Magento_Sales';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Order Address'));
        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        $address = $this->_coreRegistry->registry('order_address');
        $orderId = $address->getOrder()->getIncrementId();
        if ($address->getAddressType() == 'shipping') {
            $type = __('Shipping');
        } else {
            $type = __('Billing');
        }
        return __('Edit Order %1 %2 Address', $orderId, $type);
    }

    /**
     * Back button url getter
     *
     * @return string
     * @since 2.0.0
     */
    public function getBackUrl()
    {
        $address = $this->_coreRegistry->registry('order_address');
        return $this->getUrl('sales/*/view', ['order_id' => $address ? $address->getOrder()->getId() : null]);
    }
}
