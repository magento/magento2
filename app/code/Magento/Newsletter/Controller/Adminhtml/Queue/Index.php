<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Queue\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Queue list action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Newsletter::newsletter_queue');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Newsletter Queue'));
        $this->_addBreadcrumb(__('Newsletter Queue'), __('Newsletter Queue'));

        $this->_view->renderLayout();
    }
}
