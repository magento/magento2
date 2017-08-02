<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

/**
 * Class \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\ApplyRules
 *
 * @since 2.0.0
 */
class ApplyRules extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * Apply rules action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->renderLayout();
    }
}
