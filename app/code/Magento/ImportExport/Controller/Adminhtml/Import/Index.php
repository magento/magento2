<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\ImportExport\Controller\Adminhtml\Import as ImportController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\ImportExport\Controller\Adminhtml\Import\Index
 *
 * @since 2.0.0
 */
class Index extends ImportController
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        $this->messageManager->addNotice(
            $this->_objectManager->get(\Magento\ImportExport\Helper\Data::class)->getMaxUploadSizeMessage()
        );
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_ImportExport::system_convert_import');
        $resultPage->getConfig()->getTitle()->prepend(__('Import/Export'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import'));
        $resultPage->addBreadcrumb(__('Import'), __('Import'));
        return $resultPage;
    }
}
