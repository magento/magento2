<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\Framework\Filesystem;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\ImportExport\Model\Export\FileInfo;
use Throwable;

/**
 * Controller that delete file by name.
 */
class Delete extends ExportController implements HttpPostActionInterface
{
    /**
     * Url to this controller
     */
    const URL = 'adminhtml/export_file/delete';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * @var FileInfo
     */
    private $fileInfo;

    /**
     * Delete constructor.
     *
     * @param Action\Context $context
     * @param Filesystem $filesystem
     * @param WriteFactory $writeFactory
     * @param FileInfo $fileInfo
     */
    public function __construct(
        Action\Context $context,
        Filesystem $filesystem,
        WriteFactory $writeFactory,
        ?FileInfo $fileInfo = null
    ) {
        $this->filesystem = $filesystem;
        $this->writeFactory = $writeFactory;
        $this->fileInfo = $fileInfo ?? ObjectManager::getInstance()->get(FileInfo::class);
        parent::__construct($context);
    }

    /**
     * Controller basic method implementation.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('adminhtml/export/index');
        try {
            if (empty($fileName = $this->getRequest()->getParam('filename'))) {
                $this->messageManager->addErrorMessage(__('Please provide valid export file name'));

                return $resultRedirect;
            }
            $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
            try {
                $fileName = $directoryWrite->getDriver()->getRealPathSafety(DIRECTORY_SEPARATOR . $fileName);
                $path = 'export' . $fileName;
                if (empty($fileName) || !$this->fileInfo->isExportFile($fileName)) {
                    $this->messageManager->addErrorMessage(__('Please provide valid export file name'));

                    return $resultRedirect;
                }
                $directoryWrite->delete($path);
                $this->messageManager->addSuccessMessage(__('File %1 deleted', $fileName));
            } catch (Throwable $exception) {
                $this->messageManager->addErrorMessage(
                    __('Sorry, but the data is invalid or the file is not uploaded.')
                );
            }
        } catch (FileSystemException $exception) {
            $this->messageManager->addErrorMessage(__('There are no export file with such name %1', $fileName));
        }

        return $resultRedirect;
    }
}
