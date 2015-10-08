<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Webapi\Product\Option\Type;

use Magento\Framework\Stdlib\DateTime;

/**
 * Catalog product option date validator
 */
class Date extends \Magento\Catalog\Model\Product\Option\Type\DefaultType
{
    /**
     * @var string
     */
    protected $_formattedOptionValue = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param array $data
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        array $data = []
    ) {
        $this->_localeDate = $localeDate;
        parent::__construct($checkoutSession, $scopeConfig, $data);
    }

    /**
     * Validate user input for option
     *
     * @param array $values All product option values, i.e. array (option_id => mixed, option_id => mixed...)
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateUserValue($values)
    {
        parent::validateUserValue($values);

        $option = $this->getOption();
        $value = $this->getUserValue();
        $dateTime = \DateTime::createFromFormat(DateTime::DATETIME_PHP_FORMAT, $value);

        $dateValid = true;
        $lastErrors = \DateTime::getLastErrors();
        if (!($dateTime && $lastErrors['error_count'] == 0)) {
            $dateValid = false;
        }

        if ($dateValid && $dateTime) {
            $this->setUserValue(
                [
                    'date' => $value,
                    'year' => $dateTime->format('Y'),
                    'month' => $dateTime->format('m'),
                    'day' => $dateTime->format('d'),
                    'hour' => $dateTime->format('H'),
                    'minute' => intval($dateTime->format('i')),
                    'day_part' => $dateTime->format('a'),
                    'date_internal' => '',
                ]
            );
        } elseif (!$dateValid && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            $this->setIsValid(false);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please specify product\'s required option(s).')
            );
        } else {
            $this->setUserValue(null);
            return $this;
        }

        return $this;
    }

    /**
     * Prepare option value for cart
     *
     * @return string|null Prepared option value
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function prepareForCart()
    {
        if ($this->getIsValid() && $this->getUserValue() !== null) {
            $option = $this->getOption();
            $value = $this->getUserValue();

            if (isset($value['date_internal']) && $value['date_internal'] != '') {
                $this->_setInternalInRequest($value['date_internal']);
                return $value['date_internal'];
            }

            $timestamp = 0;

            if ($this->_dateExists()) {
                if ($this->useCalendar()) {
                    $timestamp += (new \DateTime($value['date']))->getTimestamp();
                } else {
                    $timestamp += mktime(0, 0, 0, $value['month'], $value['day'], $value['year']);
                }
            } else {
                $timestamp += mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            }

            if ($this->_timeExists()) {
                // 24hr hour conversion
                if (!$this->is24hTimeFormat()) {
                    $pmDayPart = 'pm' == strtolower($value['day_part']);
                    if (12 == $value['hour']) {
                        $value['hour'] = $pmDayPart ? 12 : 0;
                    } elseif ($pmDayPart) {
                        $value['hour'] += 12;
                    }
                }

                $timestamp += 60 * 60 * $value['hour'] + 60 * $value['minute'];
            }

            $date = (new \DateTime())->setTimestamp($timestamp);
            $result = $date->format('Y-m-d H:i:s');

            // Save date in internal format to avoid locale date bugs
            $this->_setInternalInRequest($result);

            return $result;
        } else {
            return null;
        }
    }

    /**
     * Return formatted option value for quote option
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getFormattedOptionValue($optionValue)
    {
        if ($this->_formattedOptionValue === null) {
            if ($this->getOption()->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE) {
                $result = $this->_localeDate->formatDateTime(
                    new \DateTime($optionValue),
                    \IntlDateFormatter::MEDIUM,
                    \IntlDateFormatter::NONE
                );
            } elseif ($this->getOption()->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME) {
                $result = $this->_localeDate->formatDateTime(
                    new \DateTime($optionValue),
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::SHORT
                );
            } elseif ($this->getOption()->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME) {
                $result = $this->_localeDate->formatDateTime(
                    new \DateTime($optionValue),
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::SHORT
                );
            } else {
                $result = $optionValue;
            }
            $this->_formattedOptionValue = $result;
        }
        return $this->_formattedOptionValue;
    }

    /**
     * Return printable option value
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getPrintableOptionValue($optionValue)
    {
        return $this->getFormattedOptionValue($optionValue);
    }

    /**
     * Return formatted option value ready to edit, ready to parse
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getEditableOptionValue($optionValue)
    {
        return $this->getFormattedOptionValue($optionValue);
    }

    /**
     * Parse user input value and return cart prepared value
     *
     * @param string $optionValue
     * @param array $productOptionValues Values for product option
     * @return string|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function parseOptionValue($optionValue, $productOptionValues)
    {
        try {
            $date = new \DateTime($optionValue);
        } catch (\Exception $e) {
            return null;
        }
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Prepare option value for info buy request
     *
     * @param string $optionValue
     * @return array
     */
    public function prepareOptionValueForRequest($optionValue)
    {
        $confItem = $this->getConfigurationItem();
        $infoBuyRequest = $confItem->getOptionByCode('info_buyRequest');
        try {
            $value = unserialize($infoBuyRequest->getValue());
            if (is_array($value) && isset($value['options']) && isset($value['options'][$this->getOption()->getId()])
            ) {
                return $value['options'][$this->getOption()->getId()];
            } else {
                return ['date_internal' => $optionValue];
            }
        } catch (\Exception $e) {
            return ['date_internal' => $optionValue];
        }
    }

    /**
     * Use Calendar on frontend or not
     *
     * @return boolean
     */
    public function useCalendar()
    {
        return (bool)$this->getConfigData('use_calendar');
    }

    /**
     * Time Format
     *
     * @return boolean
     */
    public function is24hTimeFormat()
    {
        return (bool)($this->getConfigData('time_format') == '24h');
    }

    /**
     * Year range start
     *
     * @return string|false
     */
    public function getYearStart()
    {
        $_range = explode(',', $this->getConfigData('year_range'));
        if (isset($_range[0]) && !empty($_range[0])) {
            return $_range[0];
        } else {
            return date('Y');
        }
    }

    /**
     * Year range end
     *
     * @return string|false
     */
    public function getYearEnd()
    {
        $_range = explode(',', $this->getConfigData('year_range'));
        if (isset($_range[1]) && !empty($_range[1])) {
            return $_range[1];
        } else {
            return date('Y');
        }
    }

    /**
     * Save internal value of option in infoBuy_request
     *
     * @param string $internalValue Datetime value in internal format
     * @return void
     */
    protected function _setInternalInRequest($internalValue)
    {
        $requestOptions = $this->getRequest()->getOptions();
        if (!isset($requestOptions[$this->getOption()->getId()])) {
            $requestOptions[$this->getOption()->getId()] = [];
        }
        $requestOptions[$this->getOption()->getId()] = ['date_internal' => $internalValue];
        $this->getRequest()->setOptions($requestOptions);
    }

    /**
     * Does option have date?
     *
     * @return boolean
     */
    protected function _dateExists()
    {
        return in_array(
            $this->getOption()->getType(),
            [
                \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE,
                \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME
            ]
        );
    }

    /**
     * Does option have time?
     *
     * @return boolean
     */
    protected function _timeExists()
    {
        return in_array(
            $this->getOption()->getType(),
            [
                \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME,
                \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME
            ]
        );
    }
}
