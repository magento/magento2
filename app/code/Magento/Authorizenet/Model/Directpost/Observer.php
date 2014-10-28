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
namespace Magento\Authorizenet\Model\Directpost;

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
     * Core helper
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * Authorizenet helper
     *
     * @var \Magento\Authorizenet\Helper\Data
     */
    protected $_authorizenetData;

    /**
     * @var \Magento\Authorizenet\Model\Directpost
     */
    protected $_payment;

    /**
     * @var \Magento\Authorizenet\Model\Directpost\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Authorizenet\Helper\Data $authorizenetData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Authorizenet\Model\Directpost $payment
     * @param \Magento\Authorizenet\Model\Directpost\Session $session
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Authorizenet\Helper\Data $authorizenetData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Authorizenet\Model\Directpost $payment,
        \Magento\Authorizenet\Model\Directpost\Session $session,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_authorizenetData = $authorizenetData;
        $this->_coreData = $coreData;
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
        /* @var $order \Magento\Sales\Model\Order */
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
    public function addAdditionalFieldsToResponseFrontend(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $this->_coreRegistry->registry('directpost_order');

        if ($order && $order->getId()) {
            $payment = $order->getPayment();
            if ($payment && $payment->getMethod() == $this->_payment->getCode()) {
                /** @var \Magento\Checkout\Controller\Action $controller */
                $controller = $observer->getEvent()->getData('controller_action');
                $request = $controller->getRequest();
                $response = $controller->getResponse();
                $result = $this->_coreData->jsonDecode($response->getBody('default'));

                if (empty($result['error'])) {
                    $payment = $order->getPayment();
                    //if success, then set order to session and add new fields
                    $this->_session->addCheckoutOrderIncrementId($order->getIncrementId());
                    $this->_session->setLastOrderIncrementId($order->getIncrementId());
                    $requestToAuthorizenet = $payment->getMethodInstance()->generateRequestFromOrder($order);
                    $requestToAuthorizenet->setControllerActionName($request->getControllerName());
                    $requestToAuthorizenet->setIsSecure((string)$this->_storeManager->getStore()->isCurrentlySecure());

                    $result['directpost'] = array('fields' => $requestToAuthorizenet->getData());

                    $response->clearHeader('Location');
                    $response->representJson($this->_coreData->jsonEncode($result));
                }
            }
        }

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
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getData('order');
        $this->_authorizenetData->updateOrderEditIncrements($order);

        return $this;
    }
}
