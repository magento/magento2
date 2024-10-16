<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

use Laminas\Validator\File\ExcludeExtension;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\FilesSize;
use Laminas\Validator\File\ImageSize;
use Laminas\Validator\ValidatorChain;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Http;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Validator
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSize;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $rootDirectory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Size $fileSize
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
     * @param string[] $errors Array of validation failure message codes @see ValidatorChain::getErrors()
     * @param array $fileInfo File info
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return string[] Array of error messages
     * @see \Magento\Catalog\Model\Product\Option\Type\File::_getValidatorErrors
     */
    protected function getValidatorErrors($errors, $fileInfo, $option)
    {
        $result = [];
        foreach ($errors as $errorCode) {
            switch ($errorCode) {
                case ExcludeExtension::FALSE_EXTENSION:
                    $result[] = __(
                        "The file '%1' for '%2' has an invalid extension.",
                        $fileInfo['title'],
                        $option->getTitle()
                    );
                    break;
                case Extension::FALSE_EXTENSION:
                    $result[] = __(
                        "The file '%1' for '%2' has an invalid extension.",
                        $fileInfo['title'],
                        $option->getTitle()
                    );
                    break;
                case ImageSize::WIDTH_TOO_BIG:
                case ImageSize::HEIGHT_TOO_BIG:
                    $result[] = __(
                        "The maximum allowed image size for '%1' is %2x%3 px.",
                        $option->getTitle(),
                        $option->getImageSizeX(),
                        $option->getImageSizeY()
                    );
                    break;
                case FilesSize::TOO_BIG:
                    $result[] = __(
                        "The file '%1' you uploaded is larger than the %2 megabytes allowed by our server.",
                        $fileInfo['title'],
                        $this->fileSize->getMaxFileSizeInMb()
                    );
                    break;
                case ImageSize::NOT_DETECTED:
                    $result[] = __(
                        'The file "%1" is empty. Select another file and try again.',
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
     */
    protected function parseExtensionsString($extensions)
    {
        $extensions = is_string($extensions) ? strtolower($extensions) : '';

        if (preg_match_all('/(?<extension>[a-z0-9]+)/si', $extensions, $matches)) {
            return $matches['extension'] ?: null;
        }
        return null;
    }

    /**
     * Adds required validators to th $object
     *
     * @param Http|ValidatorChain $object
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param array $fileFullPath
     * @return Http|ValidatorChain $object
     * @throws \Magento\Framework\Exception\InputException
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
            $object->addValidator(new ImageSize($dimensions));
        }

        // File extension
        $allowed = $this->parseExtensionsString($option->getFileExtension());
        if ($allowed !== null) {
            $object->addValidator(new Extension($allowed));
        } else {
            $forbidden = $this->parseExtensionsString($this->getConfigData('forbidden_extensions'));
            if ($forbidden !== null) {
                $object->addValidator(new ExcludeExtension($forbidden));
            }
        }

        $object->addValidator(
            new FilesSize(['max' => $this->fileSize->getMaxFileSize()])
        );
        return $object;
    }

    /**
     * Simple check if file is image
     *
     * @param array|string $fileInfo - either file data from \Laminas\File\Transfer\Transfer or file path
     * @return boolean
     * @see \Magento\Catalog\Model\Product\Option\Type\File::_isImage
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

        $fileContent = $this->rootDirectory->readFile($fileInfo);
        if (empty($fileContent) || !getimagesizefromstring($fileContent)) {
            return false;
        }

        return true;
    }
}
