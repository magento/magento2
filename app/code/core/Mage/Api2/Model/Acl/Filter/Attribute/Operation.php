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
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Operation source model
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Acl_Filter_Attribute_Operation
{
    /**
     * Get options paramets
     *
     * @return array
     */
    static public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ,
                'label' => Mage::helper('Mage_Api2_Helper_Data')->__('Read')
            ),
            array(
                'value' => Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_WRITE,
                'label' => Mage::helper('Mage_Api2_Helper_Data')->__('Write')
            )
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
            Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ  => Mage::helper('Mage_Api2_Helper_Data')->__('Read'),
            Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_WRITE => Mage::helper('Mage_Api2_Helper_Data')->__('Write')
        );
    }
}
