<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Category\Attribute\Backend;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\File\Uploader;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Catalog category image attribute backend model
 *
 * @api
 * @since 100.0.2
 */
class Image extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     *
     * @deprecated 101.0.0
     * @see MAGETWO-71174
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     *
     * @deprecated 101.0.0
     * @see MAGETWO-71174
     */
    protected $_filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     *
     * @deprecated 101.0.0
     * @see MAGETWO-71174
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     *
     * @deprecated 101.0.0
     * @see MAGETWO-71174
     */
    protected $_logger;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var string
     */
    private $additionalData = '_additional_data_';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param StoreManagerInterface $storeManager
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        ?StoreManagerInterface $storeManager = null,
        ?ImageUploader $imageUploader = null
    ) {
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger = $logger;
        $this->storeManager = $storeManager ??
            ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->imageUploader = $imageUploader ??
            ObjectManager::getInstance()->get(ImageUploader::class);
    }

    /**
     * Gets image name from $value array.
     *
     * Will return empty string in a case when $value is not an array.
     *
     * @param array $value Attribute value
     * @return string
     */
    private function getUploadedImageName($value)
    {
        if (is_array($value) && isset($value[0]['name'])) {
            return $value[0]['name'];
        }

        return '';
    }

    /**
     * Check that image name exists in catalog/category directory and return new image name if it already exists.
     *
     * @param string $imageName
     * @return string
     */
    private function checkUniqueImageName(string $imageName): string
    {
        $mediaDirectory = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $imageAbsolutePath = $mediaDirectory->getAbsolutePath(
            $this->imageUploader->getBasePath() . DIRECTORY_SEPARATOR . $imageName
        );

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $imageName = call_user_func([Uploader::class, 'getNewFilename'], $imageAbsolutePath);

        return $imageName;
    }

    /**
     * Avoiding saving potential upload data to DB.
     *
     * Will set empty image attribute value if image was not uploaded.
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 101.0.8
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $value = $object->getData($attributeName);

        if ($this->isTmpFileAvailable($value) && $imageName = $this->getUploadedImageName($value)) {
            try {
                /** @var StoreInterface $store */
                $store = $this->storeManager->getStore();
                $baseMediaDir = $store->getBaseMediaDir();
                $newImgRelativePath = $this->imageUploader->moveFileFromTmp($imageName, true);
                $value[0]['url'] = '/' . $baseMediaDir . '/' . $newImgRelativePath;
                $value[0]['name'] = $value[0]['url'];
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        } elseif ($this->fileResidesOutsideCategoryDir($value)) {
            // use relative path for image attribute so we know it's outside of category dir when we fetch it
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $value[0]['url'] = parse_url($value[0]['url'], PHP_URL_PATH);
            $value[0]['name'] = $value[0]['url'];
        }

        if ($imageName = $this->getUploadedImageName($value)) {
            if (!$this->fileResidesOutsideCategoryDir($value)) {
                $imageName = $this->checkUniqueImageName($imageName);
            }
            $object->setData($this->additionalData . $attributeName, $value);
            $object->setData($attributeName, $imageName);
        } elseif (!is_string($value)) {
            $object->setData($attributeName, null);
        }

        return parent::beforeSave($object);
    }

    /**
     * Check if temporary file is available for new image upload.
     *
     * @param array $value
     * @return bool
     */
    private function isTmpFileAvailable($value)
    {
        return is_array($value) && isset($value[0]['tmp_name']);
    }

    /**
     * Check for file path resides outside of category media dir. The URL will be a path including pub/media if true
     *
     * @param array|null $value
     * @return bool
     */
    private function fileResidesOutsideCategoryDir($value)
    {
        if (!is_array($value) || !isset($value[0]['url'])) {
            return false;
        }

        $fileUrl = ltrim($value[0]['url'], '/');
        $baseMediaDir = $this->_filesystem->getUri(DirectoryList::MEDIA);

        if (!$baseMediaDir) {
            return false;
        }

        return strpos($fileUrl, $baseMediaDir) !== false;
    }

    /**
     * Save uploaded file and set its name to category
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Catalog\Model\Category\Attribute\Backend\Image
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave($object)
    {
        return $this;
    }
}
