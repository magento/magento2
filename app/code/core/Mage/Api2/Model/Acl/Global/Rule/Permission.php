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
 * @category    Mage
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Permission source model
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Acl_Global_Rule_Permission
{
    /**#@+
     * Source keys
     */
    const TYPE_ALLOW = 1;
    const TYPE_DENY  = 0;
    /**#@-*/

    /**
     * Get options parameters
     *
     * @return array
     */
    static public function toOptionArray()
    {
        return array(
            array(
                'value' => self::TYPE_DENY,
                'label' => Mage::helper('Mage_Api2_Helper_Data')->__('Deny')
            ),
            array(
                'value' => self::TYPE_ALLOW,
                'label' => Mage::helper('Mage_Api2_Helper_Data')->__('Allow')
            ),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    static public function toArray()
    {
        return array(
            self::TYPE_DENY  => Mage::helper('Mage_Api2_Helper_Data')->__('Deny'),
            self::TYPE_ALLOW => Mage::helper('Mage_Api2_Helper_Data')->__('Allow'),
        );
    }
}
