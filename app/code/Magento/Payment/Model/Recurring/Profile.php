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
 * @package     Magento_Payment
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring payment profile
 * Extends from \Magento\Core\Abstract for a reason: to make descendants have its own resource
 */
namespace Magento\Payment\Model\Recurring;

class Profile extends \Magento\Core\Model\AbstractModel
{
    /**
     * Constants for passing data through catalog
     *
     * @var string
     */
    const BUY_REQUEST_START_DATETIME = 'recurring_profile_start_datetime';
    const PRODUCT_OPTIONS_KEY = 'recurring_profile_options';

    /**
     * Period units
     *
     * @var string
     */
    const PERIOD_UNIT_DAY = 'day';
    const PERIOD_UNIT_WEEK = 'week';
    const PERIOD_UNIT_SEMI_MONTH = 'semi_month';
    const PERIOD_UNIT_MONTH = 'month';
    const PERIOD_UNIT_YEAR = 'year';

    /**
     * Errors collected during validation
     *
     * @var array
     */
    protected $_errors = array();

    /**
     *
     * @var \Magento\Payment\Model\Method\AbstractMethod
     */
    protected $_methodInstance = null;

    /**
     * Locale instance used for importing/exporting data
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale = null;

    /**
     * Store instance used by locale or method instance
     *
     * @var \Magento\Core\Model\Store
     */
    protected $_store = null;

    /**
     * Payment methods reference
     *
     * @var array
     */
    protected $_paymentMethods = array();

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData = null;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_paymentData = $paymentData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Check whether the object data is valid
     * Returns true if valid.
     *
     * @return bool
     */
    public function isValid()
    {
        $this->_filterValues();
        $this->_errors = array();

        // start date, order ref ID, schedule description
        if (!$this->getStartDatetime()) {
            $this->_errors['start_datetime'][] = __('The start date is undefined.');
        } elseif (!\Zend_Date::isDate($this->getStartDatetime(), \Magento\Date::DATETIME_INTERNAL_FORMAT)) {
            $this->_errors['start_datetime'][] = __('The start date has an invalid format.');
        }
        if (!$this->getScheduleDescription()) {
            $this->_errors['schedule_description'][] = __('The schedule description must be provided.');
        }

        // period unit and frequency
        if (!$this->getPeriodUnit() || !in_array($this->getPeriodUnit(), $this->getAllPeriodUnits(false), true)) {
            $this->_errors['period_unit'][] = __('The billing period unit is not defined or wrong.');
        }
        if ($this->getPeriodFrequency() && !$this->_validatePeriodFrequency('period_unit', 'period_frequency')) {
            $this->_errors['period_frequency'][] = __('The period frequency is wrong.');;
        }

        // trial period unit, trial frequency, trial period max cycles, trial billing amount
        if ($this->getTrialPeriodUnit()) {
            if (!in_array($this->getTrialPeriodUnit(), $this->getAllPeriodUnits(false), true)) {
                $this->_errors['trial_period_unit'][] = __('The trial billing period unit is wrong.');
            }
            if (!$this->getTrialPeriodFrequency()
                || !$this->_validatePeriodFrequency('trial_period_unit', 'trial_period_frequency')) {
                $this->_errors['trial_period_frequency'][] = __('The trial period frequency is wrong.');
            }
            if (!$this->getTrialPeriodMaxCycles()) {
                $this->_errors['trial_period_max_cycles'][] = __('The trial period max cycles is wrong.');
            }
            if (!$this->getTrialBillingAmount()) {
                $this->_errors['trial_billing_amount'][] = __('The trial billing amount is wrong.');
            }
        }

        // billing and other amounts
        if (!$this->getBillingAmount() || 0 >= $this->getBillingAmount()) {
            $this->_errors['billing_amount'][] = __('We found a wrong or empty billing amount specified.');
        }
        foreach (array('trial_billing_abount', 'shipping_amount', 'tax_amount', 'init_amount') as $key) {
            if ($this->hasData($key) && 0 >= $this->getData($key)) {
                $this->_errors[$key][] = __('The wrong %1 is specified.', $this->getFieldLabel($key));
            }
        }

        // currency code
        if (!$this->getCurrencyCode()) {
            $this->_errors['currency_code'][] = __('The currency code is undefined.');
        }

        // payment method
        if (!$this->_methodInstance || !$this->getMethodCode()) {
            $this->_errors['method_code'][] = __('The payment method code is undefined.');
        }
        if ($this->_methodInstance) {
            try {
                $this->_methodInstance->validateRecurringProfile($this);
            } catch (\Magento\Core\Exception $e) {
                $this->_errors['payment_method'][] = $e->getMessage();
            }
        }

        return empty($this->_errors);
    }

    /**
     * Getter for errors that may appear after validation
     *
     * @param bool $isGrouped
     * @param bool $asMessage
     * @return array
     * @throws \Magento\Core\Exception
     */
    public function getValidationErrors($isGrouped = true, $asMessage = false)
    {
        if ($isGrouped && $this->_errors) {
            $result = array();
            foreach ($this->_errors as $row) {
                $result[] = implode(' ', $row);
            }
            if ($asMessage) {
                throw new \Magento\Core\Exception(__("The payment profile is invalid:\n%1.",
                    implode("\n", $result)));
            }
            return $result;
        }
        return $this->_errors;
    }

    /**
     * Setter for payment method instance
     *
     * @param \Magento\Payment\Model\Method\AbstractMethod $object
     * @return \Magento\Payment\Model\Recurring\Profile
     * @throws \Exception
     */
    public function setMethodInstance(\Magento\Payment\Model\Method\AbstractMethod $object)
    {
        if ($object instanceof \Magento\Payment\Model\Recurring\Profile\MethodInterface) {
            $this->_methodInstance = $object;
        } else {
            throw new \Exception('Invalid payment method instance for use in recurring profile.');
        }
        return $this;
    }

    /**
     * Collect needed information from buy request
     * Then filter data
     *
     * @param \Magento\Object $buyRequest
     * @return \Magento\Payment\Model\Recurring\Profile
     * @throws \Magento\Core\Exception
     */
    public function importBuyRequest(\Magento\Object $buyRequest)
    {
        $startDate = $buyRequest->getData(self::BUY_REQUEST_START_DATETIME);
        if ($startDate) {
            $this->_ensureLocaleAndStore();
            $dateFormat = $this->_locale->getDateTimeFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT);
            $localeCode = $this->_locale->getLocaleCode();
            if (!\Zend_Date::isDate($startDate, $dateFormat, $localeCode)) {
                throw new \Magento\Core\Exception(__('The recurring profile start date has invalid format.'));
            }
            $utcTime = $this->_locale->utcDate($this->_store, $startDate, true, $dateFormat)
                ->toString(\Magento\Date::DATETIME_INTERNAL_FORMAT);
            $this->setStartDatetime($utcTime)->setImportedStartDatetime($startDate);
        }
        return $this->_filterValues();
    }

    /**
     * Import product recurring profile information
     * Returns false if it cannot be imported
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Payment\Model\Recurring\Profile|false
     */
    public function importProduct(\Magento\Catalog\Model\Product $product)
    {
        if ($product->isRecurring() && is_array($product->getRecurringProfile())) {
            // import recurring profile data
            $this->addData($product->getRecurringProfile());

            // automatically set product name if there is no schedule description
            if (!$this->hasScheduleDescription()) {
                $this->setScheduleDescription($product->getName());
            }

            // collect start datetime from the product options
            $options = $product->getCustomOption(self::PRODUCT_OPTIONS_KEY);
            if ($options) {
                $options = unserialize($options->getValue());
                if (is_array($options)) {
                    if (isset($options['start_datetime'])) {
                        $startDatetime = new \Zend_Date($options['start_datetime'],
                            \Magento\Date::DATETIME_INTERNAL_FORMAT);
                        $this->setNearestStartDatetime($startDatetime);
                    }
                }
            }

            return $this->_filterValues();
        }
        return false;
    }

    /**
     * Render available schedule information
     *
     * @return array
     */
    public function exportScheduleInfo()
    {
        $result = array(
            new \Magento\Object(array(
                'title'    => __('Billing Period'),
                'schedule' => $this->_renderSchedule('period_unit', 'period_frequency', 'period_max_cycles'),
            ))
        );
        $trial = $this->_renderSchedule('trial_period_unit', 'trial_period_frequency', 'trial_period_max_cycles');
        if ($trial) {
            $result[] = new \Magento\Object(array(
                'title'    => __('Trial Period'),
                'schedule' => $trial,
            ));
        }
        return $result;
    }

    /**
     * Determine nearest possible profile start date
     *
     * @param \Zend_Date $minAllowed
     * @return \Magento\Payment\Model\Recurring\Profile
     */
    public function setNearestStartDatetime(\Zend_Date $minAllowed = null)
    {
        // TODO: implement proper logic with invoking payment method instance
        $date = $minAllowed;
        if (!$date || $date->getTimestamp() < time()) {
            $date = new \Zend_Date(time());
        }
        $this->setStartDatetime($date->toString(\Magento\Date::DATETIME_INTERNAL_FORMAT));
        return $this;
    }

    /**
     * Convert the start datetime (if set) to proper locale/timezone and return
     *
     * @param bool $asString
     * @return \Zend_Date|string
     */
    public function exportStartDatetime($asString = true)
    {
        $datetime = $this->getStartDatetime();
        if (!$datetime || !$this->_locale || !$this->_store) {
            return;
        }
        $date = $this->_locale->storeDate($this->_store, strtotime($datetime), true);
        if ($asString) {
            return $date->toString(
                $this->_locale->getDateTimeFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT)
            );
        }
        return $date;
    }

    /**
     * Locale instance setter
     *
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @return \Magento\Payment\Model\Recurring\Profile
     */
    public function setLocale(\Magento\Core\Model\LocaleInterface $locale)
    {
        $this->_locale = $locale;
        return $this;
    }

    /**
     * Store instance setter
     *
     * @param \Magento\Core\Model\Store $store
     * @return \Magento\Payment\Model\Recurring\Profile
     */
    public function setStore(\Magento\Core\Model\Store $store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Getter for available period units
     *
     * @param bool $withLabels
     * @return array
     */
    public function getAllPeriodUnits($withLabels = true)
    {
        $units = array(
            self::PERIOD_UNIT_DAY,
            self::PERIOD_UNIT_WEEK,
            self::PERIOD_UNIT_SEMI_MONTH,
            self::PERIOD_UNIT_MONTH,
            self::PERIOD_UNIT_YEAR
        );

        if ($withLabels) {
            $result = array();
            foreach ($units as $unit) {
                $result[$unit] = $this->getPeriodUnitLabel($unit);
            }
            return $result;
        }
        return $units;
    }

    /**
     * Render label for specified period unit
     *
     * @param string $unit
     * @return string
     */
    public function getPeriodUnitLabel($unit)
    {
        switch ($unit) {
            case self::PERIOD_UNIT_DAY:
                return __('Day');
            case self::PERIOD_UNIT_WEEK:
                return __('Week');
            case self::PERIOD_UNIT_SEMI_MONTH:
                return __('Two Weeks');
            case self::PERIOD_UNIT_MONTH:
                return __('Month');
            case self::PERIOD_UNIT_YEAR:
                return __('Year');
        }
        return $unit;
    }

    /**
     * Getter for field label
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldLabel($field)
    {
        switch ($field) {
            case 'subscriber_name':
                return __('Subscriber Name');
            case 'start_datetime':
                return __('Start Date');
            case 'internal_reference_id':
                return __('Internal Reference ID');
            case 'schedule_description':
                return __('Schedule Description');
            case 'suspension_threshold':
                return __('Maximum Payment Failures');
            case 'bill_failed_later':
                return __('Auto Bill on Next Cycle');
            case 'period_unit':
                return __('Billing Period Unit');
            case 'period_frequency':
                return __('Billing Frequency');
            case 'period_max_cycles':
                return __('Maximum Billing Cycles');
            case 'billing_amount':
                return __('Billing Amount');
            case 'trial_period_unit':
                return __('Trial Billing Period Unit');
            case 'trial_period_frequency':
                return __('Trial Billing Frequency');
            case 'trial_period_max_cycles':
                return __('Maximum Trial Billing Cycles');
            case 'trial_billing_amount':
                return __('Trial Billing Amount');
            case 'currency_code':
                return __('Currency');
            case 'shipping_amount':
                return __('Shipping Amount');
            case 'tax_amount':
                return __('Tax Amount');
            case 'init_amount':
                return __('Initial Fee');
            case 'init_may_fail':
                return __('Allow Initial Fee Failure');
            case 'method_code':
                return __('Payment Method');
            case 'reference_id':
                return __('Payment Reference ID');
        }
    }

    /**
     * Getter for field comments
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldComment($field)
    {
        switch ($field) {
            case 'subscriber_name':
                return __('Full name of the person receiving the product or service '
                    . 'paid for by the recurring payment.');
            case 'start_datetime':
                return __('This is the date when billing for the profile begins.');
            case 'schedule_description':
                return __('Enter a short description of the recurring payment. '
                    . 'By default, this description will match the product name.');
            case 'suspension_threshold':
                return __('This is the number of scheduled payments '
                    . 'that can fail before the profile is automatically suspended.');
            case 'bill_failed_later':
                return __('Use this to automatically bill the outstanding balance amount in the next billing cycle '
                    . '(if there were failed payments).');
            case 'period_unit':
                return __('This is the unit for billing during the subscription period.');
            case 'period_frequency':
                return __('This is the number of billing periods that make up one billing cycle.');
            case 'period_max_cycles':
                return __('This is the number of billing cycles for the payment period.');
            case 'init_amount':
                return __('The initial, non-recurring payment amount is due immediately when the profile is created.');
            case 'init_may_fail':
                return __('This sets whether to suspend the payment profile if the initial fee fails or, '
                    . 'instead, add it to the outstanding balance.');
        }
    }

    /**
     * Transform some specific data for output
     *
     * @param string $key
     * @return mixed
     */
    public function renderData($key)
    {
        $value = $this->_getData($key);
        switch ($key) {
            case 'period_unit':
                return $this->getPeriodUnitLabel($value);
            case 'method_code':
                if (!$this->_paymentMethods) {
                    $this->_paymentMethods = $this->_paymentData->getPaymentMethodList(false);
                }
                if (isset($this->_paymentMethods[$value])) {
                    return $this->_paymentMethods[$value];
                }
                break;
            case 'start_datetime':
                return $this->exportStartDatetime(true);
        }
        return $value;
    }

    /**
     * Filter self data to make sure it can be validated properly
     *
     * @return \Magento\Payment\Model\Recurring\Profile
     */
    protected function _filterValues()
    {
        // determine payment method/code
        if ($this->_methodInstance) {
            $this->setMethodCode($this->_methodInstance->getCode());
        } elseif ($this->getMethodCode()) {
            $this->getMethodInstance();
        }

        // unset redundant values, if empty
        foreach (array('schedule_description',
            'suspension_threshold', 'bill_failed_later', 'period_frequency', 'period_max_cycles', 'reference_id',
            'trial_period_unit', 'trial_period_frequency', 'trial_period_max_cycles', 'init_may_fail') as $key) {
            if ($this->hasData($key) && (!$this->getData($key) || '0' == $this->getData($key))) {
                $this->unsetData($key);
            }
        }

        // cast amounts
        foreach (array(
            'billing_amount', 'trial_billing_amount', 'shipping_amount', 'tax_amount', 'init_amount') as $key) {
            if ($this->hasData($key)) {
                if (!$this->getData($key) || 0 == $this->getData($key)) {
                    $this->unsetData($key);
                } else {
                    $this->setData($key, sprintf('%.4F', $this->getData($key)));
                }
            }
        }

        // automatically determine start date, if not set
        if ($this->getStartDatetime()) {
            $date = new \Zend_Date($this->getStartDatetime(), \Magento\Date::DATETIME_INTERNAL_FORMAT);
            $this->setNearestStartDatetime($date);
        } else {
            $this->setNearestStartDatetime();
        }

        return $this;
    }

    /**
     * Check that locale and store instances are set
     *
     * @throws \Exception
     */
    protected function _ensureLocaleAndStore()
    {
        if (!$this->_locale || !$this->_store) {
            throw new \Exception('Locale and store instances must be set for this operation.');
        }
    }

    /**
     * Return payment method instance
     *
     * @return \Magento\Payment\Model\Method\AbstractMethod
     */
    protected function getMethodInstance()
    {
        if (!$this->_methodInstance) {
            $this->setMethodInstance($this->_paymentData->getMethodInstance($this->getMethodCode()));
        }
        $this->_methodInstance->setStore($this->getStoreId());
        return $this->_methodInstance;
    }

    /**
     * Check accordance of the unit and frequency
     *
     * @param string $unitKey
     * @param string $frequencyKey
     * @return bool
     */
    protected function _validatePeriodFrequency($unitKey, $frequencyKey)
    {
        if ($this->getData($unitKey) == self::PERIOD_UNIT_SEMI_MONTH && $this->getData($frequencyKey) != 1) {
            return false;
        }
        return true;
    }

    /**
     * Perform full validation before saving
     *
     * @throws \Magento\Core\Exception
     */
    protected function _validateBeforeSave()
    {
        if (!$this->isValid()) {
            throw new \Magento\Core\Exception($this->getValidationErrors(true, true));
        }
        if (!$this->getInternalReferenceId()) {
            throw new \Magento\Core\Exception(__('An internal reference ID is required to save the payment profile.'));
        }
    }

    /**
     * Validate before saving
     *
     * @return \Magento\Payment\Model\Recurring\Profile
     */
    protected function _beforeSave()
    {
        $this->_validateBeforeSave();
        return parent::_beforeSave();
    }

    /**
     * Generate explanations for specified schedule parameters
     *
     * TODO: utilize \Zend_Translate_Plural or similar stuff to render proper declensions with numerals.
     *
     * @param string $periodKey
     * @param string $frequencyKey
     * @param string $cyclesKey
     * @return array
     */
    protected function _renderSchedule($periodKey, $frequencyKey, $cyclesKey)
    {
        $result = array();

        $period = $this->_getData($periodKey);
        $frequency = (int)$this->_getData($frequencyKey);
        if (!$period || !$frequency) {
            return $result;
        }
        if (self::PERIOD_UNIT_SEMI_MONTH == $period) {
            $frequency = '';
        }
        $result[] = __('%1 %2 cycle.', $frequency, $this->getPeriodUnitLabel($period));

        $cycles = (int)$this->_getData($cyclesKey);
        if ($cycles) {
            $result[] = __('Repeats %1 time(s)', $cycles);
        } else {
            $result[] = __('Repeats until suspended or canceled.');
        }
        return $result;
    }
}
