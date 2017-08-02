<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Html;

use Magento\Framework\Locale\Bundle\DataBundle;

/**
 * Calendar block for page header
 *
 * Prepares localization data for calendar
 *
 * @api
 * @since 2.0.0
 */
class Calendar extends \Magento\Framework\View\Element\Template
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.0.0
     */
    protected $_date;

    /**
     * JSON Encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     * @since 2.0.0
     */
    protected $encoder;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $_localeResolver;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Json\EncoderInterface $encoder
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Json\EncoderInterface $encoder,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->_date = $date;
        $this->encoder = $encoder;
        $this->_localeResolver = $localeResolver;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $localeData = (new DataBundle())->get($this->_localeResolver->getLocale());

        // get days names
        $daysData = $localeData['calendar']['gregorian']['dayNames'];
        $this->assign(
            'days',
            [
                'wide' => $this->encoder->encode(array_values(iterator_to_array($daysData['format']['wide']))),
                'abbreviated' => $this->encoder->encode(
                    array_values(iterator_to_array($daysData['format']['abbreviated']))
                ),
            ]
        );

        /**
         * Month names in abbreviated format values was added to ICU Data tables
         * starting ICU library version 52.1. For some OS, like CentOS, default
         * installation version of ICU library is 50.1.2, which not contain
         * 'abbreviated' key, and that may cause a PHP fatal error when passing
         * as an argument of function 'iterator_to_array'. This issue affects
         * locales like ja_JP, ko_KR etc.
         *
         * @see http://source.icu-project.org/repos/icu/tags/release-50-1-2/icu4c/source/data/locales/ja.txt
         * @see http://source.icu-project.org/repos/icu/tags/release-52-1/icu4c/source/data/locales/ja.txt
         * @var \ResourceBundle $monthsData
         */
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];
        $this->assign(
            'months',
            [
                'wide' => $this->encoder->encode(array_values(iterator_to_array($monthsData['format']['wide']))),
                'abbreviated' => $this->encoder->encode(
                    array_values(
                        iterator_to_array(
                            null !== $monthsData->get('format')->get('abbreviated')
                            ? $monthsData['format']['abbreviated']
                            : $monthsData['format']['wide']
                        )
                    )
                ),
            ]
        );

        // get "today" and "week" words
        $this->assign('today', $this->encoder->encode($localeData['fields']['day']['relative']['0']));
        $this->assign('week', $this->encoder->encode($localeData['fields']['week']['dn']));

        // get "am" & "pm" words
        $this->assign('am', $this->encoder->encode($localeData['calendar']['gregorian']['AmPmMarkers']['0']));
        $this->assign('pm', $this->encoder->encode($localeData['calendar']['gregorian']['AmPmMarkers']['1']));

        // get first day of week and weekend days
        $this->assign(
            'firstDay',
            (int)$this->_scopeConfig->getValue(
                'general/locale/firstday',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $this->assign(
            'weekendDays',
            $this->encoder->encode(
                (string)$this->_scopeConfig->getValue(
                    'general/locale/weekend',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )
        );

        // define default format and tooltip format
        $this->assign(
            'defaultFormat',
            $this->encoder->encode(
                $this->_localeDate->getDateFormat(\IntlDateFormatter::MEDIUM)
            )
        );
        $this->assign(
            'toolTipFormat',
            $this->encoder->encode(
                $this->_localeDate->getDateFormat(\IntlDateFormatter::LONG)
            )
        );

        // get days and months for en_US locale - calendar will parse exactly in this locale
        $englishMonths = (new DataBundle())->get('en_US')['calendar']['gregorian']['monthNames'];
        $enUS = new \stdClass();
        $enUS->m = new \stdClass();
        $enUS->m->wide = array_values(iterator_to_array($englishMonths['format']['wide']));
        $enUS->m->abbr = array_values(iterator_to_array($englishMonths['format']['abbreviated']));
        $this->assign('enUS', $this->encoder->encode($enUS));

        return parent::_toHtml();
    }

    /**
     * Return offset of current timezone with GMT in seconds
     *
     * @return int
     * @since 2.0.0
     */
    public function getTimezoneOffsetSeconds()
    {
        return $this->_date->getGmtOffset();
    }

    /**
     * Getter for store timestamp based on store timezone settings
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return int
     * @since 2.0.0
     */
    public function getStoreTimestamp($store = null)
    {
        return $this->_localeDate->scopeTimeStamp($store);
    }

    /**
     * Getter for yearRange option in datepicker
     *
     * @return string
     * @since 2.0.0
     */
    public function getYearRange()
    {
        return (new \DateTime())->modify('- 100 years')->format('Y')
            . ':' . (new \DateTime())->modify('+ 100 years')->format('Y');
    }
}
