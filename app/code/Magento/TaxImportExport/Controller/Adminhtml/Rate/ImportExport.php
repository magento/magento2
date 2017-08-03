<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxImportExport\Controller\Adminhtml\Rate;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\TaxImportExport\Controller\Adminhtml\Rate\ImportExport
 *
 */
class ImportExport extends \Magento\TaxImportExport\Controller\Adminhtml\Rate
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_TaxImportExport::import_export';

    /**
     * Import and export Page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Magento_TaxImportExport::system_convert_tax');
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(
                \Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExportHeader::class
            )
        );
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(\Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExport::class)
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Tax Zones and Rates'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import and Export Tax Rates'));
        return $resultPage;
    }
}
