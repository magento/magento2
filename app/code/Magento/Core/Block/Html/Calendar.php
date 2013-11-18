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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Calendar block for page header
 * Prepares localization data for calendar
 *
 * @category   Magento
 * @package    Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Block\Html;

class Calendar extends \Magento\Core\Block\Template
{
    /**
     * Date model
     *
     * @var \Magento\Core\Model\Date
     */
    protected $_date;

    /**
     * Core locale
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Core\Model\Date $date
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Core\Model\Date $date,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_locale = $locale;
        $this->_date = $date;
        parent::__construct($coreData, $context, $data);
    }

    protected function _toHtml()
    {
        $localeCode = $this->_locale->getLocaleCode();

        // get days names
        $days = \Zend_Locale_Data::getList($localeCode, 'days');
        $helper = $this->_coreData;
        $this->assign('days', array(
            'wide'        => $helper->jsonEncode(array_values($days['format']['wide'])),
            'abbreviated' => $helper->jsonEncode(array_values($days['format']['abbreviated']))
        ));

        // get months names
        $months = \Zend_Locale_Data::getList($localeCode, 'months');
        $this->assign('months', array(
            'wide'        => $helper->jsonEncode(array_values($months['format']['wide'])),
            'abbreviated' => $helper->jsonEncode(array_values($months['format']['abbreviated']))
        ));

        // get "today" and "week" words
        $this->assign('today', $helper->jsonEncode(\Zend_Locale_Data::getContent($localeCode, 'relative', 0)));
        $this->assign('week', $helper->jsonEncode(\Zend_Locale_Data::getContent($localeCode, 'field', 'week')));

        // get "am" & "pm" words
        $this->assign('am', $helper->jsonEncode(\Zend_Locale_Data::getContent($localeCode, 'am')));
        $this->assign('pm', $helper->jsonEncode(\Zend_Locale_Data::getContent($localeCode, 'pm')));

        // get first day of week and weekend days
        $this->assign('firstDay',    (int)$this->_storeConfig->getConfig('general/locale/firstday'));
        $this->assign('weekendDays', $helper->jsonEncode((string)$this->_storeConfig->getConfig('general/locale/weekend')));

        // define default format and tooltip format
        $this->assign(
            'defaultFormat',
            $helper->jsonEncode($this->_locale->getDateFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_MEDIUM))
        );
        $this->assign(
            'toolTipFormat',
            $helper->jsonEncode($this->_locale->getDateFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_LONG))
        );

        // get days and months for en_US locale - calendar will parse exactly in this locale
        $days = \Zend_Locale_Data::getList('en_US', 'days');
        $months = \Zend_Locale_Data::getList('en_US', 'months');
        $enUS = new \stdClass();
        $enUS->m = new \stdClass();
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
        return $this->_date->getGmtOffset();
    }

    /**
     * Getter for store timestamp based on store timezone settings
     *
     * @param mixed $store
     * @return int
     */
    public function getStoreTimestamp($store = null)
    {
        return $this->_locale->storeTimeStamp($store);
    }
}
