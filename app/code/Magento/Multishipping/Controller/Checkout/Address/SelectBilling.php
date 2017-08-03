<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout\Address;

/**
 * Class \Magento\Multishipping\Controller\Checkout\Address\SelectBilling
 *
 * @since 2.0.0
 */
class SelectBilling extends \Magento\Multishipping\Controller\Checkout\Address
{
    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(
            \Magento\Multishipping\Model\Checkout\Type\Multishipping\State::STEP_BILLING
        );
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
