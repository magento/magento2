<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
namespace Magefan\LoginAsCustomer\Controller\Login;

/**
 * LoginAsCustomer login action
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $login = $this->_initLogin();
        if (!$login) {
            $this->_redirect('/');
            return;
        }

        try {
            $session = $this->_objectManager
                ->create('\Magento\Customer\Model\Session');
            if ($session->getId()) {
                /* Logout if logged in */
                $session->logout();
            } else {
                /* Remove items from cart */
                $cart = $this->_objectManager
                    ->create('\Magento\Checkout\Model\Cart');
                foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
                    $cart->removeItem($item->getId());
                }
                $cart->save();
            }
            /* Log in */
            $login->authenticateCustomer();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->messageManager->addSuccess(
            __('You are logged in as customer: %1', $login->getCustomer()->getName())
        );

        $this->_redirect('*/*/proceed');
    }

    /**
     * Init login info
     * @return false || \Magefan\LoginAsCustomer\Model\Login
     */
    protected function _initLogin()
    {
        $secret = $this->getRequest()->getParam('secret');
        if (!$secret) {
            $this->messageManager->addError(__('Cannot login to account. No secret key provided.'));
            return false;
        }

        $login = $this->_objectManager
            ->create('\Magefan\LoginAsCustomer\Model\Login')
            ->loadNotUsed($secret);

        if ($login->getId()) {
            return $login;
        } else {
            $this->messageManager->addError(__('Cannot login to account. Secret key is not valid.'));
            return false;
        }
    }

}
