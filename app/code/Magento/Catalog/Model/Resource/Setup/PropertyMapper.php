<?php
/**
 * Catalog attribute property mapper
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
namespace Magento\Catalog\Model\Resource\Setup;

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
        return array(
            'frontend_input_renderer' => $this->_getValue($input, 'input_renderer'),
            'is_global' => $this->_getValue(
                $input,
                'global',
                \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL
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
            'is_used_for_promo_rules' => $this->_getValue($input, 'used_for_promo_rules', 0)
        );
    }
}
