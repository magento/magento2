<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Framework\Api\ArrayObjectSearch;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File as IoFileSystem;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Psr\Log\LoggerInterface;

/**
 * Metadata for form image field
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Image extends File
{
    /**
     * @var ImageContentInterfaceFactory
     */
    private $imageContentFactory;

    /**
     * @var IoFileSystem
     */
    private $ioFileSystem;

    /**
     * @var ReadInterface
     */
    private $mediaEntityTmpReadDirectory;

    /**
     * @var WriteInterface
     */
    private $mediaWriteDirectory;

    /**
     * @param TimezoneInterface $localeDate
     * @param LoggerInterface $logger
     * @param AttributeMetadataInterface $attribute
     * @param ResolverInterface $localeResolver
     * @param null|string $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @param EncoderInterface $urlEncoder
     * @param NotProtectedExtension $fileValidator
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param FileProcessorFactory|null $fileProcessorFactory
     * @param ImageContentInterfaceFactory|null $imageContentInterfaceFactory
     * @param IoFileSystem|null $ioFileSystem
     * @param DirectoryList|null $directoryList
     * @param WriteFactory|null $writeFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws FileSystemException
     */
    public function __construct(
        TimezoneInterface $localeDate,
        LoggerInterface $logger,
        AttributeMetadataInterface $attribute,
        ResolverInterface $localeResolver,
        $value,
        $entityTypeCode,
        $isAjax,
        EncoderInterface $urlEncoder,
        NotProtectedExtension $fileValidator,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        ?FileProcessorFactory $fileProcessorFactory = null,
        ?ImageContentInterfaceFactory $imageContentInterfaceFactory = null,
        ?IoFileSystem $ioFileSystem = null,
        ?DirectoryList $directoryList = null,
        ?WriteFactory $writeFactory = null
    ) {
        parent::__construct(
            $localeDate,
            $logger,
            $attribute,
            $localeResolver,
            $value,
            $entityTypeCode,
            $isAjax,
            $urlEncoder,
            $fileValidator,
            $fileSystem,
            $uploaderFactory,
            $fileProcessorFactory
        );
        $this->imageContentFactory = $imageContentInterfaceFactory ?: ObjectManager::getInstance()
            ->get(ImageContentInterfaceFactory::class);
        $this->ioFileSystem = $ioFileSystem ?: ObjectManager::getInstance()
            ->get(IoFileSystem::class);
        $this->mediaWriteDirectory = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaEntityTmpReadDirectory = $fileSystem->getDirectoryReadByPath(
            $this->mediaWriteDirectory->getAbsolutePath() . $this->_entityTypeCode
            . DIRECTORY_SEPARATOR . FileProcessor::TMP_DIR . DIRECTORY_SEPARATOR
        );
    }

    /**
     * Validate file by attribute validate rules
     *
     * Return array of errors
     *
     * @param array $value
     * @return string[]
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _validateByRules($value)
    {
        $label = $value['name'] ?? $value['file'] ?? '';
        $rules = $this->getAttribute()->getValidationRules();

        // Extract and validate file name
        $fileName = $this->extractAndValidateFileName($value, $label);
        if (is_array($fileName)) {
            return $fileName;
        }
        $value['name'] = $fileName;
        $label = $fileName;

        // Get and validate file path, then validate image properties
        $validationResult = $this->validateFilePathAndProperties($value, $label);
        if (is_array($validationResult) && isset($validationResult['error'])) {
            return $validationResult['error'];
        }

        $filePath = $validationResult['filePath'];
        $imageProp = $validationResult['imageProp'];

        // Validate image format
        $formatErrors = $this->validateImageFormat($imageProp, $value, $label);
        if (!empty($formatErrors)) {
            return $formatErrors;
        }

        // Validate size and dimensions
        return $this->validateSizeAndDimensions($value, $filePath, $imageProp, $rules, $label);
    }

    /**
     * Validate file path and image properties
     *
     * @param array $value
     * @param string $label
     * @return array Returns array with filePath and imageProp or error array
     */
    private function validateFilePathAndProperties(array $value, string $label): array
    {
        $filePath = $this->getValidatedFilePath($value, $label);
        if (is_array($filePath)) {
            return ['error' => $filePath];
        }

        $imageProp = $this->validateImageProperties($filePath, $label);
        if (is_array($imageProp) && !isset($imageProp[0])) {
            return ['error' => $imageProp];
        }

        return ['filePath' => $filePath, 'imageProp' => $imageProp];
    }

    /**
     * Extract and validate file name from value
     *
     * @param array $value
     * @param string $label
     * @return string|array Returns file name or error array
     */
    private function extractAndValidateFileName(array $value, string $label)
    {
        if (empty($value['name']) && !empty($value['file'])) {
            if (!$this->isValidFilePath($value['file'])) {
                return [__('"%1" is not a valid file.', $label)];
            }
            $pathInfo = $this->ioFileSystem->getPathInfo($value['file']);
            return $pathInfo['basename'] ?? '';
        }
        return $value['name'] ?? '';
    }

    /**
     * Get and validate file path
     *
     * @param array $value
     * @param string $label
     * @return string|array Returns file path or error array
     */
    private function getValidatedFilePath(array $value, string $label)
    {
        $filePath = $value['tmp_name'] ?? null;

        if (empty($filePath) && !empty($value['file'])) {
            $tmpFileName = ltrim($value['file'], '/');

            if (!$this->isValidFilePath($tmpFileName)) {
                return [__('"%1" is not a valid file.', $label)];
            }

            if ($tmpFileName !== '') {
                $filePath = $this->mediaEntityTmpReadDirectory->getAbsolutePath($tmpFileName);
            }
        }

        if (empty($filePath) || !is_string($filePath)) {
            return [__('"%1" is not a valid file.', $label)];
        }

        if (!$this->mediaWriteDirectory->getDriver()->isExists($filePath)) {
            return [__('"%1" is not a valid file.', $label)];
        }

        return $filePath;
    }

    /**
     * Validate image properties using getimagesize
     *
     * @param string $filePath
     * @param string $label
     * @return array|false Returns image properties or error array
     */
    private function validateImageProperties(string $filePath, string $label)
    {
        try {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $imageProp = getimagesize($filePath);
        } catch (\Throwable $e) {
            $imageProp = false;
        }

        if (!$this->_isUploadedFile($filePath) || !$imageProp) {
            return [__('"%1" is not a valid file.', $label)];
        }

        return $imageProp;
    }

    /**
     * Validate image format
     *
     * @param array $imageProp
     * @param array $value
     * @param string $label
     * @return array Returns error array or empty array
     */
    private function validateImageFormat(array $imageProp, array &$value, string $label): array
    {
        $allowImageTypes = [1 => 'gif', 2 => 'jpg', 3 => 'png'];

        if (!isset($allowImageTypes[$imageProp[2]])) {
            return [__('"%1" is not a valid image format.', $label)];
        }

        // Modify image name if extension doesn't match
        $extension = $this->ioFileSystem->getPathInfo($value['name'])['extension'];
        if ($extension != $allowImageTypes[$imageProp[2]]) {
            $value['name'] = $this->ioFileSystem->getPathInfo($value['name'])['filename']
                . '.'
                . $allowImageTypes[$imageProp[2]];
        }

        return [];
    }

    /**
     * Validate file size and image dimensions
     *
     * @param array $value
     * @param string $filePath
     * @param array $imageProp
     * @param array $rules
     * @param string $label
     * @return array Returns error array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function validateSizeAndDimensions(
        array $value,
        string $filePath,
        array $imageProp,
        array $rules,
        string $label
    ): array {
        $errors = [];

        $maxFileSize = ArrayObjectSearch::getArrayElementByName($rules, 'max_file_size');
        if ($maxFileSize !== null) {
            $size = $value['size'] ?? 0;
            if ($size === 0 && !empty($filePath)) {
                $size = $this->getFileSize($filePath);
            }
            if ($maxFileSize < $size) {
                $errors[] = __('"%1" exceeds the allowed file size.', $label);
            }
        }

        $maxImageWidth = ArrayObjectSearch::getArrayElementByName($rules, 'max_image_width');
        if ($maxImageWidth !== null && $maxImageWidth < $imageProp[0]) {
            $errors[] = __('"%1" width exceeds allowed value of %2 px.', $label, $maxImageWidth);
        }

        $maxImageHeight = ArrayObjectSearch::getArrayElementByName($rules, 'max_image_height');
        if ($maxImageHeight !== null && $maxImageHeight < $imageProp[1]) {
            $errors[] = __('"%1" height exceeds allowed value of %2 px.', $label, $maxImageHeight);
        }

        return $errors;
    }

    /**
     * Get file size safely
     *
     * @param string $filePath
     * @return int
     */
    private function getFileSize(string $filePath): int
    {
        try {
            $stat = $this->mediaWriteDirectory->getDriver()->stat($filePath);
            return (int)($stat['size'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Validate file path for security
     *
     * @param string $filePath
     * @return bool
     */
    private function isValidFilePath(string $filePath): bool
    {
        // Check for null bytes
        if (strpos($filePath, "\0") !== false) {
            return false;
        }

        // Check for path traversal sequences
        if (preg_match('#(^|/)\.\.(?:/|$)#', $filePath)) {
            return false;
        }

        // Check for Windows absolute paths
        if (preg_match('#^[a-zA-Z]:[\\\\/]#', $filePath)) {
            return false;
        }

        // Check for backslashes at the start
        if (isset($filePath[0]) && $filePath[0] === '\\') {
            return false;
        }

        return true;
    }

    /**
     * Process file uploader UI component data
     *
     * @param array $value
     * @return bool|int|ImageContentInterface|string
     * @throws LocalizedException
     */
    protected function processUiComponentValue(array $value)
    {
        if ($this->_entityTypeCode == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            return $this->processCustomerAddressValue($value);
        }

        if ($this->_entityTypeCode == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            return $this->processCustomerValue($value);
        }

        return $this->_value;
    }

    /**
     * Process file uploader UI component data for customer_address entity
     *
     * @param array $value
     * @return string
     * @throws LocalizedException
     */
    protected function processCustomerAddressValue(array $value)
    {
        $fileName = $this->mediaWriteDirectory
            ->getDriver()
            ->getRealPathSafety(
                $this->mediaEntityTmpReadDirectory->getAbsolutePath(
                    ltrim(
                        $value['file'],
                        '/'
                    )
                )
            );
        return $this->getFileProcessor()->moveTemporaryFile(
            $this->mediaEntityTmpReadDirectory->getRelativePath($fileName)
        );
    }

    /**
     * Process file uploader UI component data for customer entity
     *
     * @param array $value
     * @return bool|int|ImageContentInterface|string
     * @throws LocalizedException
     */
    protected function processCustomerValue(array $value)
    {
        $file = ltrim($value['file'], '/');
        if ($this->mediaEntityTmpReadDirectory->isExist($file)) {
            $temporaryFile = FileProcessor::TMP_DIR . '/' . $file;
            $base64EncodedData = $this->getFileProcessor()->getBase64EncodedData($temporaryFile);
            /** @var ImageContentInterface $imageContentDataObject */
            $imageContentDataObject = $this->imageContentFactory->create()
                ->setName($value['name'])
                ->setBase64EncodedData($base64EncodedData)
                ->setType($value['type']);
            // Remove temporary file
            $this->getFileProcessor()->removeUploadedFile($temporaryFile);

            return $imageContentDataObject;
        }

        return $this->_value ?: $value['file'];
    }
}
