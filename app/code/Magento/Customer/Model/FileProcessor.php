<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileProcessor
{
    /**
     * Temporary directory name
     */
    const TMP_DIR = 'tmp';

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var EncoderInterface
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
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @param UrlInterface $urlBuilder
     * @param EncoderInterface $urlEncoder
     * @param string $entityTypeCode
     * @param array $allowedExtensions
     */
    public function __construct(
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        UrlInterface $urlBuilder,
        EncoderInterface $urlEncoder,
        $entityTypeCode,
        array $allowedExtensions = []
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->urlBuilder = $urlBuilder;
        $this->urlEncoder = $urlEncoder;
        $this->entityTypeCode = $entityTypeCode;
        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * Retrieve base64 encoded file content
     *
     * @param string $fileName
     * @return string
     */
    public function getBase64EncodedData($fileName)
    {
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');

        $fileContent = $this->mediaDirectory->readFile($filePath);

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
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');

        $result = $this->mediaDirectory->stat($filePath);
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
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');

        $result = $this->mediaDirectory->isExist($filePath);
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

        if ($this->entityTypeCode == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $filePath = $this->entityTypeCode . '/' . ltrim($filePath, '/');
            $viewUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA])
                . $this->mediaDirectory->getRelativePath($filePath);
        }

        if ($this->entityTypeCode == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
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
     * @throws LocalizedException
     */
    public function saveTemporaryFile($fileId)
    {
        /** @var Uploader $uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setFilesDispersion(false);
        $uploader->setFilenamesCaseSensitivity(false);
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowedExtensions($this->allowedExtensions);

        $path = $this->mediaDirectory->getAbsolutePath(
            $this->entityTypeCode . '/' . self::TMP_DIR
        );

        $result = $uploader->save($path);
        if (!$result) {
            throw new LocalizedException(__('File can not be saved to the destination folder.'));
        }

        return $result;
    }

    /**
     * Move file from temporary directory into base directory
     *
     * @param string $fileName
     * @return string
     * @throws LocalizedException
     */
    public function moveTemporaryFile($fileName)
    {
        $fileName = ltrim($fileName, '/');

        $dispersionPath = Uploader::getDispretionPath($fileName);
        $destinationPath = $this->entityTypeCode . $dispersionPath;

        if (!$this->mediaDirectory->create($destinationPath)) {
            throw new LocalizedException(
                __('Unable to create directory %1.', $destinationPath)
            );
        }

        if (!$this->mediaDirectory->isWritable($destinationPath)) {
            throw new LocalizedException(
                __('Destination folder is not writable or does not exists.')
            );
        }

        $destinationFileName = Uploader::getNewFileName(
            $this->mediaDirectory->getAbsolutePath($destinationPath) . '/' . $fileName
        );

        try {
            $this->mediaDirectory->renameFile(
                $this->entityTypeCode . '/' . self::TMP_DIR . '/' . $fileName,
                $destinationPath . '/' . $destinationFileName
            );
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Something went wrong while saving the file.')
            );
        }

        $fileName = $dispersionPath . '/' . $fileName;
        return $fileName;
    }

    /**
     * Remove uploaded file
     *
     * @param string $fileName
     * @return bool
     */
    public function removeUploadedFile($fileName)
    {
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');

        $result = $this->mediaDirectory->delete($filePath);
        return $result;
    }
}
