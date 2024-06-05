<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\History;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultInterface;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\Import;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;

/**
 * Download history controller
 */
class Download extends \Magento\ImportExport\Controller\Adminhtml\History implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct(
            $context
        );
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Download backup action
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/history');

        $fileName = $this->getRequest()->getParam('filename');

        if (empty($fileName)) {
            return $resultRedirect;
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $fileName = basename($fileName);

        /** @var Report $reportHelper */
        $reportHelper = $this->_objectManager->get(Report::class);

        if (!$reportHelper->importFileExists($fileName)) {
            return $resultRedirect;
        }

        return $this->fileFactory->create(
            $fileName,
            ['type' => 'filename', 'value' => Import::IMPORT_HISTORY_DIR . $fileName],
            DirectoryList::VAR_IMPORT_EXPORT,
            'application/octet-stream',
            $reportHelper->getReportSize($fileName)
        );
    }
}
