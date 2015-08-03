<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflow;

class Form extends \Magento\Paypal\Controller\Payflow
{
    /**
     * Submit transaction to Payflow getaway into iframe
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        $this->getResponse()->setHeader('P3P', 'CP="CAO PSA OUR"');
        $this->_view->loadLayout(false)->renderLayout();
        $layout = $this->_view->getLayout();
    }
}
