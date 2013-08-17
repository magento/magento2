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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Eav_Model_Entity_Attribute_Backend_Datetime extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * Formatting date value before save
     *
     * Should set (bool, string) correct type for empty value from html form,
     * necessary for further process, else date string
     *
     * @param Varien_Object $object
     * @throws Mage_Eav_Exception
     * @return Mage_Eav_Model_Entity_Attribute_Backend_Datetime
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();
        $_formated     = $object->getData($attributeName . '_is_formated');
        if (!$_formated && $object->hasData($attributeName)) {
            try {
                $value = $this->formatDate($object->getData($attributeName));
            } catch (Exception $e) {
                throw Mage::exception('Mage_Eav', Mage::helper('Mage_Eav_Helper_Data')->__('Invalid date'));
            }

            if (is_null($value)) {
                $value = $object->getData($attributeName);
            }

            $object->setData($attributeName, $value);
            $object->setData($attributeName . '_is_formated', true);
        }

        return $this;
    }

    /**
     * Prepare date for save in DB
     *
     * string format used from input fields (all date input fields need apply locale settings)
     * int value can be declared in code (this meen whot we use valid date)
     *
     * @param   string | int $date
     * @return  string
     */
    public function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }
        // unix timestamp given - simply instantiate date object
        if (preg_match('/^[0-9]+$/', $date)) {
            $date = new Zend_Date((int)$date);
        }
        // international format
        else if (preg_match('#^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$#', $date)) {
            $zendDate = new Zend_Date();
            $date = $zendDate->setIso($date);
        }
        // parse this date in current locale, do not apply GMT offset
        else {
            $date = Mage::app()->getLocale()->date($date,
               Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_LocaleInterface::FORMAT_TYPE_SHORT),
               null, false
            );
        }
        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }
}
