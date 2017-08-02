<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflow;

use Magento\Paypal\Controller\Payflow;

/**
 * Class Form
 * @since 2.0.0
 */
class Form extends Payflow
{
    /**
     * Submit transaction to Payflow getaway into iframe
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->getResponse()->setHeader('P3P', 'CP="CAO PSA OUR"');
        $this->_view->loadLayout(false)->renderLayout();
    }
}
