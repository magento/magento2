<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Framework\App\ResponseInterface;

class Shipping extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Multishipping checkout shipping information page
     *
     * @return  ResponseInterface|void
     */
    public function execute()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }

        if (!$this->_getState()->getCompleteStep(State::STEP_SELECT_ADDRESSES)) {
            return $this->_redirect('*/*/addresses');
        }

        $this->_getState()->setActiveStep(State::STEP_SHIPPING);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
