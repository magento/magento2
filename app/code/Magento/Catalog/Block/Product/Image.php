<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

class Image extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        if (!$this->_template) {
            $this->_template = $this->imageHelper->getFrame()
                ? 'Magento_Catalog::product/image.phtml'
                : 'Magento_Catalog::product/image_with_borders.phtml';
        }
        return $this->_template;
    }

    /**
     * Retrieve image URL
     *
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageHelper->getUrl();
    }

    /**
     * Retrieve image width
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->imageHelper->getWidth();
    }

    /**
     * Retrieve image height
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->imageHelper->getHeight();
    }

    /**
     * Retrieve image label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->imageHelper->getLabel();
    }

    /**
     * Retrieve width value for resized image
     *
     * @return string
     */
    public function getResizedImageWidth()
    {
        return $this->imageHelper->getResizedImageInfo()[0];
    }

    /**
     * Retrieve height value for resized image
     *
     * @return mixed
     */
    public function getResizedImageHeight()
    {
        return $this->imageHelper->getResizedImageInfo()[1];
    }

    /**
     * Retrieve image ratio
     *
     * @return float
     */
    public function getRatio()
    {
        return $this->imageHelper->getHeight() / $this->imageHelper->getWidth();
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @return string
     */
    public function getCustomAttributes()
    {
        $attributes = [];
        foreach ($this->attributes as $name => $value) {
            $attributes[] = $name . '="' . $value . '"';
        }
        return !empty($attributes) ? implode(' ', $attributes) : '';
    }

    /**
     * Set image custom attribute
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setAttribute($name, $value = null)
    {
        $this->attributes[$name] = $value;
        return $this;
    }
}
