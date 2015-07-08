<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\Directpost;

use Magento\Authorizenet\Model\Directpost;
use Magento\Sales\Model\Order;

/**
 * Authorize.net directpayment observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Observer
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Authorizenet helper
     *
     * @var \Magento\Authorizenet\Helper\Data
     */
    protected $_authorizenetData;

    /**
     * @var Directpost
     */
    protected $_payment;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Session
     */
    protected $_session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Authorizenet\Helper\Data $authorizenetData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Directpost $payment
     * @param \Magento\Authorizenet\Model\Directpost\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Authorizenet\Helper\Data $authorizenetData,
        \Magento\Framework\Registry $coreRegistry,
        Directpost $payment,
        \Magento\Authorizenet\Model\Directpost\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_authorizenetData = $authorizenetData;
        $this->_payment = $payment;
        $this->_session = $session;
        $this->_storeManager = $storeManager;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function saveOrderAfterSubmit(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getData('order');
        $this->_coreRegistry->register('directpost_order', $order, true);

        return $this;
    }

    /**
     * Set data for response of frontend saveOrder action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function addFieldsToResponse(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $this->_coreRegistry->registry('directpost_order');

        if (!$order || !$order->getId()) {
            return $this;
        }

        $payment = $order->getPayment();

        if (!$payment || $payment->getMethod() != $this->_payment->getCode()) {
            return $this;
        }

        $result = $observer->getData('result')->getData();

        if (!empty($result['error'])) {
            return $this;
        }

        // if success, then set order to session and add new fields
        $this->_session->addCheckoutOrderIncrementId($order->getIncrementId());
        $this->_session->setLastOrderIncrementId($order->getIncrementId());

        $requestToAuthorizenet = $payment->getMethodInstance()
            ->generateRequestFromOrder($order);
        $requestToAuthorizenet->setControllerActionName(
            $observer->getData('action')
                ->getRequest()
                ->getControllerName()
        );
        $requestToAuthorizenet->setIsSecure(
            (string)$this->_storeManager->getStore()
                ->isCurrentlySecure()
        );

        $result[$this->_payment->getCode()] = ['fields' => $requestToAuthorizenet->getData()];

        $observer->getData('result')->setData($result);

        return $this;
    }

    /**
     * Update all edit increments for all orders if module is enabled.
     * Needed for correct work of edit orders in Admin area.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function updateAllEditIncrements(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getData('order');
        $this->_authorizenetData->updateOrderEditIncrements($order);

        return $this;
    }
}
