<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Framework\Api\ArrayObjectSearch;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;

/**
 * Processes files that are save for customer.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class File extends AbstractData
{
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
     * @deprecated 100.2.0
     */
    protected $fileProcessorFactory;

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
        \Magento\Customer\Model\FileProcessorFactory $fileProcessorFactory = null
    ) {
        parent::__construct($localeDate, $logger, $attribute, $localeResolver, $value, $entityTypeCode, $isAjax);
        $this->urlEncoder = $urlEncoder;
        $this->_fileValidator = $fileValidator;
        $this->_fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->fileProcessorFactory = $fileProcessorFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Customer\Model\FileProcessorFactory::class);
        $this->fileProcessor = $this->fileProcessorFactory->create(['entityTypeCode' => $this->_entityTypeCode]);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        $extend = $this->_getRequestValue($request);

        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($this->_requestScope || !isset($_FILES[$attrCode])) {
            $value = [];
            if (strpos($this->_requestScope, '/') !== false) {
                $scopes = explode('/', $this->_requestScope);
                $mainScope = array_shift($scopes);
            } else {
                $mainScope = $this->_requestScope;
                $scopes = [];
            }

            if (!empty($_FILES[$mainScope])) {
                foreach ($_FILES[$mainScope] as $fileKey => $scopeData) {
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
            } elseif (isset($extend[0]['file']) && !empty($extend[0]['file'])) {
                /**
                 * This case is required by file uploader UI component
                 *
                 * $extend[0]['file'] - uses for AJAX validation
                 * $extend[0] - uses for POST request
                 */
                $value = $this->getIsAjaxRequest() ? $extend[0]['file'] : $extend[0];
            } else {
                $value = [];
            }
        } else {
            if (isset($_FILES[$attrCode])) {
                $value = $_FILES[$attrCode];
            } else {
                $value = [];
            }
        }

        if (!empty($extend['delete'])) {
            $value['delete'] = true;
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
        $label = $value['name'];
        $rules = $this->getAttribute()->getValidationRules();
        $extension = pathinfo($value['name'], PATHINFO_EXTENSION);
        $fileExtensions = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'file_extensions'
        );
        if ($fileExtensions !== null) {
            $extensions = explode(',', $fileExtensions);
            $extensions = array_map('trim', $extensions);
            if (!in_array($extension, $extensions)) {
                return [__('"%1" is not a valid file extension.', $extension)];
            }
        }

        /**
         * Check protected file extension
         */
        if (!$this->_fileValidator->isValid($extension)) {
            return $this->_fileValidator->getMessages();
        }

        if (!$this->_isUploadedFile($value['tmp_name'])) {
            return [__('"%1" is not a valid file.', $label)];
        }

        $maxFileSize = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_file_size'
        );
        if ($maxFileSize !== null) {
            $size = $value['size'];
            if ($maxFileSize < $size) {
                return [__('"%1" exceeds the allowed file size.', $label)];
            }
        }

        return [];
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
        if (is_uploaded_file($filename)) {
            return true;
        }

        // This case is required for file uploader UI component
        $temporaryFile = FileProcessor::TMP_DIR . '/' . pathinfo($filename)['basename'];
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

        $toDelete = !empty($value['delete']) ? true : false;
        $toUpload = !empty($value['tmp_name']) ? true : false;

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
     * @return ImageContentInterface|array|string|null
     */
    public function compactValue($value)
    {
        if ($this->getIsAjaxRequest()) {
            return $this;
        }

        // Remove outdated file (in the case of file uploader UI component)
        if (empty($value) && !empty($this->_value)) {
            $this->fileProcessor->removeUploadedFile($this->_value);
            return $value;
        }

        if (isset($value['file']) && !empty($value['file'])) {
            if ($value['file'] == $this->_value) {
                return $this->_value;
            }
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
        $result = $this->fileProcessor->moveTemporaryFile($value['file']);
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
                && !empty($value['delete'])
            ) {
                $toDelete = true;
            }
            if (!empty($value['tmp_name'])) {
                $toDelete = true;
            }
        }

        $mediaDir = $this->_fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
        $result = $this->_value;

        if ($toDelete) {
            $mediaDir->delete($this->_entityTypeCode . '/' . ltrim($this->_value, '/'));
            $result = '';
        }

        if (!empty($value['tmp_name'])) {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => $value]);
                $uploader->setFilesDispersion(true);
                $uploader->setFilenamesCaseSensitivity(false);
                $uploader->setAllowRenameFiles(true);
                $uploader->save($mediaDir->getAbsolutePath($this->_entityTypeCode), $value['name']);
                $result = $uploader->getUploadedFileName();
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function restoreValue($value)
    {
        return $this->_value;
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
     * @deprecated 100.1.3
     */
    protected function getFileProcessor()
    {
        return $this->fileProcessor;
    }
}
