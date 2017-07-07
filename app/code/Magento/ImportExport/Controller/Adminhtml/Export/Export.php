<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Export;

use Magento\Framework\Controller\ResultFactory;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\ImportExport\Model\Export as ExportModel;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

class Export extends ExportController
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
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager [optional]
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager = null
    ) {
        $this->fileFactory = $fileFactory;
        $this->sessionManager = $sessionManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Session\SessionManagerInterface::class);
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
                /** @var $model \Magento\ImportExport\Model\Export */
                $model = $this->_objectManager->create(\Magento\ImportExport\Model\Export::class);
                $model->setData($this->getRequest()->getParams());

                $this->sessionManager->writeClose();
                return $this->fileFactory->create(
                    $model->getFileName(),
                    $model->export(),
                    DirectoryList::VAR_DIR,
                    $model->getContentType()
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->messageManager->addError(__('Please correct the data sent value.'));
            }
        } else {
            $this->messageManager->addError(__('Please correct the data sent value.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }
}
