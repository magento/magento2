<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Express\AbstractExpress;

use Magento\Paypal\Controller\Express\GetToken;

/**
 * Class Start
 */
class Start extends GetToken
{
    /**
     * Start Express Checkout by requesting initial token and dispatching customer to PayPal
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        try {
            $token = $this->getToken();
            if ($token === null) {

                return;
            }

            $url = $this->_checkout->getRedirectUrl();
            if ($token && $url) {
                $this->_initToken($token);
                $this->getResponse()->setRedirect($url);

                return;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t start Express Checkout.')
            );
        }

        $this->_redirect('checkout/cart');
    }
}
