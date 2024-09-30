<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Controller\Checkout;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Multishipping\Controller\Checkout;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

class ShippingPost extends Checkout implements HttpPostActionInterface
{
    /**
     * Shipping action
     *
     * @return void
     */
    public function execute()
    {
        $shippingMethods = $this->getRequest()->getPost('shipping_method');
        try {
            $this->_eventManager->dispatch(
                'checkout_controller_multishipping_shipping_post',
                ['request' => $this->getRequest(), 'quote' => $this->_getCheckout()->getQuote()]
            );
            $this->_getCheckout()->setShippingMethods($shippingMethods);
            $this->_getState()->setActiveStep(State::STEP_BILLING);
            $this->_getState()->setCompleteStep(State::STEP_SHIPPING);
            $this->_redirect('*/*/billing');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*/shipping');
        }
    }
}
