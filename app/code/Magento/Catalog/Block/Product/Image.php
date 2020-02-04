<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * Product image block
 *
 * @api
 * @method string getImageUrl()
 * @method string getWidth()
 * @method string getHeight()
 * @method string getLabel()
 * @method float getRatio()
 * @method string getCustomAttributes()
 * @method string getClass()
 * @since 100.0.2
 */
class Image extends \Magento\Framework\View\Element\Template
{
    /**
     * @deprecated Property isn't used
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @deprecated Property isn't used
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @deprecated Property isn't used
     * @var array
     */
    protected $attributes = [];

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
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
