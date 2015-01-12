<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product gallery
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection;

class Gallery extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getProduct()->getMetaTitle());
        return parent::_prepareLayout();
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * @return Collection
     */
    public function getGalleryCollection()
    {
        return $this->getProduct()->getMediaGalleryImages();
    }

    /**
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
     * @return string
     */
    public function getImageUrl()
    {
        return $this->getCurrentImage()->getUrl();
    }

    /**
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
        $file = $this->getCurrentImage()->getPath();

        if ($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->isFile($file)) {
            $size = getimagesize($file);
            if (isset($size[0])) {
                if ($size[0] > 600) {
                    return 600;
                } else {
                    return $size[0];
                }
            }
        }

        return false;
    }

    /**
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
