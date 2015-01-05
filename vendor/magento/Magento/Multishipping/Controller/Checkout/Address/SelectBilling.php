<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Multishipping\Controller\Checkout\Address;

class SelectBilling extends \Magento\Multishipping\Controller\Checkout\Address
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(
            \Magento\Multishipping\Model\Checkout\Type\Multishipping\State::STEP_BILLING
        );
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
