<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Throwable;

/**
 * Controller that download file by name.
 */
class Download extends ExportController implements HttpGetActionInterface
{
    /**
     * Url to this controller
     */
    const URL = 'adminhtml/export_file/download/';

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * DownloadFile constructor.
     * @param Action\Context $context
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        Filesystem $filesystem
    ) {
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * Controller basic method implementation.
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $fileName = $this->getRequest()->getParam('filename');
        $exportDirectory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_EXPORT);
        try {
            $fileExist = $exportDirectory->isExist($fileName);
        } catch (Throwable $e) {
            $fileExist = false;
        }
        if (empty($fileName) || !$fileExist) {
            throw new LocalizedException(__('Please provide valid export file name'));
        }
        try {
            $path = 'export/' . $fileName;
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
            if ($directory->isFile($path)) {
                return $this->fileFactory->create(
                    $path,
                    $directory->readFile($path),
                    DirectoryList::VAR_DIR
                );
            } else {
                $fileExist = false;
            }
        } catch (LocalizedException | \Exception $exception) {
            $fileExist = false;
        }

        if (!$fileExist) {
            throw new LocalizedException(__('There are no export file with such name %1', $fileName));
        }
    }
}
