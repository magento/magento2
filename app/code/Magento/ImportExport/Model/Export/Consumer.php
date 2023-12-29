<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Api\Data\LocalizedExportInfoInterface;
use Magento\ImportExport\Api\ExportManagementInterface;
use Magento\Framework\Notification\NotifierInterface;

/**
 * Consumer for export message.
 */
class Consumer
{
    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var ExportManagementInterface
     */
    private $exportManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Consumer constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param ExportManagementInterface $exportManager
     * @param Filesystem $filesystem
     * @param NotifierInterface $notifier
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ExportManagementInterface $exportManager,
        Filesystem $filesystem,
        NotifierInterface $notifier
    ) {
        $this->logger = $logger;
        $this->exportManager = $exportManager;
        $this->filesystem = $filesystem;
        $this->notifier = $notifier;
    }

    /**
     * Consumer logic.
     *
     * @param LocalizedExportInfoInterface $exportInfo
     * @return void
     */
    public function process(LocalizedExportInfoInterface $exportInfo)
    {
        try {
            $data = $this->exportManager->export($exportInfo);
            $fileName = $exportInfo->getFileName();
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
            $directory->writeFile('export/' . $fileName, $data);

            $this->notifier->addMajor(
                __('Your export file is ready'),
                __('You can pick up your file at export main page')
            );
        } catch (LocalizedException | FileSystemException $exception) {
            $this->notifier->addCritical(
                __('Error during export process occurred'),
                __('Error during export process occurred. Please check logs for detail')
            );
            $this->logger->critical('Something went wrong while export process. ' . $exception->getMessage());
        }
    }
}
