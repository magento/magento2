<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\History as ModelHistory;
use Magento\Framework\Escaper;
use Magento\Framework\App\ObjectManager;
use Magento\ImportExport\Model\Import\RenderErrorMessages;
use Magento\ImportExport\Model\Report\ReportProcessorInterface;

/**
 * Import controller
 */
abstract class ImportResult extends Import
{
    public const IMPORT_HISTORY_FILE_DOWNLOAD_ROUTE = '*/history/download';

    /**
     * Limit view errors
     */
    public const LIMIT_ERRORS_MESSAGE = 100;

    /**
     * @var ReportProcessorInterface
     */
    protected ReportProcessorInterface $reportProcessor;

    /**
     * @var ModelHistory
     */
    protected ModelHistory $historyModel;

    /**
     * @var Report
     */
    protected Report $reportHelper;

    /**
     * @var Escaper|null
     */
    protected $escaper;

    /**
     * @var RenderErrorMessages
     */
    private RenderErrorMessages $renderErrorMessages;

    /**
     * @param Context $context
     * @param ReportProcessorInterface $reportProcessor
     * @param ModelHistory $historyModel
     * @param Report $reportHelper
     * @param Escaper|null $escaper
     * @param RenderErrorMessages|null $renderErrorMessages
     */
    public function __construct(
        Context $context,
        ReportProcessorInterface $reportProcessor,
        ModelHistory $historyModel,
        Report $reportHelper,
        Escaper $escaper = null,
        ?RenderErrorMessages $renderErrorMessages = null
    ) {
        parent::__construct($context);
        $this->reportProcessor = $reportProcessor;
        $this->historyModel = $historyModel;
        $this->reportHelper = $reportHelper;
        $this->escaper = $escaper
            ?? ObjectManager::getInstance()->get(Escaper::class);
        $this->renderErrorMessages = $renderErrorMessages ??
            ObjectManager::getInstance()->get(RenderErrorMessages::class);
    }

    /**
     * Add Error Messages for Import
     *
     * @param AbstractBlock $resultBlock
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return $this
     */
    protected function addErrorMessages(
        AbstractBlock $resultBlock,
        ProcessingErrorAggregatorInterface $errorAggregator
    ) {
        if ($errorAggregator->getErrorsCount()) {
            try {
                $resultBlock->addNotice(
                    $this->renderErrorMessages->renderMessages($errorAggregator)
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
     * Get all Error Messages from Import Results
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return array
     */
    protected function getErrorMessages(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        return $this->renderErrorMessages->getErrorMessages($errorAggregator);
    }

    /**
     * Get System Generated Exception
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return ProcessingError[]
     */
    protected function getSystemExceptions(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        return $this->renderErrorMessages->getSystemExceptions($errorAggregator);
    }

    /**
     * Generate Error Report File
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return string
     */
    protected function createErrorReport(ProcessingErrorAggregatorInterface $errorAggregator)
    {
        return $this->renderErrorMessages->createErrorReport($errorAggregator);
    }

    /**
     * Get Import History Url
     *
     * @param string $fileName
     * @return string
     */
    protected function createDownloadUrlImportHistoryFile($fileName)
    {
        return $this->renderErrorMessages->createDownloadUrlImportHistoryFile($fileName);
    }
}
