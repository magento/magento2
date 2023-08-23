<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Controller\Checkout\Address;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Multishipping\Controller\Checkout\Address;

/**
 * Controller for Billing Address that was successfully saved.
 */
class SaveBillingFromList extends Address implements HttpGetActionInterface
{
    /**
     * Reimport saved Address to Quote if it has same ID as current Billing Address.
     *
     * @return void
     */
    public function execute(): void
    {
        if ($addressId = (int)$this->getRequest()->getParam('id')) {
            $checkout = $this->_getCheckout();
            if ((int)$checkout->getQuote()->getBillingAddress()->getCustomerAddressId() === $addressId) {
                $checkout->setQuoteCustomerBillingAddress($addressId);
            }
        }
        $this->_redirect('*/*/selectBilling');
    }
}
