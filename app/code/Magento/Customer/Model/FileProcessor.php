<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * Processor class for work with uploaded files
 */
class FileProcessor
{
    /**
     * Temporary directory name
     */
    const TMP_DIR = 'tmp';

    private const CUSTOMER_FILE_URL_PATH = 'customer/index/viewfile';

    private const CUSTOMER_ADDRESS_FILE_URL_PATH = 'customer/address/viewfile';

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
     * @var Mime
     */
    private $mime;

    /**
     * @var string
     */
    private $customerFileUrlPath;

    /**
     * @var string
     */
    private $customerAddressFileUrlPath;

    /**
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @param UrlInterface $urlBuilder
     * @param EncoderInterface $urlEncoder
     * @param string $entityTypeCode
     * @param Mime $mime
     * @param array $allowedExtensions
     * @param string $customerFileUrlPath
     * @param string $customerAddressFileUrlPath
     */
    public function __construct(
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        UrlInterface $urlBuilder,
        EncoderInterface $urlEncoder,
        $entityTypeCode,
        Mime $mime,
        array $allowedExtensions = [],
        string $customerFileUrlPath = self::CUSTOMER_FILE_URL_PATH,
        string $customerAddressFileUrlPath = self::CUSTOMER_ADDRESS_FILE_URL_PATH
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->urlBuilder = $urlBuilder;
        $this->urlEncoder = $urlEncoder;
        $this->entityTypeCode = $entityTypeCode;
        $this->mime = $mime;
        $this->allowedExtensions = $allowedExtensions;
        $this->customerFileUrlPath = $customerFileUrlPath;
        $this->customerAddressFileUrlPath = $customerAddressFileUrlPath;
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

        return base64_encode($fileContent);
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

        return $this->mediaDirectory->stat($filePath);
    }

    /**
     * Retrieve MIME type of requested file
     *
     * @param string $fileName
     * @return string
     */
    public function getMimeType($fileName)
    {
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');
        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($filePath);

        return $this->mime->getMimeType($absoluteFilePath);
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

        return $this->mediaDirectory->isExist($filePath);
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
            $viewUrl = $this->urlBuilder->getUrl(
                $this->customerAddressFileUrlPath,
                [$type => $this->urlEncoder->encode(ltrim($filePath, '/'))]
            );
        }

        if ($this->entityTypeCode == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $viewUrl = $this->urlBuilder->getUrl(
                $this->customerFileUrlPath,
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
        unset($result['path']);
        if (!$result) {
            throw new LocalizedException(
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
     * @throws LocalizedException
     */
    public function moveTemporaryFile($fileName)
    {
        if (!$this->isFileTemporary($fileName)) {
            return $fileName;
        }

        $fileName = ltrim($fileName, '/');

        $dispersionPath = Uploader::getDispersionPath($fileName);
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
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file.'),
                $e
            );
        }

        return $dispersionPath . '/' . $destinationFileName;
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

        return $this->mediaDirectory->delete($filePath);
    }

    /**
     * Verify if given file temporary.
     *
     * @param string $fileName
     * @return bool
     */
    private function isFileTemporary(string $fileName): bool
    {
        $tmpFile = $this->entityTypeCode . '/' . self::TMP_DIR . '/' . ltrim($fileName, '/');
        $destinationFile = $this->entityTypeCode . '/' . ltrim($fileName, '/');

        return $this->mediaDirectory->isExist($tmpFile) && !$this->mediaDirectory->isExist($destinationFile);
    }
}
