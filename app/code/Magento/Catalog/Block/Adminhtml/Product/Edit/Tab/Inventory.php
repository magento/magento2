<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab;

use Magento\Framework\Api\SimpleDataObjectConverter;

/**
 * Product inventory data
 */
class Inventory extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/tab/inventory.phtml';

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\CatalogInventory\Model\Source\Stock
     */
    protected $stock;

    /**
     * @var \Magento\CatalogInventory\Model\Source\Backorders
     */
    protected $backorders;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Model\Source\Backorders $backorders
     * @param \Magento\CatalogInventory\Model\Source\Stock $stock
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Model\Source\Backorders $backorders,
        \Magento\CatalogInventory\Model\Source\Stock $stock,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        array $data = []
    ) {
        $this->stock = $stock;
        $this->backorders = $backorders;
        $this->moduleManager = $moduleManager;
        $this->coreRegistry = $coreRegistry;
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getBackordersOption()
    {
        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            return $this->backorders->toOptionArray();
        }

        return [];
    }

    /**
     * Retrieve stock option array
     *
     * @return array
     */
    public function getStockOption()
    {
        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            return $this->stock->toOptionArray();
        }

        return [];
    }

    /**
     * Return current product instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Retrieve Catalog Inventory  Stock Item Model
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem()
    {
        return $this->stockRegistry->getStockItem(
            $this->getProduct()->getId(),
            $this->getProduct()->getStore()->getWebsiteId()
        );
    }

    /**
     * @param string $field
     * @return string|null
     */
    public function getFieldValue($field)
    {
        $stockItem = $this->getStockItem();
        $value = null;
        if ($stockItem->getItemId()) {
            $method = 'get' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($field);
            if (is_callable([$stockItem, $method])) {
                $value = $stockItem->{$method}();
            }
        }
        return $value === null ? $this->stockConfiguration->getDefaultConfigValue($field) : $value;
    }

    /**
     * @param string $field
     * @return string|null
     */
    public function getConfigFieldValue($field)
    {
        $stockItem = $this->getStockItem();
        if ($stockItem->getItemId()) {
            $method = 'getUseConfig' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase(
                $field
            );
            if (method_exists($stockItem, $method)) {
                return $stockItem->{$method}();
            }
        }
        return $this->stockConfiguration->getDefaultConfigValue($field);
    }

    /**
     * @param string $field
     * @return string|null
     */
    public function getDefaultConfigValue($field)
    {
        return $this->stockConfiguration->getDefaultConfigValue($field);
    }

    /**
     * Is readonly stock
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->getProduct()->getInventoryReadonly();
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        if ($this->getProduct()->getId()) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getFieldSuffix()
    {
        return 'product';
    }

    /**
     * Check Whether product type can have fractional quantity or not
     *
     * @return bool
     */
    public function canUseQtyDecimals()
    {
        return $this->getProduct()->getTypeInstance()->canUseQtyDecimals();
    }

    /**
     * Check if product type is virtual
     *
     * @return bool
     */
    public function isVirtual()
    {
        return $this->getProduct()->getIsVirtual();
    }

    /**
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
