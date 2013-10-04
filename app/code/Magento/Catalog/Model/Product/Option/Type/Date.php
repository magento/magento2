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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product option date type
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Option\Type;

class Date extends \Magento\Catalog\Model\Product\Option\Type\DefaultType
{
    /**
     * @var mixed
     */
    protected $_formattedOptionValue = null;

    /**
     * Locale model
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        array $data = array()
    ) {
        $this->_locale = $locale;
        parent::__construct($checkoutSession, $coreStoreConfig, $data);
    }

    /**
     * Validate user input for option
     *
     * @throws \Magento\Core\Exception
     * @param array $values All product option values, i.e. array (option_id => mixed, option_id => mixed...)
     * @return \Magento\Catalog\Model\Product\Option\Type\DefaultType
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
                $dateValid = isset($value['day']) && isset($value['month']) && isset($value['year'])
                    && $value['day'] > 0 && $value['month'] > 0 && $value['year'] > 0;
            }
        }

        $timeValid = true;
        if ($this->_timeExists()) {
            $timeValid = isset($value['hour']) && isset($value['minute'])
                && is_numeric($value['hour']) && is_numeric($value['minute']);
        }

        $isValid = $dateValid && $timeValid;

        if ($isValid) {
            $this->setUserValue(
                array(
                    'date' => isset($value['date']) ? $value['date'] : '',
                    'year' => isset($value['year']) ? intval($value['year']) : 0,
                    'month' => isset($value['month']) ? intval($value['month']) : 0,
                    'day' => isset($value['day']) ? intval($value['day']) : 0,
                    'hour' => isset($value['hour']) ? intval($value['hour']) : 0,
                    'minute' => isset($value['minute']) ? intval($value['minute']) : 0,
                    'day_part' => isset($value['day_part']) ? $value['day_part'] : '',
                    'date_internal' => isset($value['date_internal']) ? $value['date_internal'] : '',
                )
            );
        } elseif (!$isValid && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            $this->setIsValid(false);
            if (!$dateValid) {
                throw new \Magento\Core\Exception(__('Please specify date required option(s).'));
            } elseif (!$timeValid) {
                throw new \Magento\Core\Exception(__('Please specify time required option(s).'));
            } else {
                throw new \Magento\Core\Exception(__('Please specify the product required option(s).'));
            }
        } else {
            $this->setUserValue(null);
            return $this;
        }

        return $this;
    }

    /**
     * Prepare option value for cart
     *
     * @throws \Magento\Core\Exception
     * @return mixed Prepared option value
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
                    $format = $this->_locale->getDateFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT);
                    $timestamp += $this->_locale->date($value['date'], $format, null, false)->getTimestamp();
                } else {
                    $timestamp += mktime(0, 0, 0, $value['month'], $value['day'], $value['year']);
                }
            } else {
                $timestamp += mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            }

            if ($this->_timeExists()) {
                // 24hr hour conversion
                if (! $this->is24hTimeFormat()) {
                    $pmDayPart = ('pm' == strtolower($value['day_part']));
                    if (12 == $value['hour']) {
                        $value['hour'] = $pmDayPart ? 12 : 0;
                    } elseif ($pmDayPart) {
                        $value['hour'] += 12;
                    }
                }

                $timestamp += 60 * 60 * $value['hour'] + 60 * $value['minute'];
            }

            $date = new \Zend_Date($timestamp);
            $result = $date->toString(\Magento\Date::DATETIME_INTERNAL_FORMAT);

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
     */
    public function getFormattedOptionValue($optionValue)
    {
        if ($this->_formattedOptionValue === null) {

            $option = $this->getOption();
            if ($this->getOption()->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE) {
                $format = $this->_locale->getDateFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_MEDIUM);
                $result = $this->_locale->date($optionValue, \Zend_Date::ISO_8601, null, false)
                    ->toString($format);
            } elseif ($this->getOption()->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME) {
                $format = $this->_locale->getDateTimeFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT);
                $result = $this->_locale
                    ->date($optionValue, \Magento\Date::DATETIME_INTERNAL_FORMAT, null, false)->toString($format);
            } elseif ($this->getOption()->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME) {
                $date = new \Zend_Date($optionValue);
                $result = date($this->is24hTimeFormat() ? 'H:i' : 'h:i a', $date->getTimestamp());
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
     */
    public function parseOptionValue($optionValue, $productOptionValues)
    {
        $timestamp = strtotime($optionValue);
        if ($timestamp === false || $timestamp == -1) {
            return null;
        }

        $date = new \Zend_Date($timestamp);
        return $date->toString(\Magento\Date::DATETIME_INTERNAL_FORMAT);
    }

    /**
     * Prepare option value for info buy request
     *
     * @param string $optionValue
     * @return mixed
     */
    public function prepareOptionValueForRequest($optionValue)
    {
        $confItem = $this->getConfigurationItem();
        $infoBuyRequest = $confItem->getOptionByCode('info_buyRequest');
        try {
            $value = unserialize($infoBuyRequest->getValue());
            if (is_array($value) && isset($value['options']) && isset($value['options'][$this->getOption()->getId()])) {
                return $value['options'][$this->getOption()->getId()];
            } else {
                return array('date_internal' => $optionValue);
            }
        } catch (\Exception $e) {
            return array('date_internal' => $optionValue);
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
     * @return mixed
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
     * @return mixed
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
     * @return \Magento\Catalog\Model\Product\Option\Type\Date
     */
    protected function _setInternalInRequest($internalValue)
    {
        $requestOptions = $this->getRequest()->getOptions();
        if (!isset($requestOptions[$this->getOption()->getId()])) {
            $requestOptions[$this->getOption()->getId()] = array();
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
        return in_array($this->getOption()->getType(), array(
            \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE,
            \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME
        ));
    }

    /**
     * Does option have time?
     *
     * @return boolean
     */
    protected function _timeExists()
    {
        return in_array($this->getOption()->getType(), array(
            \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME,
            \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME
        ));
    }
}
