<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
