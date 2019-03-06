<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\Framework\Filesystem;

/**
 * Controller that download file by name.
 */
class Download extends ExportController implements HttpGetActionInterface
{
    /**
     * url to this controller
     */
    const URL = 'admin/export_file/download/';

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
        if (empty($fileName = $this->getRequest()->getParam('filename'))) {
            throw new LocalizedException(__('Please provide export file name'));
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
            }
        } catch (LocalizedException | \Exception $exception) {
            throw new LocalizedException(__('There are no export file with such name %1', $fileName));
        }
    }
}
