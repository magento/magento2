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
        $label = $value[self::VALUE_KEY_NAME] ?? $value[self::VALUE_KEY_FILE] ?? '';
        $rules = $this->getAttribute()->getValidationRules();

        // Extract and validate file name
        $fileNameResult = $this->extractAndValidateFileName($value, $label);
        if (isset($fileNameResult[self::RESULT_KEY_ERROR])) {
            return $fileNameResult[self::RESULT_KEY_ERROR];
        }
        $value[self::VALUE_KEY_NAME] = $fileNameResult[self::RESULT_KEY_NAME];
        $label = $fileNameResult[self::RESULT_KEY_LABEL];

        // Get and validate file path, then validate image properties
        $validationResult = $this->validateFilePathAndProperties($value, $label);
        if (isset($validationResult[self::RESULT_KEY_ERROR])) {
            return $validationResult[self::RESULT_KEY_ERROR];
        }

        $filePath = $validationResult[self::RESULT_KEY_FILE_PATH];
        $imageProp = $validationResult[self::RESULT_KEY_IMAGE_PROP];

        // Validate image format
        $formatResult = $this->validateImageFormat($imageProp, $value, $label);
        if (isset($formatResult[self::RESULT_KEY_ERROR])) {
            return $formatResult[self::RESULT_KEY_ERROR];
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
        $filePathResult = $this->getValidatedFilePath($value, $label);
        if (isset($filePathResult[self::RESULT_KEY_ERROR])) {
            return [self::RESULT_KEY_ERROR => $filePathResult[self::RESULT_KEY_ERROR]];
        }

        $imageProp = $this->validateImageProperties($filePathResult[self::RESULT_KEY_FILE_PATH], $label);
        if (isset($imageProp[self::RESULT_KEY_ERROR])) {
            return [self::RESULT_KEY_ERROR => $imageProp[self::RESULT_KEY_ERROR]];
        }

        return [
            self::RESULT_KEY_FILE_PATH => $filePathResult[self::RESULT_KEY_FILE_PATH],
            self::RESULT_KEY_IMAGE_PROP => $imageProp
        ];
    }

    /**
     * Extract and validate file name from value
     *
     * @param array $value
     * @param string $label
     * @return array Returns array with name and label on success or error key on failure
     */
    private function extractAndValidateFileName(array $value, string $label): array
    {
        if (empty($value[self::VALUE_KEY_NAME]) && !empty($value[self::VALUE_KEY_FILE])) {
            if (!$this->isValidFilePath($value[self::VALUE_KEY_FILE])) {
                return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
            }
            $pathInfo = $this->ioFileSystem->getPathInfo($value[self::VALUE_KEY_FILE]);
            $name = $pathInfo['basename'] ?? '';
            $label = $name ?: $label;
            return [self::RESULT_KEY_NAME => $name, self::RESULT_KEY_LABEL => $label];
        }
        $name = $value[self::VALUE_KEY_NAME] ?? '';
        if (empty($name)) {
            return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
        }
        return [self::RESULT_KEY_NAME => $name, self::RESULT_KEY_LABEL => $label];
    }

    /**
     * Get and validate file path
     *
     * @param array $value
     * @param string $label
     * @return array Returns array with filePath key on success or error key on failure
     */
    private function getValidatedFilePath(array $value, string $label): array
    {
        $filePath = $value[self::VALUE_KEY_TMP_NAME] ?? null;

        if (empty($filePath) && !empty($value[self::VALUE_KEY_FILE])) {
            $tmpFileName = ltrim($value[self::VALUE_KEY_FILE], '/');

            if (!$this->isValidFilePath($tmpFileName)) {
                return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
            }

            if ($tmpFileName !== '') {
                $filePath = $this->mediaEntityTmpReadDirectory->getAbsolutePath($tmpFileName);
            }
        }

        if (empty($filePath) || !is_string($filePath)) {
            return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
        }

        if (!$this->mediaWriteDirectory->getDriver()->isExists($filePath)) {
            return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
        }

        return [self::RESULT_KEY_FILE_PATH => $filePath];
    }

    /**
     * Validate image properties using getimagesize
     *
     * @param string $filePath
     * @param string $label
     * @return array Returns image properties array on success or array with error key on failure
     */
    private function validateImageProperties(string $filePath, string $label): array
    {
        try {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $imageProp = getimagesize($filePath);
        } catch (\Throwable $e) {
            $imageProp = false;
        }

        if (!$this->_isUploadedFile($filePath) || !$imageProp) {
            return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
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

        if (!isset($imageProp[2]) || !isset($allowImageTypes[$imageProp[2]])) {
            return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid image format.', $label)]];
        }

        // Modify image name if extension doesn't match
        $pathInfo = $this->ioFileSystem->getPathInfo($value[self::VALUE_KEY_NAME]);
        $extension = $pathInfo['extension'] ?? '';
        if ($extension != $allowImageTypes[$imageProp[2]]) {
            $value[self::VALUE_KEY_NAME] = ($pathInfo['filename'] ?? '') . '.' . $allowImageTypes[$imageProp[2]];
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
            $size = $value[self::VALUE_KEY_SIZE] ?? 0;
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
                        $value[self::VALUE_KEY_FILE],
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
        $file = ltrim($value[self::VALUE_KEY_FILE], '/');
        if ($this->mediaEntityTmpReadDirectory->isExist($file)) {
            $temporaryFile = FileProcessor::TMP_DIR . '/' . $file;
            $base64EncodedData = $this->getFileProcessor()->getBase64EncodedData($temporaryFile);
            /** @var ImageContentInterface $imageContentDataObject */
            $imageContentDataObject = $this->imageContentFactory->create()
                ->setName($value[self::VALUE_KEY_NAME])
                ->setBase64EncodedData($base64EncodedData)
                ->setType($value[self::VALUE_KEY_TYPE]);
            // Remove temporary file
            $this->getFileProcessor()->removeUploadedFile($temporaryFile);

            return $imageContentDataObject;
        }

        return $this->_value ?: $value[self::VALUE_KEY_FILE];
    }
}
