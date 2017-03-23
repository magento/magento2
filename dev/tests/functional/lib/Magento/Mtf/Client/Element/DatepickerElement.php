<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * General class for datepicker elements.
 */
class DatepickerElement extends SimpleElement
{
    /**
     * DatePicker button.
     *
     * @var string
     */
    protected $datePickerButton = './../button[contains(@class,"ui-datepicker-trigger")]';

    /**
     * DatePicker block.
     *
     * @var string
     */
    protected $datePickerBlock = './ancestor::body//*[@id="ui-datepicker-div"]';

    /**
     * Field Month on the DatePicker.
     *
     * @var string
     */
    protected $datePickerMonth = './/*[contains(@class,"ui-datepicker-month")]';

    /**
     * Field Year on the DatePicker.
     *
     * @var string
     */
    protected $datePickerYear = './/*[contains(@class,"ui-datepicker-year")]';

    /**
     * Calendar on the DatePicker.
     *
     * @var string
     */
    protected $datePickerCalendar = './/*[contains(@class,"ui-datepicker-calendar")]//*/td/a[text()="%s"]';

    /**
     * DatePicker button 'Close'.
     *
     * @var string
     */
    protected $datePickerButtonClose = './/*[contains(@class,"ui-datepicker-close")]';

    /**
     * Set the date from datePicker.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $date = $this->parseDate($value);
        $date[1] = ltrim($date[1], '0');
        $this->find($this->datePickerButton, Locator::SELECTOR_XPATH)->click();
        $datapicker = $this->find($this->datePickerBlock, Locator::SELECTOR_XPATH);
        $datapicker->find($this->datePickerYear, Locator::SELECTOR_XPATH, 'select')->setValue($date[2]);
        $datapicker->find($this->datePickerMonth, Locator::SELECTOR_XPATH, 'select')->setValue($date[0]);
        $datapicker->find(sprintf($this->datePickerCalendar, $date[1]), Locator::SELECTOR_XPATH)->click();
        if ($datapicker->isVisible()) {
            $datapicker->find($this->datePickerButtonClose, Locator::SELECTOR_XPATH)->click();
        }
    }

    /**
     * Parse date from string to array.
     *
     * @param string $value
     * @return array
     */
    protected function parseDate($value)
    {
        $formatDate = '%b %d, %Y %I:%M %p';
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $formatDate = str_replace('%d', '%#d', $formatDate);
        }

        $date = strtotime($value);
        $date = strftime($formatDate, $date);
        $date = preg_split('/[,\s]/', $date);
        array_splice($date, 2, 1);

        return $date;
    }
}
