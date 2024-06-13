<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

class BackToAddresses extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(State::STEP_SELECT_ADDRESSES);
        $this->_getState()->unsCompleteStep(State::STEP_SHIPPING);
        $this->_redirect('*/*/addresses');
    }
}
