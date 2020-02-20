<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata\Form;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\FileProcessor;
use Magento\Framework\Api\ArrayObjectSearch;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFileSystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteFactory;

/**
 * Metadata for form image field
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Image extends File
{
    /**
     * @var WriteFactory
     */
    protected $writeFactory;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var ImageContentInterfaceFactory
     */
    private $imageContentFactory;

    /**
     * @var IoFileSystem
     */
    private $ioFileSystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaCustomerTmpDirectory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param null|string $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $fileValidator
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param \Magento\Customer\Model\FileProcessorFactory|null $fileProcessorFactory
     * @param \Magento\Framework\Api\Data\ImageContentInterfaceFactory|null $imageContentInterfaceFactory
     * @param IoFileSystem|null $ioFileSystem
     * @param DirectoryList|null $directoryList
     * @param WriteFactory|null $writeFactory
     * @throws LocalizedException
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
        \Magento\Customer\Model\FileProcessorFactory $fileProcessorFactory = null,
        \Magento\Framework\Api\Data\ImageContentInterfaceFactory $imageContentInterfaceFactory = null,
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
            ->get(\Magento\Framework\Api\Data\ImageContentInterfaceFactory::class);
        $this->ioFileSystem = $ioFileSystem ?: ObjectManager::getInstance()
            ->get(IoFileSystem::class);
        $this->writeFactory = $writeFactory ?: ObjectManager::getInstance()
            ->get(Filesystem\Directory\WriteFactory::class);
        $this->directoryList =  $directoryList ?: ObjectManager::getInstance()
            ->get(DirectoryList::class);
        $this->mediaCustomerTmpDirectory = $this->writeFactory->create($this->directoryList->getPath($this->directoryList::MEDIA) . '/' . $this->_entityTypeCode . '/' . FileProcessor::TMP_DIR);
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
        } catch (\Exception $e) {
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
            $result = $this->processCustomerAddressValue($value);
            return $result;
        }

        if ($this->_entityTypeCode == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $result = $this->processCustomerValue($value);
            return $result;
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
        return $this->getFileProcessor()->moveTemporaryFile($value['file']);
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
        if ($this->mediaCustomerTmpDirectory->isExist($file)) {
            $temporaryFile = FileProcessor::TMP_DIR . '/' . $file;
            $base64EncodedData = $this->getFileProcessor()->getBase64EncodedData($temporaryFile);
            /** @var ImageContentInterface $imageContentDataObject */
            $imageContentDataObject = $this->imageContentFactory->create()
                ->setName($value['name'])
                ->setBase64EncodedData($base64EncodedData)
                ->setType($value['type']);
            // Remove temporary file
            $this->mediaCustomerTmpDirectory->delete($file);

            return $imageContentDataObject;
        }

        return $this->_value;
    }
}
