<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Directory\WriteFactory;

/**
 * Processor class for work with uploaded files.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileProcessor
{
    /**
     * Temporary directory name
     */
    const TMP_DIR = 'tmp';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaEntityDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    private $urlEncoder;

    /**
     * @var string
     */
    private $entityTypeCode;

    /**
     * @var array
     */
    private $allowedExtensions = [];

    /**
     * @var \Magento\Framework\File\Mime
     */
    private $mime;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param string $entityTypeCode
     * @param \Magento\Framework\File\Mime $mime
     * @param array $allowedExtensions
     * @param WriteFactory|null $writeFactory
     * @param DirectoryList|null $directoryList
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        $entityTypeCode,
        \Magento\Framework\File\Mime $mime,
        array $allowedExtensions = [],
        WriteFactory $writeFactory = null,
        DirectoryList $directoryList = null
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->urlBuilder = $urlBuilder;
        $this->urlEncoder = $urlEncoder;
        $this->entityTypeCode = $entityTypeCode;
        $this->mime = $mime;
        $this->allowedExtensions = $allowedExtensions;
        $writeFactory = $writeFactory ?? ObjectManager::getInstance()->get(WriteFactory::class);
        $directoryList = $directoryList ?? ObjectManager::getInstance()->get(DirectoryList::class);
        $this->mediaEntityDirectory = $writeFactory->create(
            $directoryList->getPath(DirectoryList::MEDIA)
            . DIRECTORY_SEPARATOR . $this->entityTypeCode
        );
    }

    /**
     * Retrieve base64 encoded file content
     *
     * @param string $fileName
     * @return string
     */
    public function getBase64EncodedData($fileName)
    {
        $fileContent = $this->mediaEntityDirectory->readFile($fileName);

        $encodedContent = base64_encode($fileContent);
        return $encodedContent;
    }

    /**
     * Get file statistics data
     *
     * @param string $fileName
     * @return array
     */
    public function getStat($fileName)
    {
        $result = $this->mediaEntityDirectory->stat($fileName);
        return $result;
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $fileName
     * @return string
     */
    public function getMimeType($fileName)
    {
        $absoluteFilePath = $this->mediaEntityDirectory->getAbsolutePath($fileName);

        $result = $this->mime->getMimeType($absoluteFilePath);
        return $result;
    }

    /**
     * Check if the file exists
     *
     * @param string $fileName
     * @return bool
     */
    public function isExist($fileName)
    {
        $result = $this->mediaEntityDirectory->isExist($fileName);
        return $result;
    }

    /**
     * Retrieve customer/index/viewfile action URL
     *
     * @param string $filePath
     * @param string $type
     * @return string
     */
    public function getViewUrl($filePath, $type)
    {
        $viewUrl = '';

        if ($this->entityTypeCode == \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $filePath = $this->entityTypeCode . DIRECTORY_SEPARATOR . ltrim($filePath, '/');
            $viewUrl = $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA])
                . $this->mediaEntityDirectory->getRelativePath($filePath);
        }

        if ($this->entityTypeCode == \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $viewUrl = $this->urlBuilder->getUrl(
                'customer/index/viewfile',
                [$type => $this->urlEncoder->encode(ltrim($filePath, '/'))]
            );
        }

        return $viewUrl;
    }

    /**
     * Save uploaded file to temporary directory
     *
     * @param string $fileId
     * @return \string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveTemporaryFile($fileId)
    {
        /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setFilesDispersion(false);
        $uploader->setFilenamesCaseSensitivity(false);
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowedExtensions($this->allowedExtensions);

        $path = $this->mediaEntityDirectory->getAbsolutePath(
            DIRECTORY_SEPARATOR . self::TMP_DIR
        );

        $result = $uploader->save($path);
        unset($result['path']);
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }

        return $result;
    }

    /**
     * Move file from temporary directory into base directory
     *
     * @param string $fileName
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function moveTemporaryFile($fileName)
    {
        $fileName = ltrim($fileName, '/');

        $destinationPath = \Magento\MediaStorage\Model\File\Uploader::getDispersionPath($fileName);

        if (!$this->mediaEntityDirectory->create($destinationPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to create directory %1.', $destinationPath)
            );
        }

        if (!$this->mediaEntityDirectory->isWritable($destinationPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Destination folder is not writable or does not exists.')
            );
        }

        $destinationFileName = \Magento\MediaStorage\Model\File\Uploader::getNewFileName(
            $this->mediaEntityDirectory->getAbsolutePath($destinationPath) . '/' . $fileName
        );

        try {
            $this->mediaEntityDirectory->renameFile(
                DIRECTORY_SEPARATOR . self::TMP_DIR . DIRECTORY_SEPARATOR . $fileName,
                $destinationPath . DIRECTORY_SEPARATOR . $destinationFileName
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file.')
            );
        }

        return $destinationPath . DIRECTORY_SEPARATOR . $destinationFileName;
    }

    /**
     * Remove uploaded file
     *
     * @param string $fileName
     * @return bool
     */
    public function removeUploadedFile($fileName)
    {
        $filePath = DIRECTORY_SEPARATOR . ltrim($fileName, '/');

        $result = $this->mediaEntityDirectory->delete($filePath);
        return $result;
    }
}
