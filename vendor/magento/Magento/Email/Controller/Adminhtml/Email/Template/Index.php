<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

class Index extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * Index action
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
        $this->_setActiveMenu('Magento_Email::template');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Email Templates'));
        $this->_addBreadcrumb(__('Transactional Emails'), __('Transactional Emails'));
        $this->_view->renderLayout();
    }
}
