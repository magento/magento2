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
use Magento\Framework\Exception\ValidatorException;
use Magento\ImportExport\Controller\Adminhtml\Export as ExportController;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Backend\Model\View\Result\Redirect;
use \Magento\Framework\Filesystem\Directory\WriteInterface;

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
     * @deprecated Is not used anymore.
     * @see WriteInterface
     */
    private $file;

    /**
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * Delete constructor.
     *
     * @param Action\Context $context
     * @param Filesystem $filesystem
     * @param DriverInterface $file
     * @param WriteFactory $writeFactory
     */
    public function __construct(
        Action\Context $context,
        Filesystem $filesystem,
        DriverInterface $file,
        WriteFactory $writeFactory
    ) {
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->writeFactory = $writeFactory;
        parent::__construct($context);
    }

    /**
     * Controller basic method implementation.
     *
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            if (empty($fileName = $this->getRequest()->getParam('filename'))) {
                throw new LocalizedException(__('Please provide export file name'));
            }
            $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_EXPORT);
            try {
                $directoryWrite->delete($directoryWrite->getAbsolutePath($fileName));
            } catch (ValidatorException $exception) {
                throw new LocalizedException(__('Sorry, but the data is invalid or the file is not uploaded.'));
            } catch (FileSystemException $exception) {
                throw new LocalizedException(__('Sorry, but the data is invalid or the file is not uploaded.'));
            }
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('adminhtml/export/index');

            return $resultRedirect;
        } catch (FileSystemException $exception) {
            throw new LocalizedException(__('There are no export file with such name %1', $fileName));
        }
    }
}
