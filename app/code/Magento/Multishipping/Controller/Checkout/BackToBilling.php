<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

/**
 * Class \Magento\Multishipping\Controller\Checkout\BackToBilling
 *
 * @since 2.0.0
 */
class BackToBilling extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Back to billing action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(State::STEP_BILLING);
        $this->_getState()->unsCompleteStep(State::STEP_OVERVIEW);
        $this->_redirect('*/*/billing');
    }
}
