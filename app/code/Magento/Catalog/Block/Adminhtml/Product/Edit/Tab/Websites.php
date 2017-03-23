<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Stores tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab;

class Websites extends \Magento\Backend\Block\Store\Switcher
{
    /**
     * @var string
     */
    protected $_storeFromHtml;

    /**
     * @var string
     */
    protected $_template = 'catalog/product/edit/websites.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\GroupFactory $storeGroupFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Store\Model\GroupFactory $storeGroupFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $websiteFactory, $storeGroupFactory, $storeFactory, $data);
    }

    /**
     * Retrieve edited product model instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get store ID of current product
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getProduct()->getStoreId();
    }

    /**
     * Get ID of current product
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * Retrieve array of website IDs of current product
     *
     * @return array
     */
    public function getWebsites()
    {
        return $this->getProduct()->getWebsiteIds();
    }

    /**
     * Returns whether product associated with website with $websiteId
     *
     * @param int $websiteId
     * @return bool
     */
    public function hasWebsite($websiteId)
    {
        return in_array($websiteId, $this->getProduct()->getWebsiteIds());
    }

    /**
     * Check websites block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct()->getWebsitesReadonly();
    }

    /**
     * Retrieve store name by its ID
     *
     * @param int $storeId
     * @return null|string
     */
    public function getStoreName($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getName();
    }

    /**
     * Get HTML of store chooser
     *
     * @param \Magento\Store\Model\Store $storeTo
     * @return string
     */
    public function getChooseFromStoreHtml($storeTo)
    {
        if (!$this->_storeFromHtml) {
            $this->_storeFromHtml = '<select ' .
                                         'class="admin__control-select" ' .
                                         'name="copy_to_stores[__store_identifier__]" ' .
                                         'disabled="disabled">';
            $this->_storeFromHtml .= '<option value="0">' . __('Default Values') . '</option>';
            foreach ($this->getWebsiteCollection() as $_website) {
                if (!$this->hasWebsite($_website->getId())) {
                    continue;
                }
                $optGroupLabel = $this->escapeHtml($_website->getName());
                $this->_storeFromHtml .= '<optgroup label="' . $optGroupLabel . '"></optgroup>';
                foreach ($this->getGroupCollection($_website) as $_group) {
                    $optGroupName = $this->escapeHtml($_group->getName());
                    $this->_storeFromHtml .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;' . $optGroupName . '">';
                    foreach ($this->getStoreCollection($_group) as $_store) {
                        $this->_storeFromHtml .= '<option value="' . $_store->getId() . '">&nbsp;&nbsp;&nbsp;&nbsp;';
                        $this->_storeFromHtml .= $this->escapeHtml($_store->getName()) . '</option>';
                    }
                }
                $this->_storeFromHtml .= '</optgroup>';
            }
            $this->_storeFromHtml .= '</select>';
        }
        return str_replace('__store_identifier__', $storeTo->getId(), $this->_storeFromHtml);
    }
}
