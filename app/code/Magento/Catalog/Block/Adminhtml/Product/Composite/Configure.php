<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Composite;

/**
 * Adminhtml catalog product composite configure block
 *
 * @api
 * @since 2.0.0
 */
class Configure extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected $_product;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'catalog/product/composite/configure.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected $_catalogProduct;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_catalogProduct = $product;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve product object
     *
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        if (!$this->_product) {
            if ($this->_coreRegistry->registry('current_product')) {
                $this->_product = $this->_coreRegistry->registry('current_product');
            } else {
                $this->_product = $this->_catalogProduct;
            }
        }
        return $this->_product;
    }

    /**
     * Set product object
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     * @since 2.0.0
     */
    public function setProduct(\Magento\Catalog\Model\Product $product = null)
    {
        $this->_product = $product;
        return $this;
    }
}
