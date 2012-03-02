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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Calendar block for page header
 * Prepares localization data for calendar
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Block_Html_Calendar extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        $localeCode = Mage::app()->getLocale()->getLocaleCode();

        // get days names
        $days = Zend_Locale_Data::getList($localeCode, 'days');
        $helper = Mage::helper('Mage_Core_Helper_Data');
        $this->assign('days', array(
            'wide'        => $helper->jsonEncode(array_values($days['format']['wide'])),
            'abbreviated' => $helper->jsonEncode(array_values($days['format']['abbreviated']))
        ));

        // get months names
        $months = Zend_Locale_Data::getList($localeCode, 'months');
        $this->assign('months', array(
            'wide'        => $helper->jsonEncode(array_values($months['format']['wide'])),
            'abbreviated' => $helper->jsonEncode(array_values($months['format']['abbreviated']))
        ));

        // get "today" and "week" words
        $this->assign('today', $helper->jsonEncode(Zend_Locale_Data::getContent($localeCode, 'relative', 0)));
        $this->assign('week', $helper->jsonEncode(Zend_Locale_Data::getContent($localeCode, 'field', 'week')));

        // get "am" & "pm" words
        $this->assign('am', $helper->jsonEncode(Zend_Locale_Data::getContent($localeCode, 'am')));
        $this->assign('pm', $helper->jsonEncode(Zend_Locale_Data::getContent($localeCode, 'pm')));

        // get first day of week and weekend days
        $this->assign('firstDay',    (int)Mage::getStoreConfig('general/locale/firstday'));
        $this->assign('weekendDays', $helper->jsonEncode((string)Mage::getStoreConfig('general/locale/weekend')));

        // define default format and tooltip format
        $this->assign(
            'defaultFormat',
            $helper->jsonEncode(Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM))
        );
        $this->assign(
            'toolTipFormat',
            $helper->jsonEncode(Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_LONG))
        );

        // get days and months for en_US locale - calendar will parse exactly in this locale
        $days = Zend_Locale_Data::getList('en_US', 'days');
        $months = Zend_Locale_Data::getList('en_US', 'months');
        $enUS = new stdClass();
        $enUS->m = new stdClass();
        $enUS->m->wide = array_values($months['format']['wide']);
        $enUS->m->abbr = array_values($months['format']['abbreviated']);
        $this->assign('enUS', $helper->jsonEncode($enUS));

        return parent::_toHtml();
    }

    /**
     * Return offset of current timezone with GMT in seconds
     *
     * @return integer
     */
    public function getTimezoneOffsetSeconds()
    {
        return Mage::getSingleton('Mage_Core_Model_Date')->getGmtOffset();
    }

    /**
     * Getter for store timestamp based on store timezone settings
     *
     * @param mixed $store
     * @return int
     */
    public function getStoreTimestamp($store = null)
    {
        return Mage::getSingleton('Mage_Core_Model_Locale')->storeTimeStamp($store);
    }
}
