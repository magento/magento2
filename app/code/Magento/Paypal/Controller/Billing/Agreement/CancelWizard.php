<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

/**
 * Class \Magento\Paypal\Controller\Billing\Agreement\CancelWizard
 *
 * @since 2.0.0
 */
class CancelWizard extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * Wizard cancel action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_redirect('*/*/index');
    }
}
