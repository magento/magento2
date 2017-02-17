<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class FileRecorder
{
    /**
     * @var FileInfoManager
     */
    private $fileInfoManager;

    /**
     * @var FileInfoFactory
     */
    private $fileInfoFactory;

    /**
     * @var string
     */
    private $encodedFileSubdirectoryPath = 'analytics/';

    /**
     * @var string
     */
    private $encodedFileName = 'data.tgz';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param FileInfoManager $fileInfoManager
     * @param FileInfoFactory $fileInfoFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        FileInfoManager $fileInfoManager,
        FileInfoFactory $fileInfoFactory,
        Filesystem $filesystem
    ) {
        $this->fileInfoManager = $fileInfoManager;
        $this->fileInfoFactory = $fileInfoFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * @param EncodedContext $encodedContext
     * @return bool
     */
    public function recordNewFile(EncodedContext $encodedContext)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        $fileRelativePath = $this->getEncodedFileRelativePath();
        $directory->writeFile($fileRelativePath, $encodedContext->getContent());

        $fileInfo = $this->fileInfoManager->load();
        $this->registerFile($encodedContext, $fileRelativePath);
        $this->removeOldFile($fileInfo, $directory);

        return true;
        
    }

    /**
     * @return string
     */
    private function getEncodedFileRelativePath()
    {
        return $this->encodedFileSubdirectoryPath . hash('sha256', time())
            . '/' . $this->encodedFileName;
    }

    /**
     * @param EncodedContext $encodedContext
     * @param $fileRelativePath
     * @return bool
     */
    private function registerFile(EncodedContext $encodedContext, $fileRelativePath)
    {
        $newFileInfo = $this->fileInfoFactory->create();
        $newFileInfo->setInitializationVector($encodedContext->getInitializationVector())
            ->setPath($fileRelativePath);
        $this->fileInfoManager->save($newFileInfo);

        return true;
    }

    /**
     * @param FileInfo $fileInfo
     * @param WriteInterface $directory
     * @return bool
     */
    private function removeOldFile(FileInfo $fileInfo, WriteInterface $directory)
    {
        if (!$fileInfo->getPath()) {
            return true;
        }

        $directory->delete($fileInfo->getPath());

        $directoryName = dirname($fileInfo->getPath());
        if ($directoryName !== '.') {
            $listing = array_diff(scandir($directory->getAbsolutePath($directoryName)), ['.', '..']);
            if (!$listing) {
                $directory->delete($directoryName);
            }
        }

        return true;
    }
}
