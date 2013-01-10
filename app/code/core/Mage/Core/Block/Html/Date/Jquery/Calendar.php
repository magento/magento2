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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * HTML calendar element block implemented using the jQuery datepicker widget.
 *
 * @category   Mage
 * @package    Mage_Core
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Block_Html_Date_Jquery_Calendar extends Mage_Core_Block_Html_Date
{
    /**
     * File path for the regional localized Javascript file.
     */
    const LOCALIZED_URL_PATH = 'jquery/ui/i18n/jquery.ui.datepicker-%s.js';

    /**
     * Return the path to the localized Javascript file given the locale or null if it doesn't exist.
     *
     * @param string $locale - The locale (e.g. en-US or just en)
     * @return string - Url path to the localized Javascript file
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function _getUrlPathByLocale($locale)
    {
        $urlPath = sprintf(self::LOCALIZED_URL_PATH, $locale);
        try {
            $urlPath = $this->getViewFileUrl($urlPath);
        } catch (Magento_Exception $e) {
            $urlPath = null;
        }
        return $urlPath;
    }

    /**
     * Generate HTML containing a Javascript <script> tag for creating a calendar instance implemented
     * using the jQuery datepicker.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $displayFormat = Magento_Date_Jquery_Calendar::convertToDateTimeFormat(
            $this->getFormat(), true, (bool)$this->getTime()
        );

        $html = '<input type="text" name="' . $this->getName() . '" id="' . $this->getId() . '" ';
        $html .= 'value="' . $this->escapeHtml($this->getValue()) . '" class="' . $this->getClass() . '" '
            . $this->getExtraParams() . '/> ';

        $yearRange = "c-10:c+10"; /* Default the range to the current year + or - 10 years. */
        $calendarYearsRange = $this->getYearsRange();
        if ($calendarYearsRange) {
            /* Convert to the year range format that the jQuery datepicker understands. */
            sscanf($calendarYearsRange, "[%[0-9], %[0-9]]", $yearStart, $yearEnd);
            $yearRange = "$yearStart:$yearEnd";
        }

        /* First include jquery-ui. */
        $jsFiles = '"' . $this->getViewFileUrl("jquery/jquery-ui.min.js") . '", ';

        /* There are a small handful of localized files that use the 5 character locale. */
        $locale = str_replace('_', '-', Mage::app()->getLocale()->getLocaleCode());
        $localizedJsFilePath = $this->_getUrlPathByLocale($locale);

        if ($localizedJsFilePath == null) {
            /* Most localized files use the 2 character locale. */
            $locale = substr($locale, 0, 2);
            $localizedJsFilePath = $this->_getUrlPathByLocale($locale);
            if ($localizedJsFilePath == null) {
                /* Localized Javascript file doesn't exist. Default locale to empty string (English). */
                $locale = '';
            } else {
                /* Include the regional localized Javascript file. */
                $jsFiles .= '"' . $localizedJsFilePath . '", ';
            }
        } else {
            /* Include the regional localized Javascript file. */
            $jsFiles .= '"' . $localizedJsFilePath . '", ';
        }

        $jsFiles .= '"' . $this->getViewFileUrl("mage/calendar/calendar.js") . '"'; /* Lastly, the datepicker. */
        $cssFile = '"' . $this->getViewFileUrl("mage/calendar/css/calendar.css") . '"';

        $html
            .= '
            <script type="text/javascript">
                //<![CDATA[
                (function($) {
                    $.mage.event.observe("mage.calendar.initialize", function (event, initData) {
                        var datepicker = {
                            inputSelector: "#' . $this->getId() . '",
                            locale: "' . $locale . '",
                            options: {
                                buttonImage: "' . $this->getImage() . '",
                                buttonText: "' . $this->helper("Mage_Core_Helper_Data")->__("Select Date") . '",
                                dateFormat: "' . $displayFormat . '",
                                yearRange: "' . $yearRange . '"
                            }
                        };
                        initData.datepicker.push(datepicker);
                    });
                    $.mage.load.css( ' . $cssFile . ' );
                    $.mage.load.jsSync(' . $jsFiles . ');
                })(jQuery);
                //]]>
            </script>';

        return $html;
    }
}
