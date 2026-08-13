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
use Magento\Framework\Exception\ValidatorException;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\Framework\Filesystem;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\ImportExport\Model\Export\FileInfo;

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
     * @param FileInfo|null $fileInfo
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
                $fileExist = $directoryWrite->isFile('export' . $fileName);
                if (!$fileExist || !$this->isAllowedExportFile($fileName)) {
                    $this->messageManager->addErrorMessage(__(
                        'Sorry, but the data is invalid or the file is not uploaded.'
                    ));
                    return $resultRedirect;
                }
                $directoryWrite->delete($directoryWrite->getAbsolutePath() . 'export' . $fileName);
                $this->messageManager->addSuccessMessage(__('File %1 deleted', $fileName));
            } catch (ValidatorException $exception) {
                $this->messageManager->addErrorMessage(
                    __('Sorry, but the data is invalid or the file is not uploaded.')
                );
            } catch (FileSystemException $exception) {
                $this->messageManager->addErrorMessage(
                    __('Sorry, but the data is invalid or the file is not uploaded.')
                );
            }
        } catch (FileSystemException $exception) {
            $this->messageManager->addErrorMessage(__('There are no export file with such name %1', $fileName));
        }

        return $resultRedirect;
    }

    /**
     * Check whether requested file is a completed export file.
     *
     * @param string $fileName
     * @return bool
     */
    private function isAllowedExportFile(string $fileName): bool
    {
        return $this->fileInfo->isExportFile($fileName);
    }
}
