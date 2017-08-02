<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Controller\Adminhtml\Indexer;

/**
 * Class \Magento\Indexer\Controller\Adminhtml\Indexer\ListAction
 *
 * @since 2.0.0
 */
class ListAction extends \Magento\Indexer\Controller\Adminhtml\Indexer
{
    /**
     * Display processes grid action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Indexer::system_index');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Index Management'));
        $this->_view->renderLayout();
    }
}
