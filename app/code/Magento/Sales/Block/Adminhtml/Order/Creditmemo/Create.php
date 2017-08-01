<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Creditmemo;

/**
 * Adminhtml creditmemo create
 *
 * @api
 * @since 2.0.0
 */
class Create extends \Magento\Backend\Block\Widget\Form\Container
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
        $this->_objectId = 'order_id';
        $this->_controller = 'adminhtml_order_creditmemo';
        $this->_mode = 'create';

        parent::_construct();

        $this->buttonList->remove('delete');
        $this->buttonList->remove('save');
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     * @since 2.0.0
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    /**
     * Get header text
     *
     * @return string
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        if ($this->getCreditmemo()->getInvoice()) {
            $header = __('New Credit Memo for Invoice #%1', $this->getCreditmemo()->getInvoice()->getIncrementId());
        } else {
            $header = __('New Credit Memo for Order #%1', $this->getCreditmemo()->getOrder()->getRealOrderId());
        }

        return $header;
    }

    /**
     * Get back url
     *
     * @return string
     * @since 2.0.0
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            'sales/order/view',
            ['order_id' => $this->getCreditmemo() ? $this->getCreditmemo()->getOrderId() : null]
        );
    }
}
