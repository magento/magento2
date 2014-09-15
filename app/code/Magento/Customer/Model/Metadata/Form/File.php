<?php
/**
 * Form Element File Data Model
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Framework\Service\ArrayObjectSearch;

class File extends AbstractData
{
    /**
     * Validator for check not protected extensions
     *
     * @var \Magento\Core\Model\File\Validator\NotProtectedExtension
     */
    protected $_validatorNotProtectedExtensions;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @var \Magento\Core\Model\File\Validator\NotProtectedExtension
     */
    protected $_fileValidator;

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $_fileSystem;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param null $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\File\Validator\NotProtectedExtension $fileValidator
     * @param \Magento\Framework\App\Filesystem $fileSystem
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Logger $logger,
        \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $value,
        $entityTypeCode,
        $isAjax,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\File\Validator\NotProtectedExtension $fileValidator,
        \Magento\Framework\App\Filesystem $fileSystem
    ) {
        parent::__construct($localeDate, $logger, $attribute, $localeResolver, $value, $entityTypeCode, $isAjax);
        $this->_coreData = $coreData;
        $this->_fileValidator = $fileValidator;
        $this->_fileSystem = $fileSystem;
    }

    /**
     * {@inheritdoc}
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        if ($this->getIsAjaxRequest()) {
            return false;
        }

        $extend = $this->_getRequestValue($request);

        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($this->_requestScope) {
            $value = array();
            if (strpos($this->_requestScope, '/') !== false) {
                $scopes = explode('/', $this->_requestScope);
                $mainScope = array_shift($scopes);
            } else {
                $mainScope = $this->_requestScope;
                $scopes = array();
            }

            if (!empty($_FILES[$mainScope])) {
                foreach ($_FILES[$mainScope] as $fileKey => $scopeData) {
                    foreach ($scopes as $scopeName) {
                        if (isset($scopeData[$scopeName])) {
                            $scopeData = $scopeData[$scopeName];
                        } else {
                            $scopeData[$scopeName] = array();
                        }
                    }

                    if (isset($scopeData[$attrCode])) {
                        $value[$fileKey] = $scopeData[$attrCode];
                    }
                }
            } else {
                $value = array();
            }
        } else {
            if (isset($_FILES[$attrCode])) {
                $value = $_FILES[$attrCode];
            } else {
                $value = array();
            }
        }

        if (!empty($extend['delete'])) {
            $value['delete'] = true;
        }

        return $value;
    }

    /**
     * Validate file by attribute validate rules
     * Return array of errors
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
        if (!is_null($fileExtensions)) {
            $extensions = explode(',', $fileExtensions);
            $extensions = array_map('trim', $extensions);
            if (!in_array($extension, $extensions)) {
                return array(__('"%1" is not a valid file extension.', $extension));
            }
        }

        /**
         * Check protected file extension
         */
        if (!$this->_fileValidator->isValid($extension)) {
            return $this->_fileValidator->getMessages();
        }

        if (!$this->_isUploadedFile($value['tmp_name'])) {
            return array(__('"%1" is not a valid file.', $label));
        }

        $maxFileSize = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_file_size'
        );
        if (!is_null($maxFileSize)) {
            $size = $value['size'];
            if ($maxFileSize < $size) {
                return array(__('"%1" exceeds the allowed file size.', $label));
            }
        }

        return array();
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
        return is_uploaded_file($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue($value)
    {
        if ($this->getIsAjaxRequest()) {
            return true;
        }

        $errors = array();
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
     * {@inheritdoc}
     *
     * @return $this|string
     */
    public function compactValue($value)
    {
        if ($this->getIsAjaxRequest()) {
            return $this;
        }

        $attribute = $this->getAttribute();
        $original = $this->_value;
        $toDelete = false;
        if ($original) {
            if (!$attribute->isRequired() && !empty($value['delete'])) {
                $toDelete = true;
            }
            if (!empty($value['tmp_name'])) {
                $toDelete = true;
            }
        }

        $path = $this->_fileSystem->getPath(\Magento\Framework\App\Filesystem::MEDIA_DIR)
            . '/' . $this->_entityTypeCode;

        $result = $original;
        // unlink entity file
        if ($toDelete) {
            $result = '';
            $file = $path . $original;
            $ioFile = new \Magento\Framework\Io\File();
            if ($ioFile->fileExists($file)) {
                $ioFile->rm($file);
            }
        }

        if (!empty($value['tmp_name'])) {
            try {
                $uploader = new \Magento\Framework\File\Uploader($value);
                $uploader->setFilesDispersion(true);
                $uploader->setFilenamesCaseSensitivity(false);
                $uploader->setAllowRenameFiles(true);
                $uploader->save($path, $value['name']);
                $fileName = $uploader->getUploadedFileName();
                $result = $fileName;
            } catch (\Exception $e) {
                $this->_logger->logException($e);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function restoreValue($value)
    {
        return $this->_value;
    }

    /**
     * {@inheritdoc}
     */
    public function outputValue($format = \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_TEXT)
    {
        $output = '';
        if ($this->_value) {
            switch ($format) {
                case \Magento\Customer\Model\Metadata\ElementFactory::OUTPUT_FORMAT_JSON:
                    $output = array('value' => $this->_value, 'url_key' => $this->_coreData->urlEncode($this->_value));
                    break;
            }
        }

        return $output;
    }
}
