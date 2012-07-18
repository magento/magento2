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
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Source model of import/export format versions
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_ImportExport_Model_Source_Format_Version
{
    /**#@+
     * Import versions
     */
    const VERSION_1 = 1;
    const VERSION_2 = 2;
    /**#@-*/

    /**
     * Prepare and return array of available version file formats
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array(array(
            'label' => Mage::helper('Mage_ImportExport_Helper_Data')->__('-- Please Select --'),
            'value' => ''
        ));

        $options = $this->toArray();
        if (is_array($options) && count($options) > 0) {
            foreach ($options as $value => $label) {
                $optionArray[] = array(
                    'label' => $label,
                    'value' => $value
                );
            }
        }

        return $optionArray;
    }

    /**
     * Get possible format versions
     *
     * @return array
     */
    public function toArray()
    {
        $helper = Mage::helper('Mage_ImportExport_Helper_Data');

        return array(
            self::VERSION_1 => $helper->__('Magento 1.7 format'),
            self::VERSION_2 => $helper->__('Magento 2.0 format'),
        );
    }
}
