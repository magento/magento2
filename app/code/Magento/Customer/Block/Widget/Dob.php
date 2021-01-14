<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Api\ArrayObjectSearch;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Customer date of birth attribute block
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Dob extends AbstractWidget
{
    /**
     * Constants for borders of date-type customer attributes
     */
    const MIN_DATE_RANGE_KEY = 'date_range_min';

    const MAX_DATE_RANGE_KEY = 'date_range_max';

    /**
     * Date inputs
     *
     * @var array
     */
    protected $_dateInputs = [];

    /**
     * @var \Magento\Framework\View\Element\Html\Date
     */
    protected $dateElement;

    /**
     * @var \Magento\Framework\Data\Form\FilterFactory
     */
    protected $filterFactory;

    /**
     * JSON Encoder
     *
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param \Magento\Framework\View\Element\Html\Date $dateElement
     * @param \Magento\Framework\Data\Form\FilterFactory $filterFactory
     * @param array $data
     * @param EncoderInterface|null $encoder
     * @param ResolverInterface|null $localeResolver
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        \Magento\Framework\View\Element\Html\Date $dateElement,
        \Magento\Framework\Data\Form\FilterFactory $filterFactory,
        array $data = [],
        ?EncoderInterface $encoder = null,
        ?ResolverInterface $localeResolver = null
    ) {
        $this->dateElement = $dateElement;
        $this->filterFactory = $filterFactory;
        $this->encoder = $encoder ?? ObjectManager::getInstance()->get(EncoderInterface::class);
        $this->localeResolver = $localeResolver ?? ObjectManager::getInstance()->get(ResolverInterface::class);
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
    }

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Customer::widget/dob.phtml');
    }

    /**
     * Check if dob attribute enabled in system
     *
     * @return bool
     */
    public function isEnabled()
    {
        $attributeMetadata = $this->_getAttribute('dob');
        return $attributeMetadata ? (bool)$attributeMetadata->isVisible() : false;
    }

    /**
     * Check if dob attribute marked as required
     *
     * @return bool
     */
    public function isRequired()
    {
        $attributeMetadata = $this->_getAttribute('dob');
        return $attributeMetadata ? (bool)$attributeMetadata->isRequired() : false;
    }

    /**
     * Set date
     *
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->setTime($this->filterTime($date));
        $this->setValue($this->applyOutputFilter($date));
        return $this;
    }

    /**
     * Sanitizes time
     *
     * @param mixed $value
     * @return bool|int
     */
    private function filterTime($value)
    {
        $time = false;
        if ($value) {
            if ($value instanceof \DateTimeInterface) {
                $time =  $value->getTimestamp();
            } elseif (is_numeric($value)) {
                $time = $value;
            } elseif (is_string($value)) {
                $time = strtotime($value);
                $time = $time === false ? $this->_localeDate->date($value, null, false, false)->getTimestamp() : $time;
            }
        }

        return $time;
    }

    /**
     * Return Data Form Filter or false
     *
     * @return \Magento\Framework\Data\Form\Filter\FilterInterface|false
     */
    protected function getFormFilter()
    {
        $attributeMetadata = $this->_getAttribute('dob');
        $filterCode = $attributeMetadata->getInputFilter();
        if ($filterCode) {
            $data = [];
            if ($filterCode == 'date') {
                $data['format'] = $this->getDateFormat();
            }
            $filter = $this->filterFactory->create($filterCode, $data);
            return $filter;
        }
        return false;
    }

    /**
     * Apply output filter to value
     *
     * @param string $value
     * @return string
     */
    protected function applyOutputFilter($value)
    {
        $filter = $this->getFormFilter();
        if ($filter && $value) {
            $value = date('Y-m-d', $this->getTime());
            $value = $filter->outputFilter($value);
        }
        return $value;
    }

    /**
     * Get day
     *
     * @return string|bool
     */
    public function getDay()
    {
        return $this->getTime() ? date('d', $this->getTime()) : '';
    }

    /**
     * Get month
     *
     * @return string|bool
     */
    public function getMonth()
    {
        return $this->getTime() ? date('m', $this->getTime()) : '';
    }

    /**
     * Get year
     *
     * @return string|bool
     */
    public function getYear()
    {
        return $this->getTime() ? date('Y', $this->getTime()) : '';
    }

    /**
     * Return label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Date of Birth');
    }

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode
     *
     * @return string
     */
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Create correct date field
     *
     * @return string
     */
    public function getFieldHtml()
    {
        $this->dateElement->setData(
            [
                'extra_params' => $this->getHtmlExtraParams(),
                'name' => $this->getHtmlId(),
                'id' => $this->getHtmlId(),
                'class' => $this->getHtmlClass(),
                'value' => $this->getValue(),
                'date_format' => $this->getDateFormat(),
                'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                'years_range' => '-120y:c+nn',
                'max_date' => '-1d',
                'change_month' => 'true',
                'change_year' => 'true',
                'show_on' => 'both',
                'first_day' => $this->getFirstDay()
            ]
        );
        return $this->dateElement->getHtml();
    }

    /**
     * Return id
     *
     * @return string
     */
    public function getHtmlId()
    {
        return 'dob';
    }

    /**
     * Return data-validate rules
     *
     * @return string
     */
    public function getHtmlExtraParams()
    {
        $validators = [];
        if ($this->isRequired()) {
            $validators['required'] = true;
        }
        $validators['validate-date'] = [
            'dateFormat' => $this->getDateFormat()
        ];
        $validators['validate-dob'] = [
            'dateFormat' => $this->getDateFormat()
        ];

        return 'data-validate="' . $this->_escaper->escapeHtml(json_encode($validators)) . '"';
    }

    /**
     * Returns format which will be applied for DOB in javascript
     *
     * @return string
     */
    public function getDateFormat()
    {
        $dateFormat = $this->setTwoDayPlaces($this->_localeDate->getDateFormatWithLongYear());
        /** Escape RTL characters which are present in some locales and corrupt formatting */
        $escapedDateFormat = preg_replace('/[^MmDdYy\/\.\-]/', '', $dateFormat);

        return $escapedDateFormat;
    }

    /**
     * Add date input html
     *
     * @param string $code
     * @param string $html
     * @return void
     */
    public function setDateInput($code, $html)
    {
        $this->_dateInputs[$code] = $html;
    }

    /**
     * Sort date inputs by dateformat order of current locale
     *
     * @param bool $stripNonInputChars
     *
     * @return string
     */
    public function getSortedDateInputs($stripNonInputChars = true)
    {
        $mapping = [];
        if ($stripNonInputChars) {
            $mapping['/[^medy]/i'] = '\\1';
        }
        $mapping['/m{1,5}/i'] = '%1$s';
        $mapping['/e{1,5}/i'] = '%2$s';
        $mapping['/d{1,5}/i'] = '%2$s';
        $mapping['/y{1,5}/i'] = '%3$s';

        $dateFormat = preg_replace(array_keys($mapping), array_values($mapping), $this->getDateFormat());

        return sprintf($dateFormat, $this->_dateInputs['m'], $this->_dateInputs['d'], $this->_dateInputs['y']);
    }

    /**
     * Return minimal date range value
     *
     * @return string|null
     */
    public function getMinDateRange()
    {
        $dob = $this->_getAttribute('dob');
        if ($dob !== null) {
            $rules = $this->_getAttribute('dob')->getValidationRules();
            $minDateValue = ArrayObjectSearch::getArrayElementByName(
                $rules,
                self::MIN_DATE_RANGE_KEY
            );
            if ($minDateValue !== null) {
                return date("Y/m/d", $minDateValue);
            }
        }
        return null;
    }

    /**
     * Return maximal date range value
     *
     * @return string|null
     */
    public function getMaxDateRange()
    {
        $dob = $this->_getAttribute('dob');
        if ($dob !== null) {
            $rules = $this->_getAttribute('dob')->getValidationRules();
            $maxDateValue = ArrayObjectSearch::getArrayElementByName(
                $rules,
                self::MAX_DATE_RANGE_KEY
            );
            if ($maxDateValue !== null) {
                return date("Y/m/d", $maxDateValue);
            }
        }
        return null;
    }

    /**
     * Return first day of the week
     *
     * @return int
     */
    public function getFirstDay()
    {
        return (int)$this->_scopeConfig->getValue(
            'general/locale/firstday',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get translated calendar config json formatted
     *
     * @return string
     */
    public function getTranslatedCalendarConfigJson(): string
    {
        $localeData = (new DataBundle())->get($this->localeResolver->getLocale());
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];
        $daysData = $localeData['calendar']['gregorian']['dayNames'];

        return $this->encoder->encode(
            [
                'closeText' => __('Done'),
                'prevText' => __('Prev'),
                'nextText' => __('Next'),
                'currentText' => __('Today'),
                'monthNames' => array_values(iterator_to_array($monthsData['format']['wide'])),
                'monthNamesShort' => array_values(iterator_to_array($monthsData['format']['abbreviated'])),
                'dayNames' => array_values(iterator_to_array($daysData['format']['wide'])),
                'dayNamesShort' => array_values(iterator_to_array($daysData['format']['abbreviated'])),
                'dayNamesMin' => array_values(iterator_to_array($daysData['format']['short'])),
            ]
        );
    }

    /**
     * Set 2 places for day value in format string
     *
     * @param string $format
     * @return string
     */
    private function setTwoDayPlaces(string $format): string
    {
        return preg_replace(
            '/(?<!d)d(?!d)/',
            'dd',
            $format
        );
    }
}
