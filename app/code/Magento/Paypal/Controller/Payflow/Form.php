<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflow;

use Magento\Paypal\Controller\Payflow;

/**
 * Class Form
 */
class Form extends Payflow
{
    /**
     * Submit transaction to Payflow getaway into iframe
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setHeader('P3P', 'CP="CAO PSA OUR"');
        $this->_view->loadLayout(false)->renderLayout();
    }
}
