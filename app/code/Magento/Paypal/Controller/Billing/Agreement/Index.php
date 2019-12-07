<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Billing\Agreement;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Index Controller.
 */
class Index extends \Magento\Paypal\Controller\Billing\Agreement implements HttpGetActionInterface
{
    /**
     * View billing agreements
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Billing Agreements'));
        $this->_view->renderLayout();
    }
}
