<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Date grid column filter
 */
class Date extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->mathRandom = $mathRandom;
        $this->_localeResolver = $localeResolver;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $htmlId = $this->mathRandom->getUniqueHash($this->_getHtmlId());
        $format = $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);
        $html = '<div class="range" id="' .
            $htmlId .
            '_range"><div class="range-line date">' .
            '<input type="text" name="' .
            $this->_getHtmlName() .
            '[from]" id="' .
            $htmlId .
            '_from"' .
            ' value="' .
            $this->getEscapedValue(
                'from'
            ) . '" class="input-text no-changes" placeholder="' . __(
                'From'
            ) . '" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'from'
            ) . '/>' . '</div>';
        $html .= '<div class="range-line date">' .
            '<input type="text" name="' .
            $this->_getHtmlName() .
            '[to]" id="' .
            $htmlId .
            '_to"' .
            ' value="' .
            $this->getEscapedValue(
                'to'
            ) . '" class="input-text no-changes" placeholder="' . __(
                'To'
            ) . '" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'to'
            ) . '/>' . '</div></div>';
        $html .= '<input type="hidden" name="' .
            $this->_getHtmlName() .
            '[locale]"' .
            ' value="' .
            $this->_localeResolver->getLocaleCode() .
            '"/>';
        $html .= '<script type="text/javascript">
            require(["jquery", "mage/calendar"], function($){
                $("#' .
            $htmlId .
            '_range").dateRange({
                    dateFormat: "' .
            $format .
            '",
                    buttonImage: "' .
            $this->getViewFileUrl(
                'images/grid-cal.gif'
            ) . '",
                        buttonText: "' . $this->escapeHtml(__('Date selector')) .
            '",
                    from: {
                        id: "' .
            $htmlId .
            '_from"
                    },
                    to: {
                        id: "' .
            $htmlId .
            '_to"
                    }
                })
            });
        </script>';
        return $html;
    }

    /**
     * @param string|null $index
     * @return string
     */
    public function getEscapedValue($index = null)
    {
        $value = $this->getValue($index);
        if ($value instanceof \Zend_Date) {
            return $value->toString(
                $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT)
            );
        }
        return $value;
    }

    /**
     * @param string|null $index
     * @return array|string|int|float|null
     */
    public function getValue($index = null)
    {
        if ($index) {
            if ($data = $this->getData('value', 'orig_' . $index)) {
                //date('Y-m-d', strtotime($data));
                return $data;
            }
            return null;
        }
        $value = $this->getData('value');
        if (is_array($value)) {
            $value['date'] = true;
        }
        return $value;
    }

    /**
     * @return array|string|int|float|null
     */
    public function getCondition()
    {
        $value = $this->getValue();

        return $value;
    }

    /**
     * @param array|string|int|float $value
     * @return $this
     */
    public function setValue($value)
    {
        if (isset($value['locale'])) {
            if (!empty($value['from'])) {
                $value['orig_from'] = $value['from'];
                $value['from'] = $this->_convertDate($value['from'], $value['locale']);
            }
            if (!empty($value['to'])) {
                $value['orig_to'] = $value['to'];
                $value['to'] = $this->_convertDate($value['to'], $value['locale']);
            }
        }
        if (empty($value['from']) && empty($value['to'])) {
            $value = null;
        }
        $this->setData('value', $value);
        return $this;
    }

    /**
     * Convert given date to default (UTC) timezone
     *
     * @param string $date
     * @param string $locale
     * @return \Magento\Framework\Stdlib\DateTime\Date|null
     */
    protected function _convertDate($date, $locale)
    {
        try {
            $dateObj = $this->_localeDate->date(null, null, $locale, false);

            //set default timezone for store (admin)
            $dateObj->setTimezone(
                $this->_scopeConfig->getValue(
                    $this->_localeDate->getDefaultTimezonePath(),
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );

            //set beginning of day
            $dateObj->setHour(00);
            $dateObj->setMinute(00);
            $dateObj->setSecond(00);

            //set date with applying timezone of store
            $dateObj->set($date, \Zend_Date::DATE_SHORT, $locale);

            //convert store date to default date in UTC timezone without DST
            $dateObj->setTimezone(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::DEFAULT_TIMEZONE);

            return $dateObj;
        } catch (\Exception $e) {
            return null;
        }
    }
}
