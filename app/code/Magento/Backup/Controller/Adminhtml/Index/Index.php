<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

class Index extends \Magento\Backup\Controller\Adminhtml\Index
{
    /**
     * Backup list action
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Backup::system_tools_backup');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Backups'));
        $this->_addBreadcrumb(__('System'), __('System'));
        $this->_addBreadcrumb(__('Tools'), __('Tools'));
        $this->_addBreadcrumb(__('Backups'), __('Backup'));

        $this->_view->renderLayout();
    }
}
