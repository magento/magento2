<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product image controls model
 */
namespace Magento\Catalog\Model\Product\Image;

class View extends \Magento\Framework\Object
{
    /**
     * Separator between location and suffix
     */
    const VAR_NAME_SEPARATOR = ':';

    /**
     * Location suffix for image type
     */
    const SUFFIX_TYPE = 'type';

    /**
     * Location suffix for image width
     */
    const SUFFIX_WIDTH = 'width';

    /**
     * Location suffix for image height
     */
    const SUFFIX_HEIGHT = 'height';

    /**
     * Name control var for flag whether white image borders enable
     */
    const WHITE_BORDERS = 'product_image_white_borders';

    /**
     * Module for control var for flag whether white image borders enable
     */
    const WHITE_BORDERS_MODULE = 'Magento_Catalog';

    /**
     * @var \Magento\Framework\Config\View
     */
    protected $_configView;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_helperImage;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var string
     */
    protected $_location;

    /**
     * @var string
     */
    protected $_module;

    /**
     * View config model
     *
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $_viewConfig;

    /**
     * @param \Magento\Catalog\Helper\Image $helperImage
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $helperImage,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        array $data = []
    ) {
        $this->_helperImage = $helperImage;
        $this->_viewConfig = $viewConfig;
        parent::__construct($data);
    }

    /**
     * Initialize block
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $location
     * @param string $module
     * @return $this
     */
    public function init(\Magento\Catalog\Model\Product $product, $location, $module = null)
    {
        $this->_product = $product;
        $this->_location = $location;
        $this->_module = $module;
        return $this;
    }

    /**
     * Return product image url
     *
     * @return string
     */
    public function getUrl()
    {
        $this->_helperImage->init(
            $this->_product,
            $this->getType()
        )->keepFrame(
            $this->isWhiteBorders()
        )->constrainOnly(
            true
        )->resize(
            $this->getWidth(),
            $this->getHeight()
        );
        return (string)$this->_helperImage;
    }

    /**
     * Return product image label
     *
     * @return string
     */
    public function getLabel()
    {
        $label = $this->_product->getData($this->getType() . '_' . 'label');
        if (empty($label)) {
            $label = $this->_product->getName();
        }
        return $label;
    }

    /**
     * Whether white borders present
     *
     * @return bool
     */
    public function isWhiteBorders()
    {
        return (bool)$this->_getConfigView()->getVarValue(self::WHITE_BORDERS_MODULE, self::WHITE_BORDERS);
    }

    /**
     * Return product image type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_getImageVar(self::SUFFIX_TYPE);
    }

    /**
     * Return product image width
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->_getImageVar(self::SUFFIX_WIDTH);
    }

    /**
     * Return product image height
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->_getImageVar(self::SUFFIX_HEIGHT) ?: $this->getWidth();
    }

    /**
     * Get view config object
     *
     * @return \Magento\Framework\Config\View
     */
    protected function _getConfigView()
    {
        if (null === $this->_configView) {
            $this->_configView = $this->_viewConfig->getViewConfig();
        }
        return $this->_configView;
    }

    /**
     * Getter config view config var by suffix
     *
     * @param string $suffix
     * @return string|false
     */
    protected function _getImageVar($suffix)
    {
        return $this->_getConfigView()->getVarValue(
            $this->_module,
            $this->_location . self::VAR_NAME_SEPARATOR . $suffix
        );
    }
}
