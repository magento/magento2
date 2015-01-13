<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

class Index extends \Magento\ImportExport\Controller\Adminhtml\Import
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->messageManager->addNotice(
            $this->_objectManager->get('Magento\ImportExport\Helper\Data')->getMaxUploadSizeMessage()
        );
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_ImportExport::system_convert_import');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import/Export'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import'));
        $this->_addBreadcrumb(__('Import'), __('Import'));
        $this->_view->renderLayout();
    }
}
