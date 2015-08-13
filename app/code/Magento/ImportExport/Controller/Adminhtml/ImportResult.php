<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Import controller
 */
abstract class ImportResult extends Import
{
    const IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE = 'admin/history/download';

    /**
     * @var \Magento\ImportExport\Model\Report\ReportProcessorInterface
     */
    protected $reportProcessor;

    /**
     * @var \Magento\ImportExport\Model\History
     */
    protected $historyModel;

    /**
     * @var \Magento\ImportExport\Helper\Report
     */
    protected $reportHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor
     * @param \Magento\ImportExport\Model\History $historyModel
     * @param \Magento\ImportExport\Helper\Report $reportHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor,
        \Magento\ImportExport\Model\History $historyModel,
        \Magento\ImportExport\Helper\Report $reportHelper
    ) {
        parent::__construct($context);
        $this->reportProcessor = $reportProcessor;
        $this->historyModel = $historyModel;
        $this->reportHelper = $reportHelper;
    }

    /**
     * @param \Magento\Framework\View\Element\AbstractBlock $resultBlock
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return $this
     */
    protected function addErrorMessages(
        \Magento\Framework\View\Element\AbstractBlock $resultBlock,
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        if ($errorAggregator->getErrorsCount()) {
            $message = '';
            foreach ($this->getErrorMessages($errorAggregator) as $error) {
                $message .= $error . '<br>';
            }
            if ($errorAggregator->hasFatalExceptions()) {
                foreach ($this->getSystemExceptions($errorAggregator) as $error) {
                    $message .= $error->getErrorMessage() . '<br>'
                        . __('Additional data: ') . $error->getErrorDescription() . '<br>';
                }
            }
            $resultBlock->addNotice(
                '<strong>' . __('Following Error(s) has been occurred during importing process:') . '</strong><br>'
                . __('Only first 100 error are displayed here ')
                . '<a href="'
                . $this->createDownloadUrlImportHistoryFile($this->createErrorReport($errorAggregator))
                . '">' . __('Download full report') . '</a><br>'
                . $message
            );
        }

        return $this;
    }

    /**
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator
     * @return array
     */
    protected function getErrorMessages(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $messages = [];
        $rowMessages = $errorAggregator->getRowsGroupedByErrorCode([], [AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]);
        foreach ($rowMessages as $errorCode => $rows) {
            $messages[] = $errorCode . ' ' . __('in rows:') . ' ' . implode(', ', $rows);
        }
        return $messages;
    }

    /**
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError[]
     */
    protected function getSystemExceptions(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        return $errorAggregator->getErrorsByCode([AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]);
    }

    /**
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return string
     */
    protected function createErrorReport(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $this->historyModel->loadLastInsertItem();
        $sourceFile = $this->reportHelper->getReportAbsolutePath($this->historyModel->getData('imported_file'));
        return $this->reportProcessor->createReport($sourceFile, $errorAggregator);
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function createDownloadUrlImportHistoryFile($fileName)
    {
        return $this->getUrl(self::IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE, ['filename' => $fileName]);
    }
}
