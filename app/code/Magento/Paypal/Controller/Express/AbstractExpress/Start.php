<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Express\AbstractExpress;

use Magento\Paypal\Controller\Express\GetToken;

/**
 * Class Start
 * @since 2.0.0
 */
class Start extends GetToken
{
    /**
     * Start Express Checkout by requesting initial token and dispatching customer to PayPal
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
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
