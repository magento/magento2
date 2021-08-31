<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\View\Options\Type;

use DateTimeZone;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FilterFactory;
use Magento\Framework\Stdlib\DateTime;

/**
 * Product options text type block
 *
 * @api
 * @since 100.0.2
 */
class Date extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * Fill date and time options with leading zeros or not
     *
     * @var boolean
     */
    protected $_fillLeadingZeros = true;

    /**
     * Catalog product option type date
     *
     * @var \Magento\Catalog\Model\Product\Option\Type\Date
     */
    protected $_catalogProductOptionTypeDate;

    /**
     * @var FilterFactory
     */
    private $filterFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate
     * @param array $data
     * @param FilterFactory|null $filterFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate,
        array $data = [],
        ?FilterFactory $filterFactory = null
    ) {
        $this->_catalogProductOptionTypeDate = $catalogProductOptionTypeDate;
        parent::__construct($context, $pricingHelper, $catalogData, $data);
        $this->filterFactory = $filterFactory ?? ObjectManager::getInstance()->get(FilterFactory::class);
    }

    /**
     * Use JS calendar settings
     *
     * @return boolean
     */
    public function useCalendar()
    {
        return $this->_catalogProductOptionTypeDate->useCalendar();
    }

    /**
     * Date input
     *
     * @return string Formatted Html
     */
    public function getDateHtml()
    {
        if ($this->useCalendar()) {
            return $this->getCalendarDateHtml();
        } else {
            return $this->getDropDownsDateHtml();
        }
    }

    /**
     * JS Calendar html
     *
     * @return string Formatted Html
     */
    public function getCalendarDateHtml()
    {
        $option = $this->getOption();
        $values = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());

        $yearStart = $this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->_catalogProductOptionTypeDate->getYearEnd();

        $dateFormat = $this->_localeDate->getDateFormatWithLongYear();
        /** Escape RTL characters which are present in some locales and corrupt formatting */
        $escapedDateFormat = preg_replace('/[^MmDdYy\/\.\-]/', '', $dateFormat);
        $value = null;
        if (is_array($values)) {
            $date = $this->getInternalDateString($values);
            if ($date !== null) {
                $dateFilter = $this->filterFactory->create('date', ['format' => $escapedDateFormat]);
                $value = $dateFilter->outputFilter($date);
            } elseif (isset($values['date'])) {
                $value = $values['date'];
            }
        }
        $calendar = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Date::class
        )->setId(
            'options_' . $this->getOption()->getId() . '_date'
        )->setName(
            'options[' . $this->getOption()->getId() . '][date]'
        )->setClass(
            'product-custom-option datetime-picker input-text'
        )->setImage(
            $this->getViewFileUrl('Magento_Theme::calendar.png')
        )->setDateFormat(
            $escapedDateFormat
        )->setValue(
            $value
        )->setYearsRange(
            $yearStart . ':' . $yearEnd
        );

        return $calendar->getHtml();
    }

    /**
     * Date (dd/mm/yyyy) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getDropDownsDateHtml()
    {
        $fieldsSeparator = '&nbsp;';
        $fieldsOrder = $this->_catalogProductOptionTypeDate->getConfigData('date_fields_order');
        $fieldsOrder = str_replace(',', $fieldsSeparator, $fieldsOrder);

        $monthsHtml = $this->_getSelectFromToHtml('month', 1, 12);
        $daysHtml = $this->_getSelectFromToHtml('day', 1, 31);

        $yearStart = $this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->_catalogProductOptionTypeDate->getYearEnd();
        $yearsHtml = $this->_getSelectFromToHtml('year', $yearStart, $yearEnd);

        $translations = ['d' => $daysHtml, 'm' => $monthsHtml, 'y' => $yearsHtml];
        return strtr($fieldsOrder, $translations);
    }

    /**
     * Time (hh:mm am/pm) html drop-downs
     *
     * @return string Formatted Html
     */
    public function getTimeHtml()
    {
        if ($this->_catalogProductOptionTypeDate->is24hTimeFormat()) {
            $hourStart = 0;
            $hourEnd = 23;
            $dayPartHtml = '';
        } else {
            $hourStart = 1;
            $hourEnd = 12;
            $dayPartHtml = $this->_getHtmlSelect(
                'day_part'
            )->setOptions(
                [
                    'am' => $this->escapeHtml(__('AM')),
                    'pm' => $this->escapeHtml(__('PM'))
                ]
            )->getHtml();
        }
        $hoursHtml = $this->_getSelectFromToHtml('hour', $hourStart, $hourEnd);
        $minutesHtml = $this->_getSelectFromToHtml('minute', 0, 59);

        return $hoursHtml . '&nbsp;<b>:</b>&nbsp;' . $minutesHtml . '&nbsp;' . $dayPartHtml;
    }

    /**
     * Return drop-down html with range of values
     *
     * @param string $name Id/name of html select element
     * @param int $from Start position
     * @param int $to End position
     * @param int|null $value Value selected
     * @return string Formatted Html
     */
    protected function _getSelectFromToHtml($name, $from, $to, $value = null)
    {
        $options = [['value' => '', 'label' => '-']];
        for ($i = $from; $i <= $to; $i++) {
            $options[] = ['value' => $i, 'label' => $this->_getValueWithLeadingZeros($i)];
        }
        return $this->_getHtmlSelect($name, $value)->setOptions($options)->getHtml();
    }

    /**
     * HTML select element
     *
     * @param string $name Id/name of html select element
     * @param int|null $value
     * @return mixed
     */
    protected function _getHtmlSelect($name, $value = null)
    {
        $option = $this->getOption();

        $this->setSkipJsReloadPrice(1);

        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setId(
            'options_' . $this->getOption()->getId() . '_' . $name
        )->setClass(
            'product-custom-option admin__control-select datetime-picker' . $require
        )->setExtraParams()->setName(
            'options[' . $option->getId() . '][' . $name . ']'
        );

        $extraParams = 'style="width:auto"';
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $extraParams .= ' data-role="calendar-dropdown" data-calendar-role="' . $name . '"';
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        if ($this->getOption()->getIsRequire()) {
            $extraParams .= ' data-validate=\'{"datetime-validation": true}\'';
        }

        $select->setExtraParams($extraParams);
        if ($value === null) {
            $values = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
            $value = is_array($values) ? $this->parseDate($values, $name) : null;
        }
        if ($value !== null) {
            $select->setValue($value);
        }

        return $select;
    }

    /**
     * Add Leading Zeros to number less than 10
     *
     * @param int $value
     * @return string|int
     */
    protected function _getValueWithLeadingZeros($value)
    {
        if (!$this->_fillLeadingZeros) {
            return $value;
        }
        return $value < 10 ? '0' . $value : $value;
    }

    /**
     * Get internal date format of provided value
     *
     * @param array $value
     * @return string|null
     */
    private function getInternalDateString(array $value): ?string
    {
        $result = null;
        if (!empty($value['date']) && !empty($value['date_internal'])) {
            $dateTimeZone = new DateTimeZone($this->_localeDate->getConfigTimezone());
            $dateTimeObject = date_create_from_format(
                DateTime::DATETIME_PHP_FORMAT,
                $value['date_internal'],
                $dateTimeZone
            );
            if ($dateTimeObject !== false) {
                $result = $dateTimeObject->format(DateTime::DATE_PHP_FORMAT);
            }
        } elseif (!empty($value['day']) && !empty($value['month']) && !empty($value['year'])) {
            $dateTimeObject = $this->_localeDate->date();
            $dateTimeObject->setDate((int) $value['year'], (int) $value['month'], (int) $value['day']);
            $result = $dateTimeObject->format(DateTime::DATE_PHP_FORMAT);
        }
        return $result;
    }

    /**
     * Parse option value and return the requested part
     *
     * @param array $value
     * @param string $part [year, month, day, hour, minute, day_part]
     * @return string|null
     */
    private function parseDate(array $value, string $part): ?string
    {
        $result = null;
        if (!empty($value['date']) && !empty($value['date_internal'])) {
            $formatDate = explode(' ', $value['date_internal']);
            $date = explode('-', $formatDate[0]);
            $value['year'] = $date[0];
            $value['month'] = $date[1];
            $value['day'] = $date[2];
        }

        if (isset($value[$part])) {
            $result = (string) $value[$part];
        }

        return $result;
    }
}
