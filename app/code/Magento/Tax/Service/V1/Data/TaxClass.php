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

namespace Magento\Tax\Service\V1\Data;

/**
 * Tax class data
 */
class TaxClass extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     *
     * Tax class field key.
     */
    const KEY_ID = 'class_id';
    const KEY_NAME = 'class_name';
    const KEY_TYPE = 'class_type';
    /**#@-*/

    /**
     * Get tax class ID.
     *
     * @return int|null
     */
    public function getClassId()
    {
        return $this->_get(self::KEY_ID);
    }

    /**
     * Get tax class name.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->_get(self::KEY_NAME);
    }

    /**
     * Get tax class type.
     *
     * @return string
     */
    public function getClassType()
    {
        return $this->_get(self::KEY_TYPE);
    }
}
