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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Date grid column filter
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 * @todo        date format
 */
class Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract
{
    protected $_locale;

    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setCanLoadCalendarJs(true);
        }
        return $this;
    }

    public function getHtml()
    {
        $htmlId = $this->_getHtmlId() . microtime(true);
        $format = $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $html = '<div class="range"><div class="range-line date">
            <span class="label">' . Mage::helper('Mage_Adminhtml_Helper_Data')->__('From').':</span>
            <input type="text" name="'.$this->_getHtmlName().'[from]" id="'.$htmlId.'_from" value="'.$this->getEscapedValue('from').'" class="input-text no-changes"/>
            <img src="' . Mage::getDesign()->getSkinUrl('images/grid-cal.gif') . '" alt="" class="v-middle" id="'.$htmlId.'_from_trig" title="'.$this->escapeHtml(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Date selector')).'"/>
            </div>';
        $html.= '<div class="range-line date">
            <span class="label">' . Mage::helper('Mage_Adminhtml_Helper_Data')->__('To').' :</span>
            <input type="text" name="'.$this->_getHtmlName().'[to]" id="'.$htmlId.'_to" value="'.$this->getEscapedValue('to').'" class="input-text no-changes"/>
            <img src="' . Mage::getDesign()->getSkinUrl('images/grid-cal.gif') . '" alt="" class="v-middle" id="'.$htmlId.'_to_trig" title="'.$this->escapeHtml(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Date selector')).'"/>
            </div></div>';
        $html.= '<input type="hidden" name="'.$this->_getHtmlName().'[locale]" value="'.$this->getLocale()->getLocaleCode().'"/>';
        $html.= '<script type="text/javascript">
            Calendar.setup({
                inputField : "'.$htmlId.'_from",
                ifFormat : "'.$format.'",
                button : "'.$htmlId.'_from_trig",
                align : "Bl",
                singleClick : true
            });
            Calendar.setup({
                inputField : "'.$htmlId.'_to",
                ifFormat : "'.$format.'",
                button : "'.$htmlId.'_to_trig",
                align : "Bl",
                singleClick : true
            });

            $("'.$htmlId.'_to_trig").observe("click", showCalendar);
            $("'.$htmlId.'_from_trig").observe("click", showCalendar);

            function showCalendar(event){
                var element = event.element(event);
                var offset = $(element).viewportOffset();
                var scrollOffset = $(element).cumulativeScrollOffset();
                var dimensionsButton = $(element).getDimensions();
                var index = $("widget-chooser").getStyle("zIndex");

                $$("div.calendar").each(function(item){
                    if ($(item).visible()) {
                        var dimensionsCalendar = $(item).getDimensions();

                        $(item).setStyle({
                            "zIndex" : index + 1,
                            "left" : offset[0] + scrollOffset[0] - dimensionsCalendar.width + dimensionsButton.width + "px",
                            "top" : offset[1] + scrollOffset[1] + dimensionsButton.height + "px"
                        });
                    };
                });
            };
        </script>';
        return $html;
    }

    public function getEscapedValue($index=null)
    {
        $value = $this->getValue($index);
        if ($value instanceof Zend_Date) {
            return $value->toString($this->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
        }
        return $value;
    }

    public function getValue($index=null)
    {
        if ($index) {
            if ($data = $this->getData('value', 'orig_' . $index)) {
                return $data;//date('Y-m-d', strtotime($data));
            }
            return null;
        }
        $value = $this->getData('value');
        if (is_array($value)) {
            $value['date'] = true;
        }
        return $value;
    }

    public function getCondition()
    {
        $value = $this->getValue();

        return $value;
    }

    public function setValue($value)
    {
        if (isset($value['locale'])) {
            if (!empty($value['from'])) {
                $value['orig_from'] = $value['from'];
                $value['from'] = $this->_convertDate($value['from'], $value['locale']);
            }
            if (!empty($value['to'])) {
                $value['orig_to'] = $value['to'];
                $value['to'] = $this->_convertDate($value['to'], $value['locale']);
            }
        }
        if (empty($value['from']) && empty($value['to'])) {
            $value = null;
        }
        $this->setData('value', $value);
        return $this;
    }

    /**
     * Retrieve locale
     *
     * @return Mage_Core_Model_Locale
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            $this->_locale = Mage::app()->getLocale();
        }
        return $this->_locale;
    }

    /**
     * Convert given date to default (UTC) timezone
     *
     * @param string $date
     * @param string $locale
     * @return Zend_Date
     */
    protected function _convertDate($date, $locale)
    {
        try {
            $dateObj = $this->getLocale()->date(null, null, $locale, false);

            //set default timezone for store (admin)
            $dateObj->setTimezone(Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));

            //set begining of day
            $dateObj->setHour(00);
            $dateObj->setMinute(00);
            $dateObj->setSecond(00);

            //set date with applying timezone of store
            $dateObj->set($date, Zend_Date::DATE_SHORT, $locale);

            //convert store date to default date in UTC timezone without DST
            $dateObj->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);

            return $dateObj;
        }
        catch (Exception $e) {
            return null;
        }
    }
}
