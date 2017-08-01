<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

/**
 * Class \Magento\Email\Controller\Adminhtml\Email\Template\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * Index action
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
        $this->_setActiveMenu('Magento_Email::template');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Email Templates'));
        $this->_addBreadcrumb(__('Transactional Emails'), __('Transactional Emails'));
        $this->_view->renderLayout();
    }
}
