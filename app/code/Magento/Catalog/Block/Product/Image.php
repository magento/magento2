<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * @api
 * @method string getImageUrl()
 * @method string getWidth()
 * @method string getHeight()
 * @method string getLabel()
 * @method mixed getResizedImageWidth()
 * @method mixed getResizedImageHeight()
 * @method float getRatio()
 * @method string getCustomAttributes()
 * @since 2.0.0
 */
class Image extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Helper\Image
     * @since 2.0.0
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected $product;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $attributes = [];

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        if (isset($data['template'])) {
            $this->setTemplate($data['template']);
            unset($data['template']);
        }
        parent::__construct($context, $data);
    }
}
