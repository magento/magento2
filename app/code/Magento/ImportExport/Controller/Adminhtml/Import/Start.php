<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\ImportExport\Controller\Adminhtml\ImportResult as ImportResultController;
use Magento\Framework\Controller\ResultFactory;

class Start extends ImportResultController implements HttpPostActionInterface
{
    /**
     * @var \Magento\ImportExport\Model\Import
     */
    protected $importModel;

    /**
     * @var \Magento\Framework\Message\ExceptionMessageFactoryInterface
     */
    private $exceptionMessageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor
     * @param \Magento\ImportExport\Model\History $historyModel
     * @param \Magento\ImportExport\Helper\Report $reportHelper
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param \Magento\Framework\Message\ExceptionMessageFactoryInterface $exceptionMessageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor,
        \Magento\ImportExport\Model\History $historyModel,
        \Magento\ImportExport\Helper\Report $reportHelper,
        \Magento\ImportExport\Model\Import $importModel,
        \Magento\Framework\Message\ExceptionMessageFactoryInterface $exceptionMessageFactory
    ) {
        parent::__construct($context, $reportProcessor, $historyModel, $reportHelper);
        $this->importModel = $importModel;
        $this->exceptionMessageFactory = $exceptionMessageFactory;
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
            $resultBlock
                ->addAction('show', 'import_validation_container')
                ->addAction('innerHTML', 'import_validation_container_header', __('Status'))
                ->addAction('hide', ['edit_form', 'upload_button', 'messages']);

            $this->importModel->setData($data);
            $errorAggregator = $this->importModel->getErrorAggregator();
            try {
                $this->importModel->importSource();
            } catch (\Exception $e) {
                $resultMessageBlock = $resultLayout->getLayout()->getBlock('messages');
                $message = $this->exceptionMessageFactory->createMessage($e);
                $html = $resultMessageBlock->addMessage($message)->toHtml();
                $errorAggregator->addError(
                    \Magento\ImportExport\Model\Import\Entity\AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
                    \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::ERROR_LEVEL_CRITICAL,
                    null,
                    null,
                    null,
                    $html
                );
            }

            if ($this->importModel->getErrorAggregator()->hasToBeTerminated()) {
                $resultBlock->addError(__('Maximum error count has been reached or system error is occurred!'));
                $this->addErrorMessages($resultBlock, $errorAggregator);
            } else {
                $this->importModel->invalidateIndex();
                $this->addErrorMessages($resultBlock, $errorAggregator);
                $resultBlock->addSuccess(__('Import successfully done'));
            }

            return $resultLayout;
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }
}
