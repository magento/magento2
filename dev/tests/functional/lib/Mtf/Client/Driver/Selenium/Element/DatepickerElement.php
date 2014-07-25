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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element as ElementInterface;
use Mtf\Client\Element\Locator;

/**
 * Class DatepickerElement
 * General class for datepicker elements.
 */
class DatepickerElement extends Element
{
    /**
     * DatePicker button
     *
     * @var string
     */
    protected $datePickerButton = './../img[contains(@class,"ui-datepicker-trigger")]';

    /**
     * DatePicker block
     *
     * @var string
     */
    protected $datePickerBlock = './ancestor::body//*[@id="ui-datepicker-div"]';

    /**
     * Field Month on the DatePicker
     *
     * @var string
     */
    protected $datePickerMonth = './/*[contains(@class,"ui-datepicker-month")]';

    /**
     * Field Year on the DatePicker
     *
     * @var string
     */
    protected $datePickerYear = './/*[contains(@class,"ui-datepicker-year")]';

    /**
     * Calendar on the DatePicker
     *
     * @var string
     */
    protected $datePickerCalendar = './/*[contains(@class,"ui-datepicker-calendar")]//*/td/a[text()="%s"]';

    /**
     * DatePicker button 'Close'
     *
     * @var string
     */
    protected $datePickerButtonClose = './/*[contains(@class,"ui-datepicker-close")]';

    /**
     * Set the date from datePicker
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
        $datapicker->find($this->datePickerMonth, Locator::SELECTOR_XPATH, 'select')->setValue($date[0]);
        $datapicker->find($this->datePickerYear, Locator::SELECTOR_XPATH, 'select')->setValue($date[2]);
        $datapicker->find(sprintf($this->datePickerCalendar, $date[1]), Locator::SELECTOR_XPATH)->click();
        if ($datapicker->isVisible()) {
            $datapicker->find($this->datePickerButtonClose, Locator::SELECTOR_XPATH)->click();
        }
    }

    /**
     * Parse date from string to array
     *
     * @param string $value
     * @return array
     */
    protected function parseDate($value)
    {
        $date = strtotime($value);
        $date = strftime("%b %#d, %Y %I:%M %p", $date);
        $date = preg_split('/[,\s]/', $date);
        array_splice($date, 2, 1);

        return $date;
    }
}
