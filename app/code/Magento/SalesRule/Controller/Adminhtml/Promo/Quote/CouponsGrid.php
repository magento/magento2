<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

/**
 * Class \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\CouponsGrid
 *
 * @since 2.0.0
 */
class CouponsGrid extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Coupon codes grid
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_initRule();
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Cart Price Rules'));
        $this->_view->renderLayout();
    }
}
