<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\History as ModelHistory;

/**
 * Import controller
 * @since 2.0.0
 */
abstract class ImportResult extends Import
{
    const IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE = '*/history/download';

    /**
     * Limit view errors
     */
    const LIMIT_ERRORS_MESSAGE = 100;

    /**
     * @var \Magento\ImportExport\Model\Report\ReportProcessorInterface
     * @since 2.0.0
     */
    protected $reportProcessor;

    /**
     * @var \Magento\ImportExport\Model\History
     * @since 2.0.0
     */
    protected $historyModel;

    /**
     * @var \Magento\ImportExport\Helper\Report
     * @since 2.0.0
     */
    protected $reportHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor
     * @param \Magento\ImportExport\Model\History $historyModel
     * @param \Magento\ImportExport\Helper\Report $reportHelper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
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
     * @since 2.0.0
     */
    protected function addErrorMessages(
        \Magento\Framework\View\Element\AbstractBlock $resultBlock,
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        if ($errorAggregator->getErrorsCount()) {
            $message = '';
            $counter = 0;
            foreach ($this->getErrorMessages($errorAggregator) as $error) {
                $message .= ++$counter . '. ' . $error . '<br>';
                if ($counter >= self::LIMIT_ERRORS_MESSAGE) {
                    break;
                }
            }
            if ($errorAggregator->hasFatalExceptions()) {
                foreach ($this->getSystemExceptions($errorAggregator) as $error) {
                    $message .= $error->getErrorMessage()
                        . ' <a href="#" onclick="$(this).next().show();$(this).hide();return false;">'
                        . __('Show more') . '</a><div style="display:none;">' . __('Additional data') . ': '
                        . $error->getErrorDescription() . '</div>';
                }
            }
            try {
                $resultBlock->addNotice(
                    '<strong>' . __('Following Error(s) has been occurred during importing process:') . '</strong><br>'
                    . '<div class="import-error-wrapper">' . __('Only the first 100 errors are shown. ')
                    . '<a href="'
                    . $this->createDownloadUrlImportHistoryFile($this->createErrorReport($errorAggregator))
                    . '">' . __('Download full report') . '</a><br>'
                    . '<div class="import-error-list">' . $message . '</div></div>'
                );
            } catch (\Exception $e) {
                foreach ($this->getErrorMessages($errorAggregator) as $errorMessage) {
                    $resultBlock->addError($errorMessage);
                }
            }
        }

        return $this;
    }

    /**
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator
     * @return array
     * @since 2.0.0
     */
    protected function getErrorMessages(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $messages = [];
        $rowMessages = $errorAggregator->getRowsGroupedByErrorCode([], [AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]);
        foreach ($rowMessages as $errorCode => $rows) {
            $messages[] = $errorCode . ' ' . __('in row(s):') . ' ' . implode(', ', $rows);
        }
        return $messages;
    }

    /**
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError[]
     * @since 2.0.0
     */
    protected function getSystemExceptions(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        return $errorAggregator->getErrorsByCode([AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]);
    }

    /**
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return string
     * @since 2.0.0
     */
    protected function createErrorReport(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $this->historyModel->loadLastInsertItem();
        $sourceFile = $this->reportHelper->getReportAbsolutePath($this->historyModel->getImportedFile());
        $writeOnlyErrorItems = true;
        if ($this->historyModel->getData('execution_time') == ModelHistory::IMPORT_VALIDATION) {
            $writeOnlyErrorItems = false;
        }
        $fileName = $this->reportProcessor->createReport($sourceFile, $errorAggregator, $writeOnlyErrorItems);
        $this->historyModel->addErrorReportFile($fileName);
        return $fileName;
    }

    /**
     * @param string $fileName
     * @return string
     * @since 2.0.0
     */
    protected function createDownloadUrlImportHistoryFile($fileName)
    {
        return $this->getUrl(self::IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE, ['filename' => $fileName]);
    }
}
