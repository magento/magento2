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

class AddressesPost extends Checkout implements HttpPostActionInterface
{
    /**
     * Multishipping checkout process posted addresses
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('*/checkout_address/newShipping');
            return;
        }
        try {
            if ($this->getRequest()->getParam('continue', false)) {
                $this->_getCheckout()->setCollectRatesFlag(true);
                $this->_getState()->setActiveStep(State::STEP_SHIPPING);
                $this->_getState()->setCompleteStep(State::STEP_SELECT_ADDRESSES);
                $this->_redirect('*/*/shipping');
            } elseif ($this->getRequest()->getParam('new_address')) {
                $this->_redirect('*/checkout_address/newShipping');
            } else {
                $this->_redirect('*/*/addresses');
            }
            if ($shipToInfo = $this->getRequest()->getPost('ship')) {
                $this->_getCheckout()->setShippingItemsInformation($shipToInfo);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('*/*/addresses');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Data saving problem'));
            $this->_redirect('*/*/addresses');
        }
    }
}
