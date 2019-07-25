<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            if (empty($fileName = $this->getRequest()->getParam('filename'))) {
                throw new LocalizedException(__('Please provide export file name'));
            }
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
            $path = $directory->getAbsolutePath() . 'export/' . $fileName;
            $this->file->deleteFile($path);
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('adminhtml/export/index');
            return $resultRedirect;
        } catch (FileSystemException $exception) {
            throw new LocalizedException(__('There are no export file with such name %1', $fileName));
        }
    }
}
