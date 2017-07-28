<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Manage;

/**
 * Class \Magento\Newsletter\Controller\Manage\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Newsletter\Controller\Manage
{
    /**
     * Managing newsletter subscription page
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout();

        if ($block = $this->_view->getLayout()->getBlock('customer_newsletter')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Newsletter Subscription'));
        $this->_view->renderLayout();
    }
}
