<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\ImportExport\Controller\Adminhtml\Import as ImportController;
use Magento\Framework\Controller\ResultFactory;

class Start extends ImportController
{
    /**
     * Start import process action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            /** @var \Magento\Framework\View\Result\Layout $resultLayout */
            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            /** @var $resultBlock \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result */
            $resultBlock = $resultLayout->getLayout()->getBlock('import.frame.result');
            /** @var $importModel \Magento\ImportExport\Model\Import */
            $importModel = $this->_objectManager->create('Magento\ImportExport\Model\Import');

            $importModel->setData($data);
            $result = $importModel->importSource();

            if ($result) {
                $importModel->invalidateIndex();
                $resultBlock
                    ->addNotice($this->getImportProcessingMessages($importModel->getErrorAggregator()))
                    ->addAction('show', 'import_validation_container')
                    ->addAction('innerHTML', 'import_validation_container_header', __('Status'))
                    ->addAction('hide', ['edit_form', 'upload_button', 'messages'])
                    ->addSuccess(__('Import successfully done'));
            } else {
                $exceptions = $this->getImportProcessingMessages($importModel->getErrorAggregator());
                foreach ($exceptions as $error) {
                    $resultBlock->addError($error);
                }
                $systemsExceptions = $this->getSystemExceptions($importModel->getErrorAggregator());
                foreach ($systemsExceptions as $error) {
                    $resultBlock->addError(
                        $error->getErrorMessage() . '<br>Additional data: ' . $error->getErrorDescription()
                    );
                }
            }

            return $resultLayout;
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }
}
