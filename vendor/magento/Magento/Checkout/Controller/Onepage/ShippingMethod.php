<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
