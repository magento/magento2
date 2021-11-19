<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\Html;

/**
 * Date element block
 */
class Date extends \Magento\Framework\View\Element\Template
{
    /**
     * Date format not supported function date().
     */
    private const DATE_FORMAT_NOT_SUPPORTED = [
        '%a', '%A', '%d', '%e', '%u', '%w', '%W', '%b', '%h', '%B', '%m', '%y', '%Y', '%D', '%F', '%x', '%n', '%t',
        '%H', '%k', '%I', '%l', '%M', '%p', '%P', '%r', '%R', '%S', '%T', '%X', '%z', '%Z', '%c', '%s', '%g', '%G', '%%'
    ];

    /**
     * Date format supported by function date().
     */
    private const DATE_FORMAT_SUPPORTED = [
        'D', 'l',  'd', 'j', 'N', 'w', 'W', 'M', 'M', 'F', 'm', 'y', 'Y', 'm/d/y', 'Y-m-d', 'm/d/y',"\n","\t", 'H', 'G',
        'h', 'g', 'i', 'A', 'a', 'h:i:s A', 'H:i', 's', 'H:i:s', 'H:i:s', 'O', 'T', 'D M j H:i:s Y', 'U', 'y', 'Y', '%'
    ];

    /**
     * Render block HTML
     *
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _toHtml()
    {
        $html = '<input type="text" name="' . $this->getName() . '" id="' . $this->getId() . '" ';
        $html .= 'value="' . $this->escapeHtml($this->getValue()) . '" ';
        $html .= 'class="' . $this->getClass() . '" ' . $this->getExtraParams() . '/> ';
        $calendarYearsRange = $this->getYearsRange();
        $changeMonth = $this->getChangeMonth();
        $changeYear = $this->getChangeYear();
        $maxDate = $this->getMaxDate();
        $showOn = $this->getShowOn();
        $firstDay = $this->getFirstDay();

        $html .= '<script type="text/javascript">
            require(["jquery", "mage/calendar"], function($){
                    $("#' .
            $this->getId() .
            '").calendar({
                        showsTime: ' .
            ($this->getTimeFormat() ? 'true' : 'false') .
            ',
                        ' .
            ($this->getTimeFormat() ? 'timeFormat: "' .
            $this->getTimeFormat() .
            '",' : '') .
            '
                        dateFormat: "' .
            $this->getDateFormat() .
            '",
                        buttonImage: "' .
            $this->getImage() .
            '",
                        ' .
            ($calendarYearsRange ? 'yearRange: "' .
            $calendarYearsRange .
            '",' : '') .
            '
                        buttonText: "' .
            (string)new \Magento\Framework\Phrase(
                'Select Date'
            ) .
            '"' . ($maxDate ? ', maxDate: "' . $maxDate . '"' : '') .
            ($changeMonth === null ? '' : ', changeMonth: ' . $changeMonth) .
            ($changeYear === null ? '' : ', changeYear: ' . $changeYear) .
            ($showOn ? ', showOn: "' . $showOn . '"' : '') .
            ($firstDay ? ', firstDay: ' . $firstDay : '') .
            '})
            });
            </script>';

        return $html;
    }

    /**
     * Convert special characters to HTML entities
     *
     * @return string
     */
    public function getEscapedValue()
    {
        if ($this->getFormat() && $this->getValue()) {
            return $this->getDateByFormat($this->getFormat(), strtotime($this->getValue()));
        }
        return $this->escapeHtml($this->getValue());
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->toHtml();
    }

    /**
     * Method to get date by format.
     *
     * @param string $format
     * @param int $timestamp
     *
     * @return string
     */
    private function getDateByFormat(string $format, int $timestamp): string
    {
        $format = str_replace(self::DATE_FORMAT_NOT_SUPPORTED, self::DATE_FORMAT_SUPPORTED, $format);

        if (strpos($format, '%') !== false) {
            $unsupportedData = ['%U', '%V', '%C'];

            foreach ($unsupportedData as $unsupported) {
                if (strpos($format, $unsupported) !== false) {
                    if ($unsupported === '%C') {
                        $format = str_replace($unsupported, round(date("Y", $timestamp) / 100), $format);

                        continue;
                    }
                    $format = str_replace($unsupported, date("W", strtotime("-1 day", $timestamp)), $format);
                }
            }
        }

        return date($format, $timestamp);
    }
}
