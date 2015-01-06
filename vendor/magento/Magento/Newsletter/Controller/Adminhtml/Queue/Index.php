<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

class Index extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Queue list action
     *
     * @return void
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
