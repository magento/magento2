<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

/**
 * Class \Magento\Multishipping\Controller\Checkout\BackToAddresses
 *
 */
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
