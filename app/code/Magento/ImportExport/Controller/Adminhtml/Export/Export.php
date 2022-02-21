<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\ImportExport\Model\Export as ExportModel;
use Magento\ImportExport\Model\Export\Entity\ExportInfoFactory;

/**
 * Controller for export operation.
 */
class Export extends ExportController implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var ExportInfoFactory
     */
    private $exportInfoFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param \Magento\Framework\Session\SessionManagerInterface|null $sessionManager
     * @param PublisherInterface|null $publisher
     * @param ExportInfoFactory|null $exportInfoFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager = null,
        PublisherInterface $publisher = null,
        ExportInfoFactory $exportInfoFactory = null
    ) {
        $this->fileFactory = $fileFactory;
        $this->sessionManager = $sessionManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Session\SessionManagerInterface::class);
        $this->messagePublisher = $publisher ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(PublisherInterface::class);
        $this->exportInfoFactory = $exportInfoFactory ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                ExportInfoFactory::class
            );
        parent::__construct($context);
    }

    /**
     * Load data with filter applying and create file for download.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->getPost(ExportModel::FILTER_ELEMENT_GROUP)) {
            try {
                $params = $this->getRequestParameters();

                if (!array_key_exists('skip_attr', $params)) {
                    $params['skip_attr'] = [];
                }

                /** @var ExportInfoFactory $dataObject */
                $dataObject = $this->exportInfoFactory->create(
                    $params['file_format'],
                    $params['entity'],
                    $params['export_filter'],
                    $params['skip_attr']
                );

                $this->messagePublisher->publish('import_export.export', $dataObject);
                $this->messageManager->addSuccessMessage(
                    __(
                        'Message is added to queue, wait to get your file soon.'
                        . ' Make sure your cron job is running to export the file'
                    )
                );
            } catch (\Exception $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->messageManager->addErrorMessage(__('Please correct the data sent value.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please correct the data sent value.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }

    /**
     * Retrieve all params as array
     *
     * @return array
     */
    public function getRequestParameters(): array
    {
        return $this->getRequest()->getParams();
    }
}
