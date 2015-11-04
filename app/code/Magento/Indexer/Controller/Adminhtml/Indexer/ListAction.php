<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Controller\Adminhtml\Indexer;

class ListAction extends \Magento\Indexer\Controller\Adminhtml\Indexer
{
    /**
     * Display processes grid action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Indexer::system_index');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Index Management'));
        $this->_view->renderLayout();
    }
}
