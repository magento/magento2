<?php
/**
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
 * @category    Magento
 * @package     Magento_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * EAV Entity Attribute File Data Model
 *
 * @category    Magento
 * @package     Magento_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Attribute\Data;

class File extends \Magento\Eav\Model\Attribute\Data\AbstractData
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
     * @var \Magento\App\Dir
     */
    protected $_coreDir;

    /**
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\File\Validator\NotProtectedExtension $fileValidator
     * @param \Magento\App\Dir $coreDir
     */
    public function __construct(
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Core\Model\Logger $logger,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\File\Validator\NotProtectedExtension $fileValidator,
        \Magento\App\Dir $coreDir
    ) {
        parent::__construct($locale, $logger);
        $this->_coreData = $coreData;
        $this->_fileValidator = $fileValidator;
        $this->_coreDir = $coreDir;
    }

    /**
     * Extract data from request and return value
     *
     * @param \Magento\App\RequestInterface $request
     * @return array|string
     */
    public function extractValue(\Magento\App\RequestInterface $request)
    {
        if ($this->getIsAjaxRequest()) {
            return false;
        }

        $extend = $this->_getRequestValue($request);

        $attrCode  = $this->getAttribute()->getAttributeCode();
        if ($this->_requestScope) {
            $value  = array();
            if (strpos($this->_requestScope, '/') !== false) {
                $scopes = explode('/', $this->_requestScope);
                $mainScope  = array_shift($scopes);
            } else {
                $mainScope  = $this->_requestScope;
                $scopes     = array();
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
     * @return array
     */
    protected function _validateByRules($value)
    {
        $label  = $this->getAttribute()->getStoreLabel();
        $rules  = $this->getAttribute()->getValidateRules();
        $extension  = pathinfo($value['name'], PATHINFO_EXTENSION);

        if (!empty($rules['file_extensions'])) {
            $extensions = explode(',', $rules['file_extensions']);
            $extensions = array_map('trim', $extensions);
            if (!in_array($extension, $extensions)) {
                return array(
                    __('"%1" is not a valid file extension.', $label)
                );
            }
        }

        /**
         * Check protected file extension
         */
        if (!$this->_fileValidator->isValid($extension)) {
            return $this->_fileValidator->getMessages();
        }

        if (!is_uploaded_file($value['tmp_name'])) {
            return array(
                __('"%1" is not a valid file.', $label)
            );
        }

        if (!empty($rules['max_file_size'])) {
            $size = $value['size'];
            if ($rules['max_file_size'] < $size) {
                return array(
                    __('"%1" exceeds the allowed file size.', $label)
                );
            };
        }

        return array();
    }

    /**
     * Validate data
     *
     * @param array|string $value
     * @throws \Magento\Core\Exception
     * @return boolean
     */
    public function validateValue($value)
    {
        if ($this->getIsAjaxRequest()) {
            return true;
        }

        $errors     = array();
        $attribute  = $this->getAttribute();
        $label      = $attribute->getStoreLabel();

        $toDelete   = !empty($value['delete']) ? true : false;
        $toUpload   = !empty($value['tmp_name']) ? true : false;

        if (!$toUpload && !$toDelete && $this->getEntity()->getData($attribute->getAttributeCode())) {
            return true;
        }

        if (!$attribute->getIsRequired() && !$toUpload) {
            return true;
        }

        if ($attribute->getIsRequired() && !$toUpload) {
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
     * Export attribute value to entity model
     *
     * @param \Magento\Core\Model\AbstractModel $entity
     * @param array|string $value
     * @return \Magento\Eav\Model\Attribute\Data\File
     */
    public function compactValue($value)
    {
        if ($this->getIsAjaxRequest()) {
            return $this;
        }

        $attribute = $this->getAttribute();
        $original  = $this->getEntity()->getData($attribute->getAttributeCode());
        $toDelete  = false;
        if ($original) {
            if (!$attribute->getIsRequired() && !empty($value['delete'])) {
                $toDelete  = true;
            }
            if (!empty($value['tmp_name'])) {
                $toDelete  = true;
            }
        }

        $path = $this->_coreDir->getDir('media') . DS . $attribute->getEntity()->getEntityTypeCode();

        // unlink entity file
        if ($toDelete) {
            $this->getEntity()->setData($attribute->getAttributeCode(), '');
            $file = $path . $original;
            $ioFile = new \Magento\Io\File();
            if ($ioFile->fileExists($file)) {
                $ioFile->rm($file);
            }
        }

        if (!empty($value['tmp_name'])) {
            try {
                $uploader = new \Magento\File\Uploader($value);
                $uploader->setFilesDispersion(true);
                $uploader->setFilenamesCaseSensitivity(false);
                $uploader->setAllowRenameFiles(true);
                $uploader->save($path, $value['name']);
                $fileName = $uploader->getUploadedFileName();
                $this->getEntity()->setData($attribute->getAttributeCode(), $fileName);
            } catch (\Exception $e) {
                $this->_logger->logException($e);
            }
        }

        return $this;
    }

    /**
     * Restore attribute value from SESSION to entity model
     *
     * @param array|string $value
     * @return \Magento\Eav\Model\Attribute\Data\File
     */
    public function restoreValue($value)
    {
        return $this;
    }

    /**
     * Return formated attribute value from entity model
     *
     * @return string|array
     */
    public function outputValue($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $output = '';
        $value  = $this->getEntity()->getData($this->getAttribute()->getAttributeCode());
        if ($value) {
            switch ($format) {
                case \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON:
                    $output = array(
                        'value'     => $value,
                        'url_key'   => $this->_coreData->urlEncode($value)
                    );
                    break;
            }
        }

        return $output;
    }
}
