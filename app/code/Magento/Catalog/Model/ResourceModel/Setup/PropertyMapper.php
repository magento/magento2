<?php
/**
 * Catalog attribute property mapper
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Setup;

use Magento\Eav\Model\Entity\Setup\PropertyMapperAbstract;

class PropertyMapper extends PropertyMapperAbstract
{
    /**
     * Map input attribute properties to storage representation
     *
     * @param array $input
     * @param int $entityTypeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map(array $input, $entityTypeId)
    {
        return [
            'frontend_input_renderer' => $this->_getValue($input, 'input_renderer'),
            'is_global' => $this->_getValue(
                $input,
                'global',
                \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
            ),
            'is_visible' => $this->_getValue($input, 'visible', 1),
            'is_searchable' => $this->_getValue($input, 'searchable', 0),
            'is_filterable' => $this->_getValue($input, 'filterable', 0),
            'is_comparable' => $this->_getValue($input, 'comparable', 0),
            'is_visible_on_front' => $this->_getValue($input, 'visible_on_front', 0),
            'is_wysiwyg_enabled' => $this->_getValue($input, 'wysiwyg_enabled', 0),
            'is_html_allowed_on_front' => $this->_getValue($input, 'is_html_allowed_on_front', 0),
            'is_visible_in_advanced_search' => $this->_getValue($input, 'visible_in_advanced_search', 0),
            'is_filterable_in_search' => $this->_getValue($input, 'filterable_in_search', 0),
            'used_in_product_listing' => $this->_getValue($input, 'used_in_product_listing', 0),
            'used_for_sort_by' => $this->_getValue($input, 'used_for_sort_by', 0),
            'apply_to' => $this->_getValue($input, 'apply_to'),
            'position' => $this->_getValue($input, 'position', 0),
            'is_used_for_promo_rules' => $this->_getValue($input, 'used_for_promo_rules', 0),
            'is_used_in_grid' => $this->_getValue($input, 'is_used_in_grid', 0),
            'is_visible_in_grid' => $this->_getValue($input, 'is_visible_in_grid', 0),
            'is_filterable_in_grid' => $this->_getValue($input, 'is_filterable_in_grid', 0),
        ];
    }
}
