<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\LocalizedFileName;
use Magento\ImportExport\Model\Export\FileInfo;
use Throwable;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;

/**
 * Controller that download file by name.
 */
class Download extends ExportController implements HttpGetActionInterface
{
    /**
     * Url to this controller
     */
    public const URL = 'adminhtml/export_file/download/';

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var LocalizedFileName
     */
    private $localizedFileName;

    /**
     * @var FileInfo
     */
    private $fileInfo;

    /**
     * DownloadFile constructor.
     * @param Action\Context $context
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param LocalizedFileName|null $localizedFileName
     * @param FileInfo|null $fileInfo
     */
    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        ?LocalizedFileName $localizedFileName = null,
        ?FileInfo $fileInfo = null
    ) {
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context);
        $this->localizedFileName = $localizedFileName ?? ObjectManager::getInstance()->get(LocalizedFileName::class);
        $this->fileInfo = $fileInfo ?? ObjectManager::getInstance()->get(FileInfo::class);
    }

    /**
     * Controller basic method implementation.
     *
     * @return Redirect|ResponseInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('adminhtml/export/index');

        $fileName = $this->getRequest()->getParam('filename');

        if (empty($fileName)) {
            $this->messageManager->addErrorMessage(__('Please provide valid export file name'));

            return $resultRedirect;
        }

        $exportDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);

        try {
            $fileName  = $exportDirectory->getDriver()->getRealPathSafety(DIRECTORY_SEPARATOR . $fileName);
            $fileExist = $exportDirectory->isExist('export' . $fileName);
        } catch (Throwable $e) {
            $fileExist = false;
        }

        if (empty($fileName) || !$fileExist || !$this->isAllowedExportFile($fileName)) {
            $this->messageManager->addErrorMessage(__('Please provide valid export file name'));

            return $resultRedirect;
        }

        try {
            $path = 'export' . $fileName;
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_IMPORT_EXPORT);
            if ($directory->isFile($path)) {
                return $this->fileFactory->create(
                    $this->localizedFileName->getFileDisplayName($path),
                    ['type' => 'filename', 'value' => $path],
                    DirectoryList::VAR_IMPORT_EXPORT
                );
            }
            $this->messageManager->addErrorMessage(__('%1 is not a valid file', $fileName));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
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
