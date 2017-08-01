<?php
/**
 * ID column renderer, also contains image URL in hidden field
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Product\Configurable\AssociatedSelector\Renderer;

/**
 * @api
 * @since 2.0.0
 */
class Id extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Catalog\Helper\Product
     * @since 2.0.0
     */
    protected $_productHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        array $data = []
    ) {
        $this->_productHelper = $productHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render grid row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $imageUrl = $row->getImage() && $row->getImage() != 'no_selection' ? $this->escapeHtml(
            $this->_productHelper->getImageUrl($row)
        ) : '';
        return $this->_getValue($row) . '<input type="hidden" data-role="image-url" value="' . $imageUrl . '"/>';
    }
}
