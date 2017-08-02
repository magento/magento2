<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

/**
 * Class \Magento\Multishipping\Controller\Checkout\Success
 *
 * @since 2.0.0
 */
class Success extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Multishipping checkout success page
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        if (!$this->_getState()->getCompleteStep(State::STEP_OVERVIEW)) {
            $this->_redirect('*/*/addresses');
            return;
        }

        $this->_view->loadLayout();
        $ids = $this->_getCheckout()->getOrderIds();
        $this->_eventManager->dispatch('multishipping_checkout_controller_success_action', ['order_ids' => $ids]);
        $this->_view->renderLayout();
    }
}
