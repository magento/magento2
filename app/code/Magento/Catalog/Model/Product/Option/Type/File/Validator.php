<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Option\Type\File;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
abstract class Validator
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\File\Size
     * @since 2.0.0
     */
    protected $fileSize;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     * @since 2.0.0
     */
    protected $rootDirectory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Size $fileSize
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->fileSize = $fileSize;
    }

    /**
     * Store Config value
     *
     * @param string $key Config value key
     * @return string
     * @since 2.0.0
     */
    protected function getConfigData($key)
    {
        return $this->scopeConfig->getValue(
            'catalog/custom_options/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Error messages for validator Errors
     *
     * @param string[] $errors Array of validation failure message codes @see \Zend_Validate::getErrors()
     * @param array $fileInfo File info
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return string[] Array of error messages
     * @see \Magento\Catalog\Model\Product\Option\Type\File::_getValidatorErrors
     * @since 2.0.0
     */
    protected function getValidatorErrors($errors, $fileInfo, $option)
    {
        $result = [];
        foreach ($errors as $errorCode) {
            switch ($errorCode) {
                case \Zend_Validate_File_ExcludeExtension::FALSE_EXTENSION:
                    $result[] = __(
                        "The file '%1' for '%2' has an invalid extension.",
                        $fileInfo['title'],
                        $option->getTitle()
                    );
                    break;
                case \Zend_Validate_File_Extension::FALSE_EXTENSION:
                    $result[] = __(
                        "The file '%1' for '%2' has an invalid extension.",
                        $fileInfo['title'],
                        $option->getTitle()
                    );
                    break;
                case \Zend_Validate_File_ImageSize::WIDTH_TOO_BIG:
                case \Zend_Validate_File_ImageSize::HEIGHT_TOO_BIG:
                    $result[] = __(
                        "The maximum allowed image size for '%1' is %2x%3 px.",
                        $option->getTitle(),
                        $option->getImageSizeX(),
                        $option->getImageSizeY()
                    );
                    break;
                case \Zend_Validate_File_FilesSize::TOO_BIG:
                    $result[] = __(
                        "The file '%1' you uploaded is larger than the %2 megabytes allowed by our server.",
                        $fileInfo['title'],
                        $this->fileSize->getMaxFileSizeInMb()
                    );
                    break;
                case \Zend_Validate_File_ImageSize::NOT_DETECTED:
                    $result[] = __(
                        "The file '%1' is empty. Please choose another one",
                        $fileInfo['title']
                    );
                    break;
                default:
                    $result[] = __(
                        "The file '%1' is invalid. Please choose another one",
                        $fileInfo['title']
                    );
            }
        }
        return $result;
    }

    /**
     * Parse file extensions string with various separators
     *
     * @param string $extensions String to parse
     * @return array|null
     * @see \Magento\Catalog\Model\Product\Option\Type\File::_parseExtensionsString
     * @since 2.0.0
     */
    protected function parseExtensionsString($extensions)
    {
        if (preg_match_all('/(?<extension>[a-z0-9]+)/si', strtolower($extensions), $matches)) {
            return $matches['extension'] ?: null;
        }
        return null;
    }

    /**
     * @param \Zend_File_Transfer_Adapter_Http|\Zend_Validate $object
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param array $fileFullPath
     * @return \Zend_File_Transfer_Adapter_Http|\Zend_Validate $object
     * @throws \Magento\Framework\Exception\InputException
     * @since 2.0.0
     */
    protected function buildImageValidator($object, $option, $fileFullPath = null)
    {
        $dimensions = [];

        if ($option->getImageSizeX() > 0) {
            $dimensions['maxwidth'] = $option->getImageSizeX();
        }
        if ($option->getImageSizeY() > 0) {
            $dimensions['maxheight'] = $option->getImageSizeY();
        }
        if (count($dimensions) > 0) {
            if ($fileFullPath !== null && !$this->isImage($fileFullPath)) {
                throw new \Magento\Framework\Exception\InputException(
                    __('File \'%1\' is not an image.', $option->getTitle())
                );
            }
            $object->addValidator(new \Zend_Validate_File_ImageSize($dimensions));
        }

        // File extension
        $allowed = $this->parseExtensionsString($option->getFileExtension());
        if ($allowed !== null) {
            $object->addValidator(new \Zend_Validate_File_Extension($allowed));
        } else {
            $forbidden = $this->parseExtensionsString($this->getConfigData('forbidden_extensions'));
            if ($forbidden !== null) {
                $object->addValidator(new \Zend_Validate_File_ExcludeExtension($forbidden));
            }
        }

        $object->addValidator(
            new \Zend_Validate_File_FilesSize(['max' => $this->fileSize->getMaxFileSize()])
        );
        return $object;
    }

    /**
     * Simple check if file is image
     *
     * @param array|string $fileInfo - either file data from \Zend_File_Transfer or file path
     * @return boolean
     * @see \Magento\Catalog\Model\Product\Option\Type\File::_isImage
     * @since 2.0.0
     */
    protected function isImage($fileInfo)
    {
        // Maybe array with file info came in
        if (is_array($fileInfo)) {
            return strstr($fileInfo['type'], 'image/');
        }

        // File path came in - check the physical file
        if (!$this->rootDirectory->isReadable($this->rootDirectory->getRelativePath($fileInfo))) {
            return false;
        }
        $imageInfo = getimagesize($fileInfo);
        if (!$imageInfo) {
            return false;
        }
        return true;
    }
}
