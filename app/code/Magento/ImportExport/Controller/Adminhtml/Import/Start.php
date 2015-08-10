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
     * @var \Magento\ImportExport\Model\Import
     */
    protected $importModel;

    /**
     * @var \Magento\ImportExport\Model\Report\ReportProcessorInterface
     */
    protected $reportProcessor;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\ImportExport\Model\Import $importModel,
        \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor
    ) {
        parent::__construct($context);
        $this->importModel = $importModel;
        $this->reportProcessor = $reportProcessor;
    }

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

            $this->importModel->setData($data);
            $this->importModel->importSource();
            if ($this->importModel->getErrorAggregator()->hasToBeTerminated()) {
                $this->addResultError($resultBlock, $this->importModel->getErrorAggregator());
            } else {
                $this->importModel->invalidateIndex();
                $this->addResultMessages($resultBlock, $this->importModel->getErrorAggregator());
            }
            $link = $this->reportProcessor->createReport($data[''], $this->importModel->getErrorAggregator());
            $resultBlock->addNotice("Link to Error report: <a href=\"$link\">$link</a>");

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
            $resultBlock->addError('System fatal exceptions(s):');
            foreach ($this->getSystemExceptions($errorAggregator) as $error) {
                $resultBlock->addError(
                    $error->getErrorMessage() . '<br>Additional data: ' . $error->getErrorDescription()
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
            $message = '';
            foreach ($this->getImportProcessingMessages($errorAggregator) as $error) {
                $message .= $error . '<br>';
            }
            $resultBlock->addNotice(
                '<strong>Following Error(s) has been occurred during importing process:</strong>:<br>' . $message
            );
        }

        return $this;
    }

    /**
     * @param string $originalFileName
     * @return string Link to error report file
     */
    protected function createReportFile($originalFileName)
    {
        return $this->reportProcessor->createReport($originalFileName, $this->importModel->getErrorAggregator());
    }
}
