<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product gallery
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product;

use Magento\Framework\Storage\FileNotFoundException;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\Registry;
use Magento\Framework\Storage\StorageProvider;

/**
 * Product gallery block
 *
 * @api
 * @since 100.0.2
 */
class Gallery extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var StorageProvider
     */
    private $storageProvider;
    /**
     * @var Config
     */
    private $mediaConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Registry $registry
     * @param array $data
     * @param StorageProvider $storageProvider
     * @param Config $mediaConfig
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Registry $registry,
        array $data = [],
        StorageProvider $storageProvider = null,
        Config $mediaConfig = null
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->storageProvider = $storageProvider ?? ObjectManager::getInstance()->get(StorageProvider::class);
        $this->mediaConfig = $mediaConfig ?? ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getProduct()->getMetaTitle());
        return parent::_prepareLayout();
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get gallery collection
     *
     * @return Collection
     */
    public function getGalleryCollection()
    {
        return $this->getProduct()->getMediaGalleryImages();
    }

    /**
     * Get current image
     *
     * @return Image|null
     */
    public function getCurrentImage()
    {
        $imageId = $this->getRequest()->getParam('image');
        $image = null;
        if ($imageId) {
            $image = $this->getGalleryCollection()->getItemById($imageId);
        }

        if (!$image) {
            $image = $this->getGalleryCollection()->getFirstItem();
        }
        return $image;
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->getCurrentImage()->getUrl();
    }

    /**
     * Get image file
     *
     * @return mixed
     */
    public function getImageFile()
    {
        return $this->getCurrentImage()->getFile();
    }

    /**
     * Retrieve image width
     *
     * @return bool|int
     */
    public function getImageWidth()
    {
        $file = $this->getCurrentImage()->getFile();
        if (!$file) {
            return false;
        }
        $productMediaFile = $this->mediaConfig->getMediaPath($file);

        $mediaStorage = $this->storageProvider->get('media');
        if ($mediaStorage->has($productMediaFile)) {
            try {
                $meta = $mediaStorage->getMetadata($productMediaFile);
                $size = $meta['size'];
                if ($size > 600) {
                    return 600;
                } else {
                    return (int) $size;
                }
            } catch (FileNotFoundException $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Get previous image
     *
     * @return Image|false
     */
    public function getPreviousImage()
    {
        $current = $this->getCurrentImage();
        if (!$current) {
            return false;
        }
        $previous = false;
        foreach ($this->getGalleryCollection() as $image) {
            if ($image->getValueId() == $current->getValueId()) {
                return $previous;
            }
            $previous = $image;
        }
        return $previous;
    }

    /**
     * Get next image
     *
     * @return Image|false
     */
    public function getNextImage()
    {
        $current = $this->getCurrentImage();
        if (!$current) {
            return false;
        }

        $next = false;
        $currentFind = false;
        foreach ($this->getGalleryCollection() as $image) {
            if ($currentFind) {
                return $image;
            }
            if ($image->getValueId() == $current->getValueId()) {
                $currentFind = true;
            }
        }
        return $next;
    }

    /**
     * Get previous image url
     *
     * @return false|string
     */
    public function getPreviousImageUrl()
    {
        $image = $this->getPreviousImage();
        if ($image) {
            return $this->getUrl('*/*/*', ['_current' => true, 'image' => $image->getValueId()]);
        }
        return false;
    }

    /**
     * Get next image url
     *
     * @return false|string
     */
    public function getNextImageUrl()
    {
        $image = $this->getNextImage();
        if ($image) {
            return $this->getUrl('*/*/*', ['_current' => true, 'image' => $image->getValueId()]);
        }
        return false;
    }
}
