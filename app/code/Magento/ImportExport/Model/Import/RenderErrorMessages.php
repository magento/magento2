<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Import;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\History as ModelHistory;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Report\ReportProcessorInterface;
use Magento\ImportExport\Controller\Adminhtml\ImportResult;

/**
 * Import Render Error Messages Service model.
 */
class RenderErrorMessages
{
    /**
     * @var ReportProcessorInterface
     */
    private ReportProcessorInterface $reportProcessor;

    /**
     * @var ModelHistory
     */
    private ModelHistory $historyModel;

    /**
     * @var Report
     */
    private Report $reportHelper;

    /**
     * @var Escaper|mixed
     */
    private mixed $escaper;

    /**
     * @var UrlInterface
     */
    private mixed $backendUrl;

    /**
     * @param ReportProcessorInterface $reportProcessor
     * @param ModelHistory $historyModel
     * @param Report $reportHelper
     * @param Escaper|null $escaper
     * @param UrlInterface|null $backendUrl
     */
    public function __construct(
        ReportProcessorInterface $reportProcessor,
        ModelHistory $historyModel,
        Report $reportHelper,
        ?Escaper $escaper = null,
        ?UrlInterface $backendUrl = null
    ) {
        $this->reportProcessor = $reportProcessor;
        $this->historyModel = $historyModel;
        $this->reportHelper = $reportHelper;
        $this->escaper = $escaper
            ?? ObjectManager::getInstance()->get(Escaper::class);
        $this->backendUrl = $backendUrl
            ?? ObjectManager::getInstance()->get(UrlInterface::class);
    }

    /**
     * Add Error Messages for Import
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return string
     */
    public function renderMessages(
        ProcessingErrorAggregatorInterface $errorAggregator
    ): string {
        $message = '';
        $counter = 0;
        $escapedMessages = [];
        foreach ($this->getErrorMessages($errorAggregator) as $error) {
            $escapedMessages[] = (++$counter) . '. ' . $this->escaper->escapeHtml($error);
            if ($counter >= ImportResult::LIMIT_ERRORS_MESSAGE) {
                break;
            }
        }
        if ($errorAggregator->hasFatalExceptions()) {
            foreach ($this->getSystemExceptions($errorAggregator) as $error) {
                $escapedMessages[] = $this->escaper->escapeHtml($error->getErrorMessage())
                    . ' <a href="#" onclick="$(this).next().show();$(this).hide();return false;">'
                    . __('Show more') . '</a><div style="display:none;">' . __('Additional data') . ': '
                    . $this->escaper->escapeHtml($error->getErrorDescription()) . '</div>';
            }
        }
        $message .= implode('<br>', $escapedMessages);
        return '<strong>' . __('Following Error(s) has been occurred during importing process:') . '</strong><br>'
            . '<div class="import-error-wrapper">' . __('Only the first 100 errors are shown. ')
            . '<a href="'
            . $this->createDownloadUrlImportHistoryFile($this->createErrorReport($errorAggregator))
            . '">' . __('Download full report') . '</a><br>'
            . '<div class="import-error-list">' . $message . '</div></div>';
    }

    /**
     * Get all Error Messages from Import Results
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return array
     */
    public function getErrorMessages(ProcessingErrorAggregatorInterface $errorAggregator): array
    {
        $messages = [];
        $rowMessages = $errorAggregator->getRowsGroupedByErrorCode([], [AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]);
        foreach ($rowMessages as $errorCode => $rows) {
            $messages[] = $errorCode . ' ' . __('in row(s):') . ' ' . implode(', ', $rows);
        }
        return $messages;
    }

    /**
     * Get System Generated Exception
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return ProcessingError[]
     */
    public function getSystemExceptions(ProcessingErrorAggregatorInterface $errorAggregator): array
    {
        return $errorAggregator->getErrorsByCode([AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]);
    }

    /**
     * Generate Error Report File
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return string
     */
    public function createErrorReport(ProcessingErrorAggregatorInterface $errorAggregator): string
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
     * Get Import History Url
     *
     * @param string $fileName
     * @return string
     */
    public function createDownloadUrlImportHistoryFile($fileName): string
    {
        return $this->backendUrl->getUrl(ImportResult::IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE, ['filename' => $fileName]);
    }
}
