<?php
/**
 * Customer attribute property mapper
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Setup;

use Magento\Eav\Model\Entity\Setup\PropertyMapperAbstract;

/**
 * Class \Magento\Customer\Model\ResourceModel\Setup\PropertyMapper
 *
 * @since 2.0.0
 */
class PropertyMapper extends PropertyMapperAbstract
{
    /**
     * Map input attribute properties to storage representation
     *
     * @param array $input
     * @param int $entityTypeId
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function map(array $input, $entityTypeId)
    {
        return [
            'is_visible' => $this->_getValue($input, 'visible', 1),
            'is_system' => $this->_getValue($input, 'system', 1),
            'input_filter' => $this->_getValue($input, 'input_filter', null),
            'multiline_count' => $this->_getValue($input, 'multiline_count', 0),
            'validate_rules' => $this->_getValue($input, 'validate_rules', null),
            'data_model' => $this->_getValue($input, 'data', null),
            'sort_order' => $this->_getValue($input, 'position', 0),
            'is_used_in_grid' => $this->_getValue($input, 'is_used_in_grid', 0),
            'is_visible_in_grid' => $this->_getValue($input, 'is_visible_in_grid', 0),
            'is_filterable_in_grid' => $this->_getValue($input, 'is_filterable_in_grid', 0),
            'is_searchable_in_grid' => $this->_getValue($input, 'is_searchable_in_grid', 0),
        ];
    }
}
