<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Block\Product;

/**
 * Product view price and stock alerts
 * @since 2.0.0
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_registry;

    /**
     * Helper instance
     *
     * @var \Magento\ProductAlert\Helper\Data
     * @since 2.0.0
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     * @since 2.0.0
     */
    protected $coreHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\ProductAlert\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\Helper\PostHelper $coreHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\ProductAlert\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Helper\PostHelper $coreHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->_helper = $helper;
        $this->coreHelper = $coreHelper;
    }

    /**
     * Retrieve currently edited product object
     *
     * @return \Magento\Catalog\Model\Product|boolean
     * @since 2.0.0
     */
    protected function getProduct()
    {
        $product = $this->_registry->registry('current_product');
        if ($product && $product->getId()) {
            return $product;
        }
        return false;
    }

    /**
     * Retrieve post action config
     *
     * @return string
     * @since 2.0.0
     */
    public function getPostAction()
    {
        return $this->coreHelper->getPostData($this->getSignupUrl());
    }
}
