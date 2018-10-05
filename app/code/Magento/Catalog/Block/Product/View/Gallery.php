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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getGalleryOptionsJson()
    {
        $optionItems = null;

        if ($this->getVar("gallery/nav")) {
            $optionItems['nav'] = $this->escapeHtml($this->getVar("gallery/nav"));
        }
        if ($this->getVar("gallery/loop")) {
            $optionItems['loop'] = filter_var($this->getVar("gallery/loop"), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->getVar("gallery/keyboard")) {
            $optionItems['keyboard'] = filter_var($this->getVar("gallery/keyboard"), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->getVar("gallery/arrows")) {
            $optionItems['arrows'] = filter_var($this->getVar("gallery/arrows"), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->getVar("gallery/caption")) {
            $optionItems['showCaption'] = filter_var($this->getVar("gallery/caption"), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->getVar("gallery/allowfullscreen")) {
            $optionItems['allowfullscreen'] = filter_var(
                $this->getVar("gallery/allowfullscreen"),
                FILTER_VALIDATE_BOOLEAN
            );
        }
        if ($this->getVar("gallery/navdir")) {
            $optionItems['navdir'] = $this->escapeHtml($this->getVar("gallery/navdir"));
        }
        if ($this->getVar("gallery/navarrows")) {
            $optionItems['navarrows'] = filter_var($this->getVar("gallery/navarrows"), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->getVar("gallery/navtype")) {
            $optionItems['navtype'] = $this->escapeHtml($this->getVar("gallery/navtype"));
        }
        if ($this->getVar("gallery/thumbmargin")) {
            $optionItems['thumbmargin'] = filter_var($this->getVar("gallery/thumbmargin"), FILTER_VALIDATE_INT);
        }
        if ($this->getVar("gallery/transition/effect")) {
            $optionItems['transition'] = $this->escapeHtml($this->getVar("gallery/transition/effect"));
        }
        if ($this->getVar("gallery/transition/duration")) {
            $optionItems['transitionduration'] = filter_var(
                $this->getVar("gallery/transition/duration"),
                FILTER_VALIDATE_INT
            );
        }

        $optionItems['width'] = filter_var(
            $this->getImageAttribute('product_page_image_medium', 'width'),
            FILTER_VALIDATE_INT
        );
        $optionItems['thumbwidth'] = filter_var(
            $this->getImageAttribute('product_page_image_small', 'width'),
            FILTER_VALIDATE_INT
        );
        $imgHeight = $this->getImageAttribute('product_page_image_medium', 'height')
            ?: $this->getImageAttribute('product_page_image_medium', 'width');
        if ($imgHeight) {
            $optionItems['height'] = filter_var($imgHeight, FILTER_VALIDATE_INT);
        }
        $thumbHeight = $this->getImageAttribute('product_page_image_small', 'height')
            ?: $this->getImageAttribute('product_page_image_small', 'width');
        if ($thumbHeight) {
            $optionItems['thumbheight'] = filter_var($thumbHeight, FILTER_VALIDATE_INT);
        }

        return json_encode($optionItems);
    }

    /**
     * Retrieve gallery fullscreen options in JSON format
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getGalleryFSOptionsJson()
    {
        $fsOptionItems = null;

        if ($this->getVar("gallery/fullscreen/nav")) {
            $fsOptionItems['nav'] = $this->escapeHtml($this->getVar("gallery/fullscreen/nav"));
        }
        if ($this->getVar("gallery/fullscreen/loop")) {
            $fsOptionItems['loop'] = filter_var($this->getVar("gallery/fullscreen/loop"), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->getVar("gallery/fullscreen/keyboard")) {
            $fsOptionItems['keyboard'] = filter_var(
                $this->getVar("gallery/fullscreen/keyboard"),
                FILTER_VALIDATE_BOOLEAN
            );
        }
        if ($this->getVar("gallery/fullscreen/arrows")) {
            $fsOptionItems['arrows'] = filter_var($this->getVar("gallery/fullscreen/arrows"), FILTER_VALIDATE_BOOLEAN);
        }
        if ($this->getVar("gallery/fullscreen/caption")) {
            $fsOptionItems['showCaption'] = filter_var(
                $this->getVar("gallery/fullscreen/caption"),
                FILTER_VALIDATE_BOOLEAN
            );
        }
        if ($this->getVar("gallery/fullscreen/navdir")) {
            $fsOptionItems['navdir'] = $this->escapeHtml($this->getVar("gallery/fullscreen/navdir"));
        }
        if ($this->getVar("gallery/fullscreen/navarrows")) {
            $fsOptionItems['navarrows'] = filter_var(
                $this->getVar("gallery/fullscreen/navarrows"),
                FILTER_VALIDATE_BOOLEAN
            );
        }
        if ($this->getVar("gallery/fullscreen/navtype")) {
            $fsOptionItems['navtype'] = $this->escapeHtml($this->getVar("gallery/fullscreen/navtype"));
        }
        if ($this->getVar("gallery/fullscreen/thumbmargin")) {
            $fsOptionItems['thumbmargin'] = filter_var(
                $this->getVar("gallery/fullscreen/thumbmargin"),
                FILTER_VALIDATE_INT
            );
        }
        if ($this->getVar("gallery/fullscreen/transition/effect")) {
            $fsOptionItems['transition'] = $this->escapeHtml($this->getVar("gallery/fullscreen/transition/effect"));
        }
        if ($this->getVar("gallery/fullscreen/transition/duration")) {
            $fsOptionItems['transitionduration'] = filter_var(
                $this->getVar("gallery/fullscreen/transition/duration"),
                FILTER_VALIDATE_INT
            );
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
