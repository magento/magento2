<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Config\Source;

/**
 * Catalog Search Product List Sortable allowed sortable attributes source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class ListSort implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @param \Magento\Catalog\Model\Config $catalogConfig
     */
    public function __construct(\Magento\Catalog\Model\Config $catalogConfig)
    {
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * Provide config options for dropdown
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $options[] = ['label' => __('Relevance'), 'value' => 'relevance'];
        foreach ($this->catalogConfig->getAttributesUsedForSortBy() as $attribute) {
            $options[] = ['label' => __($attribute['frontend_label']), 'value' => $attribute['attribute_code']];
        }
        return $options;
    }
}
