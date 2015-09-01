<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Model\Product;
use \Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class AbstractMediaGalleryEntryProcessor
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
abstract class AbstractMediaGalleryEntryProcessor
{
    /**
     * @var \Magento\Catalog\Model\Resource\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $fileStorageDb;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media
     */
    protected $resourceEntryMediaGallery;

    /**
     * @param \Magento\Catalog\Model\Resource\ProductFactory $productFactory
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $resourceEntryMediaGallery
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\ProductFactory $productFactory,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media $resourceEntryMediaGallery
    ) {
        $this->productFactory = $productFactory;
        $this->fileStorageDb = $fileStorageDb;
        $this->jsonHelper = $jsonHelper;
        $this->mediaConfig = $mediaConfig;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->resourceEntryMediaGallery = $resourceEntryMediaGallery;
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function beforeLoad(Product $product, AbstractAttribute $attribute)
    {
        /**
         * The method should be overwritten in a derived Class to implement 'beforeLoad' functionality
         */
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function afterLoad(Product $product, AbstractAttribute $attribute)
    {
        /**
         * The method should be overwritten in a derived Class to implement 'afterLoad' functionality
         */
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function beforeSave(Product $product, AbstractAttribute $attribute)
    {
        /**
         * The method should be overwritten in a derived Class to implement 'beforeSave' functionality
         */
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function afterSave(Product $product, AbstractAttribute $attribute)
    {
        /**
         * The method should be overwritten in a derived Class to implement 'afterSave' functionality
         */
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function beforeDelete(Product $product, AbstractAttribute $attribute)
    {
        /**
         * The method should be overwritten in a derived Class to implement 'beforeDelete' functionality
         */
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function afterDelete(Product $product, AbstractAttribute $attribute)
    {
        /**
         * The method should be overwritten in a derived Class to implement 'afterDelete' functionality
         */
    }
}
