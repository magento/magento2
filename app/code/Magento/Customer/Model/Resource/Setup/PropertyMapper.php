<?php
/**
 * Customer attribute property mapper
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
        return array(
            'is_visible' => $this->_getValue($input, 'visible', 1),
            'is_system' => $this->_getValue($input, 'system', 1),
            'input_filter' => $this->_getValue($input, 'input_filter', null),
            'multiline_count' => $this->_getValue($input, 'multiline_count', 0),
            'validate_rules' => $this->_getValue($input, 'validate_rules', null),
            'data_model' => $this->_getValue($input, 'data', null),
            'sort_order' => $this->_getValue($input, 'position', 0)
        );
    }
}
