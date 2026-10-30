<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

class CancelWizard extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * Wizard cancel action
     *
     * @return void
     */
    public function execute()
    {
        $this->_redirect('*/*/index');
    }
}
