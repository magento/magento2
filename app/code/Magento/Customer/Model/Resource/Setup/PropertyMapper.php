<?php
/**
 * Customer attribute property mapper
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Resource\Setup;

use Magento\Eav\Model\Entity\Setup\PropertyMapperAbstract;

class PropertyMapper extends PropertyMapperAbstract
{
    /**
     * Map input attribute properties to storage representation
     *
     * @param array $input
     * @param int $entityTypeId
     * @return array
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
            'sort_order' => $this->_getValue($input, 'position', 0)
        ];
    }
}
