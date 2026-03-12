<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Framework\Api\ArrayObjectSearch;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;

/**
 * Processes files that are save for customer.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class File extends AbstractData
{
    public const UPLOADED_FILE_SUFFIX = '_uploaded';

    /**#@+ Value array keys for file input */
    public const VALUE_KEY_NAME = 'name';
    public const VALUE_KEY_FILE = 'file';
    public const VALUE_KEY_TMP_NAME = 'tmp_name';
    public const VALUE_KEY_DELETE = 'delete';
    public const VALUE_KEY_SIZE = 'size';
    public const VALUE_KEY_TYPE = 'type';
    /**#@-*/

    /**#@+ Result array keys for validation methods */
    public const RESULT_KEY_ERROR = 'error';
    public const RESULT_KEY_NAME = 'name';
    public const RESULT_KEY_LABEL = 'label';
    public const RESULT_KEY_FILE_PATH = 'filePath';
    public const RESULT_KEY_IMAGE_PROP = 'imageProp';
    /**#@-*/

    /**
     * Validator for check not protected extensions
     *
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
     */
    protected $_validatorNotProtectedExtensions;

    /**
     * Core data
     *
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension
     */
    protected $_fileValidator;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * @var FileProcessorFactory
     * @deprecated 101.0.0 Call fileProcessor directly from code
     * @see $this->fileProcessor
     */
    protected $fileProcessorFactory;

    /**
     * @var IoFile|null
     */
    private $ioFile;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string|array $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $fileValidator
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param \Magento\Customer\Model\FileProcessorFactory|null $fileProcessorFactory
     * @param IoFile|null $ioFile
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $value,
        $entityTypeCode,
        $isAjax,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $fileValidator,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        ?\Magento\Customer\Model\FileProcessorFactory $fileProcessorFactory = null,
        ?IoFile $ioFile = null
    ) {
        $value = $this->prepareFileValue($value);
        parent::__construct($localeDate, $logger, $attribute, $localeResolver, $value, $entityTypeCode, $isAjax);
        $this->urlEncoder = $urlEncoder;
        $this->_fileValidator = $fileValidator;
        $this->_fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->fileProcessorFactory = $fileProcessorFactory ?: ObjectManager::getInstance()
            ->get(FileProcessorFactory::class);
        $this->fileProcessor = $this->fileProcessorFactory->create(['entityTypeCode' => $this->_entityTypeCode]);
        $this->ioFile = $ioFile ?: ObjectManager::getInstance()
            ->get(IoFile::class);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        $extend = $this->_getRequestValue($request);

        // phpcs:disable Magento2.Security.Superglobal
        $attrCode = $this->getAttribute()->getAttributeCode();

        // phpcs:disable Magento2.Security.Superglobal
        $uploadedFile = $request->getParam($attrCode . static::UPLOADED_FILE_SUFFIX);
        if ($uploadedFile) {
            $value = $uploadedFile;
        } elseif ($this->_requestScope || !isset($_FILES[$attrCode])) {
            $value = [];
            if ($this->_requestScope !== null && strpos($this->_requestScope, DIRECTORY_SEPARATOR) !== false) {
                $scopes = explode(DIRECTORY_SEPARATOR, $this->_requestScope);
                $mainScope = array_shift($scopes);
            } else {
                $mainScope = $this->_requestScope;
                $scopes = [];
            }
            // phpcs:disable Magento2.Security.Superglobal
            if ($mainScope !== null && !empty($_FILES[$mainScope])) {
                foreach ($_FILES[$mainScope] as $fileKey => $scopeData) {
                    // phpcs:enable Magento2.Security.Superglobal
                    foreach ($scopes as $scopeName) {
                        if (isset($scopeData[$scopeName])) {
                            $scopeData = $scopeData[$scopeName];
                        } else {
                            $scopeData[$scopeName] = [];
                        }
                    }

                    if (isset($scopeData[$attrCode])) {
                        $value[$fileKey] = $scopeData[$attrCode];
                    }
                }
            } elseif (isset($extend[0][self::VALUE_KEY_FILE]) && !empty($extend[0][self::VALUE_KEY_FILE])) {
                /**
                 * This case is required by file uploader UI component
                 *
                 * $extend[0]['file'] - uses for AJAX validation
                 * $extend[0] - uses for POST request
                 */
                $value = $this->getIsAjaxRequest() ? $extend[0][self::VALUE_KEY_FILE] : $extend[0];
            } else {
                $value = [];
            }
        } else {
            // phpcs:disable Magento2.Security.Superglobal
            if (isset($_FILES[$attrCode])) {
                $value = $_FILES[$attrCode];
                // phpcs:enable Magento2.Security.Superglobal
            } else {
                $value = [];
            }
        }
        // phpcs:enable Magento2.Security.Superglobal

        if (!empty($extend[self::VALUE_KEY_DELETE])) {
            $value[self::VALUE_KEY_DELETE] = true;
        }

        return $value;
    }

    /**
     * Validate file by attribute validate rules. Returns array of errors.
     *
     * @param array $value
     * @return string[]
     */
    protected function _validateByRules($value)
    {
        $label = $value[self::VALUE_KEY_NAME] ?? $value[self::VALUE_KEY_FILE] ?? '';
        $rules = $this->getAttribute()->getValidationRules() ?? [];

        // Extract and validate file name
        $fileNameResult = $this->extractAndValidateFileName($value, $label);
        if (isset($fileNameResult[self::RESULT_KEY_ERROR])) {
            return $fileNameResult[self::RESULT_KEY_ERROR];
        }
        $value[self::VALUE_KEY_NAME] = $fileNameResult[self::RESULT_KEY_NAME];
        $label = $fileNameResult[self::RESULT_KEY_LABEL];

        // Validate file extension
        $extensionResult = $this->validateFileExtension($value[self::VALUE_KEY_NAME], $rules, $label);
        if (isset($extensionResult[self::RESULT_KEY_ERROR])) {
            return $extensionResult[self::RESULT_KEY_ERROR];
        }

        // Validate file path
        $filePathResult = $this->getFilePath($value, $label);
        if (isset($filePathResult[self::RESULT_KEY_ERROR])) {
            return $filePathResult[self::RESULT_KEY_ERROR];
        }

        // Validate file size
        $sizeResult = $this->validateFileSize($value, $rules, $label);
        if (isset($sizeResult[self::RESULT_KEY_ERROR])) {
            return $sizeResult[self::RESULT_KEY_ERROR];
        }
        return [];
    }

    /**
     * Extract and validate file name from value
     *
     * @param array $value
     * @param string $label
     * @return array Returns array with name and label or error array
     */
    private function extractAndValidateFileName(array $value, string $label): array
    {
        // For UI component uploads, get name from file path if not provided
        if (empty($value[self::VALUE_KEY_NAME]) && !empty($value[self::VALUE_KEY_FILE])) {
            // Validate file path for security before extracting filename
            if (!$this->isValidFilePath($value[self::VALUE_KEY_FILE])) {
                return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
            }
            $pathInfo = $this->ioFile->getPathInfo($value[self::VALUE_KEY_FILE]);
            $name = $pathInfo['basename'] ?? '';
            $label = $name;
        } else {
            $name = $value[self::VALUE_KEY_NAME] ?? '';
        }

        // Ensure we have a valid file name
        if (empty($name)) {
            return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
        }

        return [self::RESULT_KEY_NAME => $name, self::RESULT_KEY_LABEL => $label];
    }

    /**
     * Validate file extension
     *
     * @param string $fileName
     * @param array $rules
     * @param string $label
     * @return array Returns array with RESULT_KEY_ERROR on failure or empty array on success
     */
    private function validateFileExtension(string $fileName, array $rules, string $label): array
    {
        $pathInfo = $this->ioFile->getPathInfo($fileName);
        $extension = $pathInfo['extension'] ?? '';

        if (empty($extension)) {
            return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
        }

        $fileExtensions = ArrayObjectSearch::getArrayElementByName($rules, 'file_extensions');
        if ($fileExtensions !== null) {
            $extensions = explode(',', $fileExtensions);
            $extensions = array_map('trim', $extensions);
            if (!in_array($extension, $extensions)) {
                return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file extension.', $extension)]];
            }
        }

        // Check protected file extension
        if (!$this->_fileValidator->isValid($extension)) {
            return [self::RESULT_KEY_ERROR => $this->_fileValidator->getMessages()];
        }

        return [];
    }

    /**
     * Get and validate file path
     *
     * @param array $value
     * @param string $label
     * @return array Returns array with 'filePath' key on success or 'error' key on failure
     */
    private function getFilePath(array $value, string $label): array
    {
        $filePath = $value[self::VALUE_KEY_TMP_NAME] ?? $value[self::VALUE_KEY_FILE] ?? null;
        if (empty($filePath)) {
            return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
        }

        if (!$this->_isUploadedFile($filePath)) {
            return [self::RESULT_KEY_ERROR => [__('"%1" is not a valid file.', $label)]];
        }

        return [self::RESULT_KEY_FILE_PATH => $filePath];
    }

    /**
     * Validate file size
     *
     * @param array $value
     * @param array $rules
     * @param string $label
     * @return array Returns array with RESULT_KEY_ERROR on failure or empty array on success
     */
    private function validateFileSize(array $value, array $rules, string $label): array
    {
        $maxFileSize = ArrayObjectSearch::getArrayElementByName($rules, 'max_file_size');
        if ($maxFileSize === null) {
            return [];
        }

        $size = $value[self::VALUE_KEY_SIZE] ?? 0;
        // For UI component uploads, get file size if not provided
        if ($size === 0 && !empty($value[self::VALUE_KEY_FILE])) {
            $size = $this->getTemporaryFileSize($value[self::VALUE_KEY_FILE]);
        }

        if ($maxFileSize < $size) {
            return [self::RESULT_KEY_ERROR => [__('"%1" exceeds the allowed file size.', $label)]];
        }

        return [];
    }

    /**
     * Validate file path for security
     *
     * @param string $filePath
     * @return bool
     */
    protected function isValidFilePath(string $filePath): bool
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
     * Get file size from temporary directory
     *
     * @param string $filePath
     * @return int
     */
    private function getTemporaryFileSize(string $filePath): int
    {
        if (!$this->isValidFilePath($filePath)) {
            return 0;
        }

        $pathInfo = $this->ioFile->getPathInfo($filePath);
        $fileName = $pathInfo['basename'] ?? '';
        if (empty($fileName)) {
            return 0;
        }

        $temporaryFile = FileProcessor::TMP_DIR . '/' . ltrim($fileName, '/');
        if ($this->fileProcessor->isExist($temporaryFile)) {
            $stat = $this->fileProcessor->getStat($temporaryFile);
            return (int)($stat['size'] ?? 0);
        }

        return 0;
    }

    /**
     * Helper function that checks if the file was uploaded.
     *
     * This helper function is needed for testing.
     *
     * @param string $filename
     * @return bool
     */
    protected function _isUploadedFile($filename)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (is_uploaded_file($filename)) {
            return true;
        }

        // This case is required for file uploader UI component
        $temporaryFile = FileProcessor::TMP_DIR . '/' . $this->ioFile->getPathInfo($filename)['basename'];
        if ($this->fileProcessor->isExist($temporaryFile)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateValue($value)
    {
        if ($this->getIsAjaxRequest()) {
            return true;
        }

        $errors = [];
        $attribute = $this->getAttribute();
        $label = $attribute->getStoreLabel();

        $toDelete = !empty($value[self::VALUE_KEY_DELETE]) ? true : false;
        // Check both tmp_name (traditional upload) and file (UI component upload)
        $toUpload = !empty($value[self::VALUE_KEY_TMP_NAME])
            || (!empty($value[self::VALUE_KEY_FILE]) && $value[self::VALUE_KEY_FILE] !== $this->_value);

        if (!$toUpload && !$toDelete && $this->_value) {
            return true;
        }

        if (!$attribute->isRequired() && !$toUpload) {
            return true;
        }

        if ($attribute->isRequired() && !$toUpload) {
            $errors[] = __('"%1" is a required value.', $label);
        }

        if ($toUpload) {
            $errors = array_merge($errors, $this->_validateByRules($value));
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ImageContentInterface|array|string|null
     */
    public function compactValue($value)
    {
        if ($this->getIsAjaxRequest()) {
            return '';
        }

        // Remove outdated file (in the case of file uploader UI component)
        if (!empty($this->_value)
            && (!empty($value[self::VALUE_KEY_DELETE])
                || ($this->_entityTypeCode === 'customer' && empty($value)))
        ) {
            $this->fileProcessor->removeUploadedFile($this->_value);
            return $value;
        }

        if ($value && is_string($value) && $this->fileProcessor->isExist($value)) {
            $result = $value;
        } elseif (isset($value[self::VALUE_KEY_FILE]) && !empty($value[self::VALUE_KEY_FILE])) {
            $result = $this->processUiComponentValue($value);
        } else {
            $result = $this->processInputFieldValue($value);
        }

        return $result;
    }

    /**
     * Process file uploader UI component data
     *
     * @param array $value
     * @return string|null
     */
    protected function processUiComponentValue(array $value)
    {
        if ($value[self::VALUE_KEY_FILE] == $this->_value) {
            return $this->_value;
        }
        $result = $this->fileProcessor->moveTemporaryFile($value[self::VALUE_KEY_FILE]);
        return $result;
    }

    /**
     * Process input type=file component data
     *
     * @param string $value
     * @return bool|int|string
     */
    protected function processInputFieldValue($value)
    {
        $toDelete = false;
        if ($this->_value) {
            if (!$this->getAttribute()->isRequired()
                && !empty($value[self::VALUE_KEY_DELETE])
            ) {
                $toDelete = true;
            }
            if (!empty($value[self::VALUE_KEY_TMP_NAME])) {
                $toDelete = true;
            }
        }

        $mediaDir = $this->_fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $result = $this->_value;

        if ($toDelete) {
            $mediaDir->delete($this->_entityTypeCode . DIRECTORY_SEPARATOR .
                ltrim($this->_value ?? '', DIRECTORY_SEPARATOR));
            $result = '';
        }

        if (!empty($value[self::VALUE_KEY_TMP_NAME])) {
            $uploader = $this->uploaderFactory->create(['fileId' => $value]);
            $fileExtension = $uploader->getFileExtension();
            if (!$this->_fileValidator->isValid($fileExtension)) {
                throw new LocalizedException($this->_fileValidator->getMessages()[$fileExtension]);
            }
            $uploader->setFilesDispersion(true);
            $uploader->setFilenamesCaseSensitivity(false);
            $uploader->setAllowRenameFiles(true);
            try {
                $uploader->save($mediaDir->getAbsolutePath($this->_entityTypeCode), $value[self::VALUE_KEY_NAME]);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
            $result = $uploader->getUploadedFileName();
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function restoreValue($value)
    {
        if (!empty($this->_value)) {
            return $this->_value;
        }
        return $this->compactValue($value);
    }

    /**
     * @inheritdoc
     */
    public function outputValue($format = \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        $output = '';
        if ($this->_value) {
            switch ($format) {
                case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_JSON:
                    $output = ['value' => $this->_value, 'url_key' => $this->urlEncoder->encode($this->_value)];
                    break;
            }
        }

        return $output;
    }

    /**
     * Get file processor
     *
     * @return FileProcessor
     * @deprecated 100.1.3 we don’t use such approach anymore. Call fileProcessor directly
     * @see $this->fileProcessor
     */
    protected function getFileProcessor()
    {
        return $this->fileProcessor;
    }

    /**
     * Prepare File value.
     *
     * @param array|string|null $value
     * @return array|string|null
     */
    private function prepareFileValue($value): array|string|null
    {
        if (is_array($value) && isset($value['value'])) {
            $value = $value['value'];
        }

        return $value;
    }
}
