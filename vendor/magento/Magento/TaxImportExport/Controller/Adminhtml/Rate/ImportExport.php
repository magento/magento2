<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TaxImportExport\Controller\Adminhtml\Rate;

class ImportExport extends \Magento\TaxImportExport\Controller\Adminhtml\Rate
{
    /**
     * Import and export Page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_TaxImportExport::system_convert_tax'
        )->_addContent(
            $this->_view->getLayout()->createBlock('Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExportHeader')
        )->_addContent(
            $this->_view->getLayout()->createBlock('Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExport')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Tax Zones and Rates'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Import and Export Tax Rates'));
        $this->_view->renderLayout();
    }
}
