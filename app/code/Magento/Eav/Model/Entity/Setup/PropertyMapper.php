<?php
/**
 * Default entity attribute mapper
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Model\Entity\Setup;

use Magento\Catalog\Model\Resource\Eav\Attribute;

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
        return array(
            'backend_model' => $this->_getValue($input, 'backend'),
            'backend_type' => $this->_getValue($input, 'type', 'varchar'),
            'backend_table' => $this->_getValue($input, 'table'),
            'frontend_model' => $this->_getValue($input, 'frontend'),
            'frontend_input' => $this->_getValue($input, 'input', 'text'),
            'frontend_label' => $this->_getValue($input, 'label'),
            'frontend_class' => $this->_getValue($input, 'frontend_class'),
            'source_model' => $this->_getValue($input, 'source'),
            'is_required' => $this->_getValue($input, 'required', 1),
            'is_user_defined' => $this->_getValue($input, 'user_defined', 0),
            'default_value' => $this->_getValue($input, 'default'),
            'is_unique' => $this->_getValue($input, 'unique', 0),
            'note' => $this->_getValue($input, 'note'),
            'is_global' => $this->_getValue($input, 'global', Attribute::SCOPE_GLOBAL)
        );
    }
}
