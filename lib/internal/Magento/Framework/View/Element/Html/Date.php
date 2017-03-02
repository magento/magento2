<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Html;

/**
 * Date element block
 */
class Date extends \Magento\Framework\View\Element\Template
{
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
            return strftime($this->getFormat(), strtotime($this->getValue()));
        }
        return htmlspecialchars($this->getValue());
    }

    /**
     * Produce and return block's html output
     *
     * {@inheritdoc}
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->toHtml();
    }
}
