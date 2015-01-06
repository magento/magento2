<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ImportExport\Controller\Adminhtml\Export;

class Index extends \Magento\ImportExport\Controller\Adminhtml\Export
{
    /**
     * Index action.
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_ImportExport::system_convert_export');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import/Export'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Export'));
        $this->_addBreadcrumb(__('Export'), __('Export'));

        $this->_view->renderLayout();
    }
}
