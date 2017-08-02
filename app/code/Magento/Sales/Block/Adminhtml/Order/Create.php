<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order;

/**
 * Adminhtml sales order create
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Create extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     * @since 2.0.0
     */
    protected $_sessionQuote;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        array $data = []
    ) {
        $this->_sessionQuote = $sessionQuote;
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
        $this->_controller = 'order';
        $this->_mode = 'create';

        parent::_construct();

        $this->setId('sales_order_create');

        $customerId = $this->_sessionQuote->getCustomerId();
        $storeId = $this->_sessionQuote->getStoreId();

        $this->buttonList->update('save', 'label', __('Submit Order'));
        $this->buttonList->update('save', 'onclick', 'order.submit()');
        $this->buttonList->update('save', 'class', 'primary');
        // Temporary solution, unset button widget. Will have to wait till jQuery migration is complete
        $this->buttonList->update('save', 'data_attribute', []);

        $this->buttonList->update('save', 'id', 'submit_order_top_button');
        if ($customerId === null || !$storeId) {
            $this->buttonList->update('save', 'style', 'display:none');
        }

        $this->buttonList->update('back', 'id', 'back_order_top_button');
        $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getBackUrl() . '\')');

        $this->buttonList->update('reset', 'id', 'reset_order_top_button');

        if ($customerId === null) {
            $this->buttonList->update('reset', 'style', 'display:none');
        } else {
            $this->buttonList->update('back', 'style', 'display:none');
        }

        $confirm = __('Are you sure you want to cancel this order?');
        $this->buttonList->update('reset', 'label', __('Cancel'));
        $this->buttonList->update('reset', 'class', 'cancel');
        $this->buttonList->update(
            'reset',
            'onclick',
            'deleteConfirm(\'' . $confirm . '\', \'' . $this->getCancelUrl() . '\')'
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $pageTitle = $this->getLayout()->createBlock(
            \Magento\Sales\Block\Adminhtml\Order\Create\Header::class
        )->toHtml();
        if (is_object($this->getLayout()->getBlock('page.title'))) {
            $this->getLayout()->getBlock('page.title')->setPageTitle($pageTitle);
        }
        return parent::_prepareLayout();
    }

    /**
     * Prepare header html
     *
     * @return string
     * @since 2.0.0
     */
    public function getHeaderHtml()
    {
        $out = '<div id="order-header">' . $this->getLayout()->createBlock(
            \Magento\Sales\Block\Adminhtml\Order\Create\Header::class
        )->toHtml() . '</div>';
        return $out;
    }

    /**
     * Get header width
     *
     * @return string
     * @since 2.0.0
     */
    public function getHeaderWidth()
    {
        return 'width: 70%;';
    }

    /**
     * Retrieve quote session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     * @since 2.0.0
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
    }

    /**
     * Get cancel url
     *
     * @return string
     * @since 2.0.0
     */
    public function getCancelUrl()
    {
        if ($this->_sessionQuote->getOrder()->getId()) {
            $url = $this->getUrl('sales/order/view', ['order_id' => $this->_sessionQuote->getOrder()->getId()]);
        } else {
            $url = $this->getUrl('sales/*/cancel');
        }

        return $url;
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     * @since 2.0.0
     */
    public function getBackUrl()
    {
        return $this->getUrl('sales/' . $this->_controller . '/');
    }
}
