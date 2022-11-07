<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Controller;

use Magento\Checkout\Controller\Action;
use Magento\Checkout\Controller\Express\RedirectLoginInterface;
use Magento\Checkout\Model\Session as ModelSession;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\StateException;
use Magento\Multishipping\Helper\Url;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

/**
 * Multishipping checkout controller
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Checkout extends Action implements RedirectLoginInterface
{

    /**
     * Retrieve checkout model
     *
     * @return Multishipping
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get(Multishipping::class);
    }

    /**
     * Retrieve checkout state model
     *
     * @return State
     */
    protected function _getState()
    {
        return $this->_objectManager->get(State::class);
    }

    /**
     * Retrieve checkout url helper
     *
     * @return Url
     */
    protected function _getHelper()
    {
        return $this->_objectManager->get(Url::class);
    }

    /**
     * Retrieve checkout session
     *
     * @return ModelSession
     */
    protected function _getCheckoutSession()
    {
        return $this->_objectManager->get(ModelSession::class);
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_request = $request;
        if ($this->_actionFlag->get('', 'redirectLogin')) {
            return parent::dispatch($request);
        }

        $action = $request->getActionName();

        $checkoutSessionQuote = $this->_getCheckoutSession()->getQuote();
        /**
         * Catch index action call to set some flags before checkout/type_multishipping model initialization
         */
        if ($action == 'index') {
            $checkoutSessionQuote->setIsMultiShipping(true);
            $this->_getCheckoutSession()->setCheckoutState(ModelSession::CHECKOUT_STATE_BEGIN);
        } elseif (!$checkoutSessionQuote->getIsMultiShipping() && !in_array(
            $action,
            ['login', 'register', 'success']
        )
        ) {
            $this->_redirect('*/*/index');
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return parent::dispatch($request);
        }

        if (!in_array($action, ['login', 'register'])) {
            $customerSession = $this->_objectManager->get(Session::class);
            if (!$customerSession->authenticate($this->_getHelper()->getMSLoginUrl())) {
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            }

            if (!$this->_objectManager->get(
                \Magento\Multishipping\Helper\Data::class
            )->isMultishippingCheckoutAvailable()) {
                $error = $this->_getCheckout()->getMinimumAmountError();
                $this->messageManager->addErrorMessage($error);
                $this->getResponse()->setRedirect($this->_getHelper()->getCartUrl());
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                return parent::dispatch($request);
            }
        }

        $result = $this->_preDispatchValidateCustomer();
        if ($result instanceof ResultInterface) {
            return $result;
        }

        if (!$result) {
            return $this->getResponse();
        }

        if ($this->_getCheckoutSession()->getCartWasUpdated(true)
            &&
            !in_array($action, ['index', 'login', 'register', 'addresses', 'success'])
        ) {
            $this->getResponse()->setRedirect($this->_getHelper()->getCartUrl());
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return parent::dispatch($request);
        }

        if ($action == 'success' && $this->_getCheckout()->getCheckoutSession()->getDisplaySuccess(true)) {
            return parent::dispatch($request);
        }

        try {
            $checkout = $this->_getCheckout();
        } catch (StateException $e) {
            $this->getResponse()->setRedirect($this->_getHelper()->getMSNewShippingUrl());
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return parent::dispatch($request);
        }

        $quote = $checkout->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || $quote->isVirtual()) {
            $this->getResponse()->setRedirect($this->_getHelper()->getCartUrl());
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Validate minimum amount
     *
     * @return bool
     */
    protected function _validateMinimumAmount()
    {
        if (!$this->_getCheckout()->validateMinimumAmount()) {
            $error = $this->_getCheckout()->getMinimumAmountError();
            $this->messageManager->addErrorMessage($error);
            $this->_forward('backToAddresses');
            return false;
        }
        return true;
    }

    /**
     * Returns before_auth_url redirect parameter for customer session
     *
     * @return string
     */
    public function getCustomerBeforeAuthUrl()
    {
        return $this->_objectManager->create(
            \Magento\Framework\UrlInterface::class
        )->getUrl('*/*', ['_secure' => true]);
    }

    /**
     * Returns a list of action flags [flag_key] => boolean
     *
     * @return array
     */
    public function getActionFlagList()
    {
        return ['redirectLogin' => true];
    }

    /**
     * Returns login url parameter for redirect
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_getHelper()->getMSLoginUrl();
    }

    /**
     * Returns action name which requires redirect
     *
     * @return string
     */
    public function getRedirectActionName()
    {
        return 'index';
    }
}
