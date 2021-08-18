<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @var WriteInterface
     */
    private $mediaEntityTmpDirectory;

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
        FileProcessorFactory $fileProcessorFactory = null,
        ImageContentInterfaceFactory $imageContentInterfaceFactory = null,
        IoFileSystem $ioFileSystem = null,
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
        $writeFactory = $writeFactory ?? ObjectManager::getInstance()->get(WriteFactory::class);
        $directoryList = $directoryList ?? ObjectManager::getInstance()->get(DirectoryList::class);
        $this->mediaEntityTmpDirectory = $writeFactory->create(
            $directoryList->getPath($directoryList::MEDIA)
            . '/' . $this->_entityTypeCode
            . '/' . FileProcessor::TMP_DIR
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
        $label = $value['name'];
        $rules = $this->getAttribute()->getValidationRules();

        try {
            $imageProp = getimagesize($value['tmp_name']);
        } catch (\Throwable $e) {
            $imageProp = false;
        }

        if (!$this->_isUploadedFile($value['tmp_name']) || !$imageProp) {
            return [__('"%1" is not a valid file.', $label)];
        }

        $allowImageTypes = [1 => 'gif', 2 => 'jpg', 3 => 'png'];

        if (!isset($allowImageTypes[$imageProp[2]])) {
            return [__('"%1" is not a valid image format.', $label)];
        }

        // modify image name
        $extension = $this->ioFileSystem->getPathInfo($value['name'])['extension'];
        if ($extension != $allowImageTypes[$imageProp[2]]) {
            $value['name'] = $this->ioFileSystem->getPathInfo($value['name'])['filename']
                . '.'
                . $allowImageTypes[$imageProp[2]];
        }

        $maxFileSize = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_file_size'
        );
        $errors = [];
        if ($maxFileSize !== null) {
            $size = $value['size'];
            if ($maxFileSize < $size) {
                $errors[] = __('"%1" exceeds the allowed file size.', $label);
            }
        }

        $maxImageWidth = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_image_width'
        );
        if ($maxImageWidth !== null) {
            if ($maxImageWidth < $imageProp[0]) {
                $r = $maxImageWidth;
                $errors[] = __('"%1" width exceeds allowed value of %2 px.', $label, $r);
            }
        }

        $maxImageHeight = ArrayObjectSearch::getArrayElementByName(
            $rules,
            'max_image_height'
        );
        if ($maxImageHeight !== null) {
            if ($maxImageHeight < $imageProp[1]) {
                $r = $maxImageHeight;
                $errors[] = __('"%1" height exceeds allowed value of %2 px.', $label, $r);
            }
        }

        return $errors;
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
        $fileName = $this->mediaEntityTmpDirectory
            ->getDriver()
            ->getRealPathSafety(
                $this->mediaEntityTmpDirectory->getAbsolutePath(
                    ltrim(
                        $value['file'],
                        '/'
                    )
                )
            );
        return $this->getFileProcessor()->moveTemporaryFile(
            $this->mediaEntityTmpDirectory->getRelativePath($fileName)
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
        if ($this->mediaEntityTmpDirectory->isExist($file)) {
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
