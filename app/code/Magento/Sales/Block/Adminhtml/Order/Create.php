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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order create
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Sales\Block\Adminhtml\Order;

class Create extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        array $data = array()
    ) {
        $this->_sessionQuote = $sessionQuote;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'order';
        $this->_mode = 'create';

        parent::_construct();

        $this->setId('sales_order_create');

        $customerId = $this->_sessionQuote->getCustomerId();
        $storeId    = $this->_sessionQuote->getStoreId();


        $this->_updateButton('save', 'label', __('Submit Order'));
        $this->_updateButton('save', 'onclick', 'order.submit()');
        $this->_updateButton('save', 'class', 'primary');
        // Temporary solution, unset button widget. Will have to wait till jQuery migration is complete
        $this->_updateButton('save', 'data_attribute', array());

        $this->_updateButton('save', 'id', 'submit_order_top_button');
        if (is_null($customerId) || !$storeId) {
            $this->_updateButton('save', 'style', 'display:none');
        }

        $this->_updateButton('back', 'id', 'back_order_top_button');
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getBackUrl() . '\')');

        $this->_updateButton('reset', 'id', 'reset_order_top_button');

        if (is_null($customerId)) {
            $this->_updateButton('reset', 'style', 'display:none');
        } else {
            $this->_updateButton('back', 'style', 'display:none');
        }

        $confirm = __('Are you sure you want to cancel this order?');
        $this->_updateButton('reset', 'label', __('Cancel'));
        $this->_updateButton('reset', 'class', 'cancel');
        $this->_updateButton('reset', 'onclick', 'deleteConfirm(\''.$confirm.'\', \'' . $this->getCancelUrl() . '\')');

        $pageTitle = $this->getLayout()->createBlock('Magento\Sales\Block\Adminhtml\Order\Create\Header')->toHtml();
        if (is_object($this->getLayout()->getBlock('page-title'))) {
            $this->getLayout()->getBlock('page-title')->setPageTitle($pageTitle);
        }
    }

    /**
     * Prepare header html
     *
     * @return string
     */
    public function getHeaderHtml()
    {
        $out = '<div id="order-header">'
            . $this->getLayout()->createBlock('Magento\Sales\Block\Adminhtml\Order\Create\Header')->toHtml()
            . '</div>';
        return $out;
    }

    public function getHeaderWidth()
    {
        return 'width: 70%;';
    }

    /**
     * Retrieve quote session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
    }

    public function getCancelUrl()
    {
        if ($this->_sessionQuote->getOrder()->getId()) {
            $url = $this->getUrl('sales/order/view', array(
                'order_id' => $this->_sessionQuote->getOrder()->getId()
            ));
        } else {
            $url = $this->getUrl('sales/*/cancel');
        }

        return $url;
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('sales/' . $this->_controller . '/');
    }
}
