<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Multishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

class BackToBilling extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Back to billing action
     *
     * @return void
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(State::STEP_BILLING);
        $this->_getState()->unsCompleteStep(State::STEP_OVERVIEW);
        $this->_redirect('*/*/billing');
    }
}
