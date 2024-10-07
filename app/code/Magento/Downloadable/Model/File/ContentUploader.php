<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\File;

use Magento\MediaStorage\Helper\File\Storage;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Magento\Downloadable\Api\Data\File\ContentInterface;
use Magento\Downloadable\Model\Link as LinkConfig;
use Magento\Downloadable\Model\Sample as SampleConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class ContentUploader extends Uploader implements \Magento\Downloadable\Api\Data\File\ContentUploaderInterface
{
    /**
     * Default MIME type for header "application/octet-stream"
     */
    public const DEFAULT_MIME_TYPE = 'application/octet-stream';

    /**
     * Filename prefix for temporary files
     *
     * @var string
     */
    protected $filePrefix = 'magento_api';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $systemTmpDirectory;

    /**
     * @var LinkConfig
     */
    protected $linkConfig;

    /**
     * @var SampleConfig
     */
    protected $sampleConfig;

    /**
     * @param Database $coreFileStorageDb
     * @param Storage $coreFileStorage
     * @param NotProtectedExtension $validator
     * @param Filesystem $filesystem
     * @param LinkConfig $linkConfig
     * @param SampleConfig $sampleConfig
     */
    public function __construct(
        Database $coreFileStorageDb,
        Storage $coreFileStorage,
        NotProtectedExtension $validator,
        Filesystem $filesystem,
        LinkConfig $linkConfig,
        SampleConfig $sampleConfig
    ) {
        $this->_validator = $validator;
        $this->_coreFileStorage = $coreFileStorage;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->systemTmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $this->linkConfig = $linkConfig;
        $this->sampleConfig = $sampleConfig;
    }

    /**
     * Decode base64 encoded content and save it in system tmp folder
     *
     * @param ContentInterface $fileContent
     * @return array
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    protected function decodeContent(ContentInterface $fileContent)
    {
        $tmpFileName = $this->getTmpFileName();
        $fileSize = $this->systemTmpDirectory->writeFile($tmpFileName, base64_decode($fileContent->getFileData()));

        return [
            'name' => $fileContent->getName(),
            'type' => self::DEFAULT_MIME_TYPE,
            'tmp_name' => $this->systemTmpDirectory->getAbsolutePath($tmpFileName),
            'error' => 0,
            'size' => $fileSize,
        ];
    }

    /**
     * Generate temporary file name
     *
     * @return string
     */
    protected function getTmpFileName()
    {
        return uniqid($this->filePrefix, true);
    }

    /**
     * @inheritdoc
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function upload(ContentInterface $fileContent, $contentType)
    {
        $this->_file = $this->decodeContent($fileContent);
        if (!file_exists($this->_file['tmp_name'])) {
            throw new \InvalidArgumentException('There was an error during file content upload.');
        }
        $this->_fileExists = true;
        $this->_uploadType = self::SINGLE_STYLE;
        $this->setAllowRenameFiles(true);
        $this->setFilesDispersion(true);
        $result = $this->save($this->getDestinationDirectory($contentType));

        if ($result) {
            unset($result['path']);
            $result['status'] = 'new';
            $result['name'] = substr($result['file'], strrpos($result['file'], '/') + 1);
        }

        return $result;
    }

    /**
     * Retrieve destination directory for given content type
     *
     * @param string $contentType
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getDestinationDirectory($contentType)
    {
        switch ($contentType) {
            case 'link_file':
                $directory = $this->mediaDirectory->getAbsolutePath($this->linkConfig->getBaseTmpPath());
                break;
            case 'link_sample_file':
                $directory = $this->mediaDirectory->getAbsolutePath($this->linkConfig->getBaseSampleTmpPath());
                break;
            case 'sample':
                $directory = $this->mediaDirectory->getAbsolutePath($this->sampleConfig->getBaseTmpPath());
                break;
            default:
                throw new \InvalidArgumentException('Invalid downloadable file content type.');
        }
        return $directory;
    }
}
