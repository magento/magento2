<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper;

use DateTime;
use IntlDateFormatter;
use Zend\Form\ElementInterface;
use Zend\Form\Element\DateTimeSelect as DateTimeSelectElement;
use Zend\Form\Exception;
use Zend\Form\View\Helper\FormDateSelect as FormDateSelectHelper;

class FormDateTimeSelect extends FormDateSelectHelper
{
    /**
     * Time formatter to use
     *
     * @var int
     */
    protected $timeType;

    /**
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present
     */
    public function __construct()
    {
        parent::__construct();

        // Delaying initialization until we know ext/intl is available
        $this->timeType = IntlDateFormatter::LONG;
    }

    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param ElementInterface $element
     * @param int              $dateType
     * @param int|null|string  $timeType
     * @param null|string      $locale
     * @return string
     */
    public function __invoke(
        ElementInterface $element = null,
        $dateType = IntlDateFormatter::LONG,
        $timeType = IntlDateFormatter::LONG,
        $locale = null
    ) {
        if (!$element) {
            return $this;
        }

        $this->setDateType($dateType);
        $this->setTimeType($timeType);

        if ($locale !== null) {
            $this->setLocale($locale);
        }

        return $this->render($element);
    }

    /**
     * Render a date element that is composed of six selects
     *
     * @param  ElementInterface $element
     * @return string
     * @throws \Zend\Form\Exception\InvalidArgumentException
     * @throws \Zend\Form\Exception\DomainException
     */
    public function render(ElementInterface $element)
    {
        if (!$element instanceof DateTimeSelectElement) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that the element is of type Zend\Form\Element\DateTimeSelect',
                __METHOD__
            ));
        }

        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $shouldRenderDelimiters = $element->shouldRenderDelimiters();
        $selectHelper = $this->getSelectElementHelper();
        $pattern      = $this->parsePattern($shouldRenderDelimiters);

        $daysOptions   = $this->getDaysOptions($pattern['day']);
        $monthsOptions = $this->getMonthsOptions($pattern['month']);
        $yearOptions   = $this->getYearsOptions($element->getMinYear(), $element->getMaxYear());
        $hourOptions   = $this->getHoursOptions($pattern['hour']);
        $minuteOptions = $this->getMinutesOptions($pattern['minute']);
        $secondOptions = $this->getSecondsOptions($pattern['second']);

        $dayElement    = $element->getDayElement()->setValueOptions($daysOptions);
        $monthElement  = $element->getMonthElement()->setValueOptions($monthsOptions);
        $yearElement   = $element->getYearElement()->setValueOptions($yearOptions);
        $hourElement   = $element->getHourElement()->setValueOptions($hourOptions);
        $minuteElement = $element->getMinuteElement()->setValueOptions($minuteOptions);
        $secondElement = $element->getSecondElement()->setValueOptions($secondOptions);

        if ($element->shouldCreateEmptyOption()) {
            $dayElement->setEmptyOption('');
            $yearElement->setEmptyOption('');
            $monthElement->setEmptyOption('');
            $hourElement->setEmptyOption('');
            $minuteElement->setEmptyOption('');
            $secondElement->setEmptyOption('');
        }

        $data = array();
        $data[$pattern['day']]    = $selectHelper->render($dayElement);
        $data[$pattern['month']]  = $selectHelper->render($monthElement);
        $data[$pattern['year']]   = $selectHelper->render($yearElement);
        $data[$pattern['hour']]   = $selectHelper->render($hourElement);
        $data[$pattern['minute']] = $selectHelper->render($minuteElement);

        if ($element->shouldShowSeconds()) {
            $data[$pattern['second']]  = $selectHelper->render($secondElement);
        } else {
            unset($pattern['second']);
            if ($shouldRenderDelimiters) {
                unset($pattern[4]);
            }
        }

        $markup = '';
        foreach ($pattern as $key => $value) {
            // Delimiter
            if (is_numeric($key)) {
                $markup .= $value;
            } else {
                $markup .= $data[$value];
            }
        }

        return trim($markup);
    }

    /**
     * @param  int $timeType
     * @return FormDateTimeSelect
     */
    public function setTimeType($timeType)
    {
        // The FULL format uses values that are not used
        if ($timeType === IntlDateFormatter::FULL) {
            $timeType = IntlDateFormatter::LONG;
        }

        $this->timeType = $timeType;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeType()
    {
        return $this->timeType;
    }

    /**
     * Override to also get time part
     *
     * @return string
     */
    public function getPattern()
    {
        if ($this->pattern === null) {
            $intl           = new IntlDateFormatter($this->getLocale(), $this->dateType, $this->timeType);
            // remove time zone format character
            $pattern = rtrim($intl->getPattern(), ' z');
            $this->pattern  = $pattern;
        }

        return $this->pattern;
    }

    /**
     * Parse the pattern
     *
     * @param  bool $renderDelimiters
     * @return array
     */
    protected function parsePattern($renderDelimiters = true)
    {
        $pattern    = $this->getPattern();
        $pregResult = preg_split("/([ -,.:\/]*'.*?'[ -,.:\/]*)|([ -,.:\/]+)/", $pattern, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $result = array();
        foreach ($pregResult as $value) {
            if (stripos($value, "'") === false && stripos($value, 'd') !== false) {
                $result['day'] = $value;
            } elseif (stripos($value, "'") === false && strpos($value, 'M') !== false) {
                $result['month'] = $value;
            } elseif (stripos($value, "'") === false && stripos($value, 'y') !== false) {
                $result['year'] = $value;
            } elseif (stripos($value, "'") === false && stripos($value, 'h') !==  false) {
                $result['hour'] = $value;
            } elseif (stripos($value, "'") === false && stripos($value, 'm') !== false) {
                $result['minute'] = $value;
            } elseif (stripos($value, "'") === false && strpos($value, 's') !== false) {
                $result['second'] = $value;
            } elseif (stripos($value, "'") === false && stripos($value, 'a') !== false) {
                // ignore ante/post meridiem marker
                continue;
            } elseif ($renderDelimiters) {
                $result[] = str_replace("'", '', $value);
            }
        }

        return $result;
    }

    /**
     * Create a key => value options for hours
     *
     * @param  string $pattern Pattern to use for hours
     * @return array
     */
    protected function getHoursOptions($pattern)
    {
        $keyFormatter   = new IntlDateFormatter($this->getLocale(), null, null, null, null, 'HH');
        $valueFormatter = new IntlDateFormatter($this->getLocale(), null, null, null, null, $pattern);
        $date           = new DateTime('1970-01-01 00:00:00');

        $result = array();
        for ($hour = 1; $hour <= 24; $hour++) {
            $key   = $keyFormatter->format($date);
            $value = $valueFormatter->format($date);
            $result[$key] = $value;

            $date->modify('+1 hour');
        }

        return $result;
    }

    /**
     * Create a key => value options for minutes
     *
     * @param  string $pattern Pattern to use for minutes
     * @return array
     */
    protected function getMinutesOptions($pattern)
    {
        $keyFormatter   = new IntlDateFormatter($this->getLocale(), null, null, null, null, 'mm');
        $valueFormatter = new IntlDateFormatter($this->getLocale(), null, null, null, null, $pattern);
        $date           = new DateTime('1970-01-01 00:00:00');

        $result = array();
        for ($min = 1; $min <= 60; $min++) {
            $key   = $keyFormatter->format($date);
            $value = $valueFormatter->format($date);
            $result[$key] = $value;

            $date->modify('+1 minute');
        }

        return $result;
    }

    /**
     * Create a key => value options for seconds
     *
     * @param  string $pattern Pattern to use for seconds
     * @return array
     */
    protected function getSecondsOptions($pattern)
    {
        $keyFormatter   = new IntlDateFormatter($this->getLocale(), null, null, null, null, 'ss');
        $valueFormatter = new IntlDateFormatter($this->getLocale(), null, null, null, null, $pattern);
        $date           = new DateTime('1970-01-01 00:00:00');

        $result = array();
        for ($sec = 1; $sec <= 60; $sec++) {
            $key   = $keyFormatter->format($date);
            $value = $valueFormatter->format($date);
            $result[$key] = $value;

            $date->modify('+1 second');
        }

        return $result;
    }
}
