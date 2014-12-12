<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
