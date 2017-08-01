<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Framework\App\ResponseInterface;

/**
 * Class \Magento\Multishipping\Controller\Checkout\Shipping
 *
 * @since 2.0.0
 */
class Shipping extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Multishipping checkout shipping information page
     *
     * @return  ResponseInterface|void
     * @since 2.0.0
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
        $this->_view->renderLayout();
    }
}
