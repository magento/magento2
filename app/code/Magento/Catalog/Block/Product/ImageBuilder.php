<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

use Magento\Catalog\Helper\ImageFactory as HelperFactory;

/**
 * Class \Magento\Catalog\Block\Product\ImageBuilder
 *
 * @since 2.0.0
 */
class ImageBuilder
{
    /**
     * @var ImageFactory
     * @since 2.0.0
     */
    protected $imageFactory;

    /**
     * @var HelperFactory
     * @since 2.0.0
     */
    protected $helperFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected $product;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $imageId;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $attributes = [];

    /**
     * @param HelperFactory $helperFactory
     * @param ImageFactory $imageFactory
     * @since 2.0.0
     */
    public function __construct(
        HelperFactory $helperFactory,
        ImageFactory $imageFactory
    ) {
        $this->helperFactory = $helperFactory;
        $this->imageFactory = $imageFactory;
    }

    /**
     * Set product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @since 2.0.0
     */
    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Set image ID
     *
     * @param string $imageId
     * @return $this
     * @since 2.0.0
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;
        return $this;
    }

    /**
     * Set custom attributes
     *
     * @param array $attributes
     * @return $this
     * @since 2.0.0
     */
    public function setAttributes(array $attributes)
    {
        if ($attributes) {
            $this->attributes = $attributes;
        }
        return $this;
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @return string
     * @since 2.0.0
     */
    protected function getCustomAttributes()
    {
        $result = [];
        foreach ($this->attributes as $name => $value) {
            $result[] = $name . '="' . $value . '"';
        }
        return !empty($result) ? implode(' ', $result) : '';
    }

    /**
     * Calculate image ratio
     *
     * @param \Magento\Catalog\Helper\Image $helper
     * @return float|int
     * @since 2.0.0
     */
    protected function getRatio(\Magento\Catalog\Helper\Image $helper)
    {
        $width = $helper->getWidth();
        $height = $helper->getHeight();
        if ($width && $height) {
            return $height / $width;
        }
        return 1;
    }

    /**
     * Create image block
     *
     * @return \Magento\Catalog\Block\Product\Image
     * @since 2.0.0
     */
    public function create()
    {
        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->helperFactory->create()
            ->init($this->product, $this->imageId);

        $template = $helper->getFrame()
            ? 'Magento_Catalog::product/image.phtml'
            : 'Magento_Catalog::product/image_with_borders.phtml';

        $imagesize = $helper->getResizedImageInfo();

        $data = [
            'data' => [
                'template' => $template,
                'image_url' => $helper->getUrl(),
                'width' => $helper->getWidth(),
                'height' => $helper->getHeight(),
                'label' => $helper->getLabel(),
                'ratio' =>  $this->getRatio($helper),
                'custom_attributes' => $this->getCustomAttributes(),
                'resized_image_width' => !empty($imagesize[0]) ? $imagesize[0] : $helper->getWidth(),
                'resized_image_height' => !empty($imagesize[1]) ? $imagesize[1] : $helper->getHeight(),
            ],
        ];

        return $this->imageFactory->create($data);
    }
}
