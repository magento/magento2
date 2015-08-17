<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\ImportExport\Controller\Adminhtml\Import as ImportController;
use Magento\Framework\Controller\ResultFactory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

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
            $importModel->importSource();

            if ($importModel->getErrorAggregator()->hasToBeTerminated()) {
                $this->addResultError($resultBlock, $importModel->getErrorAggregator());
            } else {
                $importModel->invalidateIndex();
                $this->addResultMessages($resultBlock, $importModel->getErrorAggregator());
            }

            return $resultLayout;
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }

    /**
     * @param \Magento\Framework\View\Element\AbstractBlock $resultBlock
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return $this
     */
    protected function addResultError(
        \Magento\Framework\View\Element\AbstractBlock $resultBlock,
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        if ($errorAggregator->isErrorLimitExceeded()) {
            $resultBlock->addError('Maximum error count has been reached:');
            foreach ($this->getImportProcessingMessages($errorAggregator) as $error) {
                $resultBlock->addError($error);
            }
        }

        if ($errorAggregator->hasFatalExceptions()) {
            foreach ($this->getSystemExceptions($errorAggregator) as $error) {
                $resultBlock->addError(
                    $error->getErrorMessage()
                    . '<a href="#" onclick="$(this).next().show();$(this).hide();return false;">'
                    . __('Show more') . '</a><div style="display:none;">' . __('Additional data') . ': '
                    . $error->getErrorDescription() . '</div>'
                );
            }
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\View\Element\AbstractBlock $resultBlock
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return $this
     */
    protected function addResultMessages(
        \Magento\Framework\View\Element\AbstractBlock $resultBlock,
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        $resultBlock
            ->addAction('show', 'import_validation_container')
            ->addAction('innerHTML', 'import_validation_container_header', __('Status'))
            ->addAction('hide', ['edit_form', 'upload_button', 'messages'])
            ->addSuccess(__('Import successfully done'));

        if ($errorAggregator->getErrorsCount()) {
            $resultBlock->addNotice('Following Error(s) has been occurred during importing process:');
            foreach ($this->getImportProcessingMessages($errorAggregator) as $error) {
                $resultBlock->addNotice($error);
            }
        }

        return $this;
    }
}
