<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\CustomerData;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;
use Magento\Checkout\CustomerData\DefaultItem;

/**
 * Class \Magento\GroupedProduct\CustomerData\GroupedItem
 *
 */
class GroupedItem extends DefaultItem
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $imageHelper,
            $msrpHelper,
            $urlBuilder,
            $configurationPool,
            $checkoutHelper
        );
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    protected function getProductForThumbnail()
    {
        /**
         * Show grouped product thumbnail if it must be always shown according to the related setting in system config
         * or if child product thumbnail is not available
         */
        $config = $this->_scopeConfig->getValue(
            \Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped::CONFIG_THUMBNAIL_SOURCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $product = $config == ThumbnailSource::OPTION_USE_PARENT_IMAGE ||
            (!$this->getProduct()->getThumbnail() || $this->getProduct()->getThumbnail() == 'no_selection')
            ? $this->getGroupedProduct()
            : $this->getProduct();
        return $product;
    }

    /**
     * Get item grouped product
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getGroupedProduct()
    {
        $option = $this->item->getOptionByCode('product_type');
        if ($option) {
            return $option->getProduct();
        }
        return $this->getProduct();
    }
}
