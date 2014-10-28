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
namespace Magento\Ogone\Block;

class Placeform extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_salesOrderFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Ogone\Model\Api
     */
    protected $_ogoneApi;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Ogone\Model\Api $ogoneApi
     * @param \Magento\Sales\Model\OrderFactory $salesOrderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Ogone\Model\Api $ogoneApi,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        array $data = array()
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_ogoneApi = $ogoneApi;
        $this->_salesOrderFactory = $salesOrderFactory;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Get checkout session namespace
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Return order instance with loaded information by increment id
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function _getOrder()
    {
        if ($this->getOrder()) {
            $order = $this->getOrder();
        } elseif ($this->_checkoutSession->getLastRealOrderId()) {
            $order = $this->_salesOrderFactory->create()
                ->loadByIncrementId($this->_checkoutSession->getLastRealOrderId());
        } else {
            return null;
        }
        return $order;
    }

    /**
     * Get Form data by using ogone payment api
     *
     * @return array
     */
    public function getFormData()
    {
        return $this->_ogoneApi->getFormFields($this->_getOrder());
    }

    /**
     * Getting gateway url
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->_ogoneApi->getConfig()->getGatewayPath();
    }
}
