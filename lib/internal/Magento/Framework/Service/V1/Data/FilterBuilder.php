<?php
/**
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

namespace Magento\Framework\Service\V1\Data;

/**
 * Builder for Filter Service Data Object.
 *
 * @method Filter create()
 */
class FilterBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Set field
     *
     * @param string $field
     * @return $this
     */
    public function setField($field)
    {
        $this->_data['field'] = $field;
        return $this;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->_data['value'] = $value;
        return $this;
    }

    /**
     * Set condition type
     *
     * @param string $conditionType
     * @return $this
     */
    public function setConditionType($conditionType)
    {
        $this->_data['condition_type'] = $conditionType;
        return $this;
    }
}
