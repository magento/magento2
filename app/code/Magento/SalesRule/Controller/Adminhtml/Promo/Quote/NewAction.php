<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

/**
 * Class \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\NewAction
 *
 * @since 2.0.0
 */
class NewAction extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * New promo quote action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
