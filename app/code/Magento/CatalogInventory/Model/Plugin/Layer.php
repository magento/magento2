<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

/**
 * Class \Magento\CatalogInventory\Model\Plugin\Layer
 *
 * @since 2.0.0
 */
class Layer
{
    /**
     * Stock status instance
     *
     * @var \Magento\CatalogInventory\Helper\Stock
     * @since 2.0.0
     */
    protected $stockHelper;

    /**
     * Store config instance
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->stockHelper = $stockHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Before prepare product collection handler
     *
     * @param \Magento\Catalog\Model\Layer $subject
     * @param \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function beforePrepareProductCollection(
        \Magento\Catalog\Model\Layer $subject,
        \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
    ) {
        if ($this->_isEnabledShowOutOfStock()) {
            return;
        }
        $this->stockHelper->addIsInStockFilterToCollection($collection);
    }

    /**
     * Get config value for 'display out of stock' option
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isEnabledShowOutOfStock()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
