<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

/**
 * Class \Magento\Paypal\Controller\Billing\Agreement\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Paypal\Controller\Billing\Agreement
{
    /**
     * View billing agreements
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Billing Agreements'));
        $this->_view->renderLayout();
    }
}
