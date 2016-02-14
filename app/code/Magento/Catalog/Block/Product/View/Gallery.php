<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Simple product data view
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product\View;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;

class Gallery extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var \Magento\Framework\Config\View
     */
    protected $configView;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var DataObject
     */
    protected $galleryImagesConfig;

    /**
     * @param \Magento\Catalog\Block\Product\Context                              $context
     * @param \Magento\Framework\Stdlib\ArrayUtils                                $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface                            $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Gallery\ImagesConfigFactoryInterface $imagesConfigFactory
     * @param array                                                               $galleryImagesConfig
     * @param array                                                               $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Gallery\ImagesConfigFactoryInterface $imagesConfigFactory,
        array $galleryImagesConfig = [],
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $arrayUtils, $data);
        $this->galleryImagesConfig = $imagesConfigFactory->create($galleryImagesConfig);
    }

    /**
     * Retrieve collection of gallery images
     *
     * @return Collection
     */
    public function getGalleryImages()
    {
        $product = $this->getProduct();
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                foreach($this->galleryImagesConfig->getItems() as $imageConfig) {
                    /** @var Product $product */
                    $image->setData(
                        $imageConfig->getData('data_object_key'),
                        $this->_imageHelper->init($product,
                            $imageConfig['image_id'])
                            ->setImageFile($image->getData('file'))
                            ->getUrl()
                    );
                }
            }
        }

        return $images;
    }

    /**
     * Return magnifier options
     *
     * @return string
     */
    public function getMagnifier()
    {
        return $this->jsonEncoder->encode($this->getVar('magnifier'));
    }

    /**
     * Return breakpoints options
     *
     * @return string
     */
    public function getBreakpoints()
    {
        return $this->jsonEncoder->encode($this->getVar('breakpoints'));
    }

    /**
     * Retrieve product images in JSON format
     *
     * @return string
     */
    public function getGalleryImagesJson()
    {
        $imagesItems = [];
        /** @var DataObject $image */
        foreach ($this->getGalleryImages() as $image) {
            $imageItem = new DataObject([
                'caption'  => $image->getData('label'),
                'position' => $image->getData('position'),
                'isMain'   => $this->isMainImage($image),
            ]);
            foreach($this->galleryImagesConfig->getItems() as $imageConfig) {
                $imageItem->setData(
                    $imageConfig->getData('json_object_key'),
                    $image->getData($imageConfig->getData('data_object_key'))
                );
            }
            $imagesItems[] = $imageItem->toArray();
        }
        if (empty($imagesItems)) {
            $imagesItems[] = [
                'thumb' => $this->getImage($this->getProduct(), 'product_thumbnail_image')->getImageUrl(),
                'img' => $this->getImage($this->getProduct(), 'product_base_image')->getImageUrl(),
                'full' => $this->getImage($this->getProduct(), 'product_page_image_large')->getImageUrl(),
                'caption' => '',
                'position' => '0',
                'isMain' => true,
            ];
        }
        return json_encode($imagesItems);
    }

    /**
     * Retrieve gallery url
     *
     * @param null|\Magento\Framework\DataObject $image
     * @return string
     */
    public function getGalleryUrl($image = null)
    {
        $params = ['id' => $this->getProduct()->getId()];
        if ($image) {
            $params['image'] = $image->getValueId();
        }
        return $this->getUrl('catalog/product/gallery', $params);
    }

    /**
     * Is product main image
     *
     * @param \Magento\Framework\DataObject $image
     * @return bool
     */
    public function isMainImage($image)
    {
        $product = $this->getProduct();
        return $product->getImage() == $image->getFile();
    }

    /**
     * @param string $imageId
     * @param string $attributeName
     * @param string $default
     * @return string
     */
    public function getImageAttribute($imageId, $attributeName, $default = null)
    {
        $attributes =
            $this->getConfigView()->getMediaAttributes('Magento_Catalog', Image::MEDIA_TYPE_CONFIG_NODE, $imageId);
        return isset($attributes[$attributeName]) ? $attributes[$attributeName] : $default;
    }

    /**
     * Retrieve config view
     *
     * @return \Magento\Framework\Config\View
     */
    private function getConfigView()
    {
        if (!$this->configView) {
            $this->configView = $this->_viewConfig->getViewConfig();
        }
        return $this->configView;
    }
}
