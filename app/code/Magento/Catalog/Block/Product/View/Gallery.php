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

use Magento\Framework\Data\Collection;
use Magento\Framework\Json\EncoderInterface;
use Magento\Catalog\Helper\Image;

/**
 * @api
 * @since 100.0.2
 */
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
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $arrayUtils, $data);
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
                /* @var \Magento\Framework\DataObject $image */
                $image->setData(
                    'small_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_medium_no_frame')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_large_no_frame')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
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
        foreach ($this->getGalleryImages() as $image) {
            $imagesItems[] = [
                'thumb' => $image->getData('small_image_url'),
                'img' => $image->getData('medium_image_url'),
                'full' => $image->getData('large_image_url'),
                'caption' => ($image->getLabel() ?: $this->getProduct()->getName()),
                'position' => $image->getPosition(),
                'isMain' => $this->isMainImage($image),
                'type' => str_replace('external-', '', $image->getMediaType()),
                'videoUrl' => $image->getVideoUrl(),
            ];
        }
        if (empty($imagesItems)) {
            $imagesItems[] = [
                'thumb' => $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'img' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'full' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'caption' => '',
                'position' => '0',
                'isMain' => true,
                'type' => 'image',
                'videoUrl' => null,
            ];
        }
        return json_encode($imagesItems);
    }

    /**
     * Retrieve gallery options in JSON format
     *
     * @return string
     */
    public function getGalleryOptionsJson()
    {
        $optionItems = null;
        
        //Need to catch the special case that if gallery/nav is false, we need to output
        //the string "false", and not the boolean false. Otherwise output string.
        //True is not a valid option, but is left in incase someone sets it to true
        //by accident.
        if (is_bool($this->getVar("gallery/nav"))) {
            $optionItems['nav'] = $this->getVar("gallery/nav") ? 'true' : 'false';
        } elseif ($this->getVar("gallery/nav") != null) {
            $optionItems['nav'] = $this->getVar("gallery/nav");
        }
        
        if (is_bool($this->getVar("gallery/loop"))) {
            $optionItems['loop'] = $this->getVar("gallery/loop");
        }
        
        if (is_bool($this->getVar("gallery/keyboard"))) {
            $optionItems['keyboard'] = $this->getVar("gallery/keyboard");
        }
        
        if (is_bool($this->getVar("gallery/arrows"))) {
            $optionItems['arrows'] = $this->getVar("gallery/arrows");
        }
        
        if (is_bool($this->getVar("gallery/allowfullscreen"))) {
            $optionItems['allowfullscreen'] = $this->getVar("gallery/allowfullscreen");
        }
        
        if (is_bool($this->getVar("gallery/caption"))) {
            $optionItems['showCaption'] = $this->getVar("gallery/caption");
        }
        
        $optionItems['width'] = (int)$this->escapeHtml(
            $this->getImageAttribute('product_page_image_medium', 'width')
        );
        
        $optionItems['thumbwidth'] = (int)$this->escapeHtml(
            $this->getImageAttribute('product_page_image_small', 'width')
        );
        
        $imgHeight = $this->getImageAttribute('product_page_image_medium', 'height')
            ?: $this->getImageAttribute('product_page_image_medium', 'width');
        if ($imgHeight) {
            $optionItems['height'] = (int)$this->escapeHtml($imgHeight);
        }
        
        $thumbHeight = $this->getImageAttribute('product_page_image_small', 'height')
            ?: $this->getImageAttribute('product_page_image_small', 'width');
        if ($thumbHeight) {
            $optionItems['thumbheight'] = (int)$this->escapeHtml($thumbHeight);
        }
        
        if ($this->getVar("gallery/thumbmargin")) {
            $optionItems['thumbmargin'] = (int)$this->getVar("gallery/thumbmargin");
        }
        
        if ($this->getVar("gallery/transition/duration")) {
            $optionItems['transitionduration'] = (int)$this->getVar("gallery/transition/duration");
        }
        
        if ($this->getVar("gallery/transition/effect")) {
            $optionItems['transition'] = $this->getVar("gallery/transition/effect");
        }
        
        if (is_bool($this->getVar("gallery/navarrows"))) {
            $optionItems['navarrows'] = $this->getVar("gallery/navarrows");
        }
        
        if ($this->getVar("gallery/navtype")) {
            $optionItems['navtype'] = $this->getVar("gallery/navtype");
        }
        
        if ($this->getVar("gallery/navdir")) {
            $optionItems['navdir'] = $this->getVar("gallery/navdir");
        }
        
        return json_encode($optionItems);
    }

    /**
     * Retrieve gallery fullscreen options in JSON format
     *
     * @return string
     */
    public function getGalleryFSOptionsJson()
    {
        $fsOptionItems = null;
  
        //Need to catch the special case that if gallery/nav is false, we need to output
        //the string "false", and not the boolean false. Otherwise output string.
        //True is not a valid option, but is left in incase someone sets it to true
        //by accident.
        if (is_bool($this->getVar("gallery/fullscreen/nav"))) {
            $fsOptionItems['nav'] = $this->getVar("gallery/fullscreen/nav") ? 'true' : 'false';
        } elseif ($this->getVar("gallery/fullscreen/nav") != null) {
            $fsOptionItems['nav'] = $this->getVar("gallery/fullscreen/nav");
        }

        if (is_bool($this->getVar("gallery/fullscreen/loop"))) {
            $fsOptionItems['loop'] = $this->getVar("gallery/fullscreen/loop");
        }
        
        if ($this->getVar("gallery/fullscreen/navtype")) {
            $fsOptionItems['navtype'] = $this->getVar("gallery/fullscreen/navtype");
        }
        
        if ($this->getVar("gallery/fullscreen/navdir")) {
            $fsOptionItems['navdir'] = $this->getVar("gallery/fullscreen/navdir");
        }
        
        if (is_bool($this->getVar("gallery/fullscreen/arrows"))) {
            $fsOptionItems['arrows'] = $this->getVar("gallery/fullscreen/arrows");
        }
        
        if (is_bool($this->getVar("gallery/fullscreen/navarrows"))) {
            $fsOptionItems['navarrows'] = $this->getVar("gallery/fullscreen/navarrows");
        }
        
        if (is_bool($this->getVar("gallery/fullscreen/caption"))) {
            $fsOptionItems['showCaption'] = $this->getVar("gallery/fullscreen/caption");
        }
        
        if ($this->getVar("gallery/fullscreen/transition/duration")) {
            $fsOptionItems['transitionduration'] = (int)$this->getVar("gallery/fullscreen/transition/duration");
        }
        
        if ($this->getVar("gallery/fullscreen/transition/effect")) {
            $fsOptionItems['transition'] = $this->getVar("gallery/fullscreen/transition/effect");
        }
        
        return json_encode($fsOptionItems);
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
        return $attributes[$attributeName] ?? $default;
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
