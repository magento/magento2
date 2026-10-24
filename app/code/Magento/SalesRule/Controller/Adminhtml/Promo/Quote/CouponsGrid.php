<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
