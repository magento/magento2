<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Framework\App\ResponseInterface;

class Billing extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Validation of selecting of billing address
     *
     * @return boolean
     */
    protected function _validateBilling()
    {
        if (!$this->_getCheckout()->getQuote()->getBillingAddress()->getFirstname()) {
            $this->_redirect('*/checkout_address/selectBilling');
            return false;
        }
        return true;
    }

    /**
     * Multishipping checkout billing information page
     *
     * @return void|ResponseInterface
     */
    public function execute()
    {
        if (!$this->_validateBilling()) {
            return;
        }

        if (!$this->_validateMinimumAmount()) {
            return;
        }

        if (!$this->_getState()->getCompleteStep(State::STEP_SHIPPING)) {
            return $this->_redirect('*/*/shipping');
        }

        $this->_getState()->setActiveStep(State::STEP_BILLING);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
