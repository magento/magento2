<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

/**
 * Products mass update inventory tab
 *
 * @api
 * @since 100.0.2
 */
class Inventory extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Source\Backorders
     */
    protected $_backorders;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var array
     * @since 101.0.0
     */
    protected $disabledFields = [];

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Model\Source\Backorders $backorders
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Model\Source\Backorders $backorders,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        array $data = []
    ) {
        $this->_backorders = $backorders;
        $this->stockConfiguration = $stockConfiguration;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve Backorders Options
     *
     * @return array
     */
    public function getBackordersOption()
    {
        return $this->_backorders->toOptionArray();
    }

    /**
     * Retrieve field suffix
     *
     * @return string
     */
    public function getFieldSuffix()
    {
        return 'inventory';
    }

    /**
     * Retrieve current store id
     *
     * @return int
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function getStoreId()
    {
        return (int)$this->getRequest()->getParam('store');
    }

    /**
     * Get default config value
     *
     * @param string $field
     * @return string|null
     */
    public function getDefaultConfigValue($field)
    {
        return $this->stockConfiguration->getDefaultConfigValue($field);
    }

    /**
     * Tab settings
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Advanced Inventory');
    }

    /**
     * Return Tab title.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Advanced Inventory');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get availability status.
     *
     * @param string $fieldName
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 101.0.0
     */
    public function isAvailable($fieldName)
    {
        return true;
    }
}
