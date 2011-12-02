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
 * @package     Mage_Install
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Install localization block
 *
 * @category   Mage
 * @package    Mage_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Install_Block_Locale extends Mage_Install_Block_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('locale.phtml');
    }

    /**
     * Retrieve locale object
     *
     * @return Zend_Locale
     */
    public function getLocale()
    {
        $locale = $this->getData('locale');
        if (is_null($locale)) {
            $locale = Mage::app()->getLocale()->getLocale();
            $this->setData('locale', $locale);
        }
        return $locale;
    }

    /**
     * Retrieve locale data post url
     *
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getCurrentStep()->getNextUrl();
        //return $this->getUrl('*/*/localePost');
    }

    /**
     * Retrieve locale change url
     *
     * @return string
     */
    public function getChangeUrl()
    {
        return $this->getUrl('*/*/localeChange');
    }

    /**
     * Retrieve locale dropdown HTML
     *
     * @return string
     */
    public function getLocaleSelect()
    {
        $html = $this->getLayout()->createBlock('Mage_Core_Block_Html_Select')
            ->setName('config[locale]')
            ->setId('locale')
            ->setTitle(Mage::helper('Mage_Install_Helper_Data')->__('Locale'))
            ->setClass('required-entry')
            ->setValue($this->getLocale()->__toString())
            ->setOptions(Mage::app()->getLocale()->getTranslatedOptionLocales())
            ->getHtml();
        return $html;
    }

    /**
     * Retrieve timezone dropdown HTML
     *
     * @return string
     */
    public function getTimezoneSelect()
    {
        $html = $this->getLayout()->createBlock('Mage_Core_Block_Html_Select')
            ->setName('config[timezone]')
            ->setId('timezone')
            ->setTitle(Mage::helper('Mage_Install_Helper_Data')->__('Time Zone'))
            ->setClass('required-entry')
            ->setValue($this->getTimezone())
            ->setOptions(Mage::app()->getLocale()->getOptionTimezones())
            ->getHtml();
        return $html;
    }

    /**
     * Retrieve timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        $timezone = Mage::getSingleton('Mage_Install_Model_Session')->getTimezone()
            ? Mage::getSingleton('Mage_Install_Model_Session')->getTimezone()
            : Mage::app()->getLocale()->getTimezone();
        if ($timezone == Mage_Core_Model_Locale::DEFAULT_TIMEZONE) {
            $timezone = 'America/Los_Angeles';
        }
        return $timezone;
    }

    /**
     * Retrieve currency dropdown html
     *
     * @return string
     */
    public function getCurrencySelect()
    {
        $html = $this->getLayout()->createBlock('Mage_Core_Block_Html_Select')
            ->setName('config[currency]')
            ->setId('currency')
            ->setTitle(Mage::helper('Mage_Install_Helper_Data')->__('Default Currency'))
            ->setClass('required-entry')
            ->setValue($this->getCurrency())
            ->setOptions(Mage::app()->getLocale()->getOptionCurrencies())
            ->getHtml();
        return $html;
    }

    /**
     * Retrieve currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return Mage::getSingleton('Mage_Install_Model_Session')->getCurrency()
            ? Mage::getSingleton('Mage_Install_Model_Session')->getCurrency()
            : Mage::app()->getLocale()->getCurrency();
    }

    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (is_null($data)) {
            $data = new Varien_Object();
            $this->setData('form_data', $data);
        }
        return $data;
    }

}
