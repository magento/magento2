<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;

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
     * @var DriverInterface
     */
    private $file;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param Filesystem $filesystem
     * @param DriverInterface $file
     */
    public function __construct(
        Action\Context $context,
        Filesystem $filesystem,
        DriverInterface $file
    ) {
        $this->filesystem = $filesystem;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * Controller basic method implementation.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('adminhtml/export/index');
        $fileName = $this->getRequest()->getParam('filename');
        if (empty($fileName) || preg_match('/\.\.(\\\|\/)/', $fileName) !== 0) {
            $this->messageManager->addErrorMessage(__('Please provide valid export file name'));

            return $resultRedirect;
        }
        try {
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
            $path = $directory->getAbsolutePath() . 'export/' . $fileName;

            if ($directory->isFile($path)) {
                $this->file->deleteFile($path);
                $this->messageManager->addSuccessMessage(__('File %1 deleted', $fileName));
            } else {
                $this->messageManager->addErrorMessage(__('%1 is not a valid file', $fileName));
            }
        } catch (FileSystemException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $resultRedirect;
    }
}
