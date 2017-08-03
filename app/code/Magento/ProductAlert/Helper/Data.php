<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Helper;

use Magento\Store\Model\Store;

/**
 * ProductAlert data helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @api
 * @since 2.0.0
 */
class Data extends \Magento\Framework\Url\Helper\Data
{
    /**
     * Current product instance (override registry one)
     *
     * @var null|\Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected $_product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    protected $_layout;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    private $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get current product instance
     *
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        if ($this->_product !== null) {
            return $this->_product;
        }
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Set current product instance
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\ProductAlert\Helper\Data
     * @since 2.0.0
     */
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * @return Store
     * @since 2.0.0
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * @param string $type
     * @return string
     * @since 2.0.0
     */
    public function getSaveUrl($type)
    {
        return $this->_getUrl(
            'productalert/add/' . $type,
            [
                'product_id' => $this->getProduct()->getId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
            ]
        );
    }

    /**
     * Create block instance
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function createBlock($block)
    {
        if (is_string($block)) {
            if (class_exists($block)) {
                $block = $this->_layout->createBlock($block);
            }
        }
        if (!$block instanceof \Magento\Framework\View\Element\AbstractBlock) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid block type: %1', $block));
        }
        return $block;
    }

    /**
     * Check whether stock alert is allowed
     *
     * @return bool
     * @since 2.0.0
     */
    public function isStockAlertAllowed()
    {
        return $this->scopeConfig->isSetFlag(
            \Magento\ProductAlert\Model\Observer::XML_PATH_STOCK_ALLOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check whether price alert is allowed
     *
     * @return bool
     * @since 2.0.0
     */
    public function isPriceAlertAllowed()
    {
        return $this->scopeConfig->isSetFlag(
            \Magento\ProductAlert\Model\Observer::XML_PATH_PRICE_ALLOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
