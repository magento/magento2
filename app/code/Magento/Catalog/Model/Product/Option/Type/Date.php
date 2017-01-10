<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Option\Type;

/**
 * Catalog product option date type
 *
 * @author     Magento Core Team <core@magentocommerce.com>
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
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_localeDate = $localeDate;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
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

        $dateValid = true;
        if ($this->_dateExists()) {
            if ($this->useCalendar()) {
                $dateValid = isset($value['date']) && preg_match('/^\d{1,4}.+\d{1,4}.+\d{1,4}$/', $value['date']);
            } else {
                $dateValid = isset(
                    $value['day']
                ) && isset(
                    $value['month']
                ) && isset(
                    $value['year']
                ) && $value['day'] > 0 && $value['month'] > 0 && $value['year'] > 0;
            }
        }

        $timeValid = true;
        if ($this->_timeExists()) {
            $timeValid = isset(
                $value['hour']
            ) && isset(
                $value['minute']
            ) && is_numeric(
                $value['hour']
            ) && is_numeric(
                $value['minute']
            );
        }

        $isValid = $dateValid && $timeValid;

        if ($isValid) {
            $this->setUserValue(
                [
                    'date' => isset($value['date']) ? $value['date'] : '',
                    'year' => isset($value['year']) ? intval($value['year']) : 0,
                    'month' => isset($value['month']) ? intval($value['month']) : 0,
                    'day' => isset($value['day']) ? intval($value['day']) : 0,
                    'hour' => isset($value['hour']) ? intval($value['hour']) : 0,
                    'minute' => isset($value['minute']) ? intval($value['minute']) : 0,
                    'day_part' => isset($value['day_part']) ? $value['day_part'] : '',
                    'date_internal' => isset($value['date_internal']) ? $value['date_internal'] : '',
                ]
            );
        } elseif (!$isValid && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            $this->setIsValid(false);
            if (!$dateValid) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please specify date required option(s).')
                );
            } elseif (!$timeValid) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please specify time required option(s).')
                );
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please specify product\'s required option(s).')
                );
            }
        } else {
            $this->setUserValue(null);
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
                    \IntlDateFormatter::NONE,
                    null,
                    'UTC'
                );
            } elseif ($this->getOption()->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME) {
                $result = $this->_localeDate->formatDateTime(
                    new \DateTime($optionValue),
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::SHORT,
                    null,
                    'UTC'
                );
            } elseif ($this->getOption()->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME) {
                $result = $this->_localeDate->formatDateTime(
                    new \DateTime($optionValue),
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::SHORT,
                    null,
                    'UTC'
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
            $value = $this->serializer->unserialize($infoBuyRequest->getValue());
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
        if (!is_array($requestOptions[$this->getOption()->getId()])) {
            $requestOptions[$this->getOption()->getId()] = [];
        }
        $requestOptions[$this->getOption()->getId()]['date_internal'] = $internalValue;
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
