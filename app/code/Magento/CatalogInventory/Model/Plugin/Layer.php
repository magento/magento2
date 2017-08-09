<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

/**
 * Class \Magento\CatalogInventory\Model\Plugin\Layer
 *
 */
class Layer
{
    /**
     * Stock status instance
     *
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * Store config instance
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
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
     */
    protected function _isEnabledShowOutOfStock()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
