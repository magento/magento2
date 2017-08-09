<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Product List Sortable allowed sortable attributes source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Config\Source;

/**
 * Class \Magento\Catalog\Model\Config\Source\ListSort
 *
 */
class ListSort implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig
     */
    public function __construct(\Magento\Catalog\Model\Config $catalogConfig)
    {
        $this->_catalogConfig = $catalogConfig;
    }

    /**
     * Retrieve option values array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => __('Position'), 'value' => 'position'];
        foreach ($this->_getCatalogConfig()->getAttributesUsedForSortBy() as $attribute) {
            $options[] = ['label' => __($attribute['frontend_label']), 'value' => $attribute['attribute_code']];
        }
        return $options;
    }

    /**
     * Retrieve Catalog Config Singleton
     *
     * @return \Magento\Catalog\Model\Config
     */
    protected function _getCatalogConfig()
    {
        return $this->_catalogConfig;
    }
}
