<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

class ShippingMethod extends \Magento\Checkout\Controller\Onepage
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $this->_view->addPageLayoutHandles();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
