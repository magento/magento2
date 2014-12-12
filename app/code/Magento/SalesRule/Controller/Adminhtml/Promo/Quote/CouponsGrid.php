<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

class CouponsGrid extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Coupon codes grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_initRule();
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Cart Price Rules'));
        $this->_view->renderLayout();
    }
}
