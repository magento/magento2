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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * System config email field backend model
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Model_Config_Backend_Locale_Timezone extends Mage_Core_Model_Config_Data
{
    /**
     * Const for PHP 5.3+ compatibility
     * This value copied from DateTimeZone::ALL_WITH_BC in PHP 5.3+
     *
     * @constant ALL_WITH_BC
     */
    const ALL_WITH_BC = 4095;

    protected function _beforeSave()
    {
        $allWithBc = self::ALL_WITH_BC;
        if (defined('DateTimeZone::ALL_WITH_BC')) {
            $allWithBc = DateTimeZone::ALL_WITH_BC;
        }

        if (!in_array($this->getValue(), DateTimeZone::listIdentifiers($allWithBc))) {
            Mage::throwException(Mage::helper('Mage_Backend_Helper_Data')->__('Invalid timezone'));
        }

        return $this;
    }
}
