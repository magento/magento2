<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Date grid column filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @todo        date format
 */
class Datetime extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Date
{
    /**
     * full day is 86400, we need 23 hours:59 minutes:59 seconds = 86399
     */
    const END_OF_DAY_IN_SECONDS = 86399;

    /**
     * {@inheritdoc}
     */
    public function getValue($index = null)
    {
        if ($index) {
            if ($data = $this->getData('value', 'orig_' . $index)) {
                // date('Y-m-d', strtotime($data));
                return $data;
            }
            return null;
        }
        $value = $this->getData('value');
        if (is_array($value)) {
            $value['datetime'] = true;
        }
        if (!empty($value['to']) && !$this->getColumn()->getFilterTime()) {
            $datetimeTo = $value['to'];

            //calculate end date considering timezone specification
            $datetimeTo->setTimezone(
                $this->_scopeConfig->getValue(
                    $this->_localeDate->getDefaultTimezonePath(),
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
            $datetimeTo->addDay(1)->subSecond(1);
            $datetimeTo->setTimezone(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::DEFAULT_TIMEZONE);
        }
        return $value;
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
        if ($this->getColumn()->getFilterTime()) {
            try {
                $dateObj = $this->_localeDate->date(null, null, $locale, false);

                //set default timezone for store (admin)
                $dateObj->setTimezone(
                    $this->_scopeConfig->getValue(
                        $this->_localeDate->getDefaultTimezonePath(),
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );

                //set date with applying timezone of store
                $dateObj->set(
                    $date,
                    $this->_localeDate->getDateTimeFormat(
                        \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
                    ),
                    $locale
                );

                //convert store date to default date in UTC timezone without DST
                $dateObj->setTimezone(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::DEFAULT_TIMEZONE);

                return $dateObj;
            } catch (\Exception $e) {
                return null;
            }
        }
        return parent::_convertDate($date, $locale);
    }

    /**
     * Render filter html
     *
     * @return string
     */
    public function getHtml()
    {
        $htmlId = $this->mathRandom->getUniqueHash($this->_getHtmlId());
        $format = $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);
        $timeFormat = '';

        if ($this->getColumn()->getFilterTime()) {
            $timeFormat = $this->_localeDate->getTimeFormat(
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
            );
        }

        $html =
            '<div class="range" id="' . $htmlId . '_range"><div class="range-line date">' . '<input type="text" name="'
            . $this->_getHtmlName() . '[from]" id="' . $htmlId . '_from"' . ' value="' . $this->getEscapedValue('from')
            . '" class="input-text no-changes" placeholder="' . __(
                'From'
            ) . '" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'from'
            ) . '/>' . '</div>';
        $html .= '<div class="range-line date">' . '<input type="text" name="' . $this->_getHtmlName() . '[to]" id="'
            . $htmlId . '_to"' . ' value="' . $this->getEscapedValue(
                'to'
            ) . '" class="input-text no-changes" placeholder="' . __(
                'To'
            ) . '" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'to'
            ) . '/>' . '</div></div>';
        $html .= '<input type="hidden" name="' . $this->_getHtmlName() . '[locale]"' . ' value="'
            . $this->_localeResolver->getLocaleCode() . '"/>';
        $html .= '<script type="text/javascript">
            require(["jquery", "mage/calendar"],function($){
                    $("#' . $htmlId . '_range").dateRange({
                        dateFormat: "' . $format . '",
                        timeFormat: "' . $timeFormat . '",
                        showsTime: ' . ($this->getColumn()->getFilterTime() ? 'true' : 'false') . ',
                        buttonImage: "' . $this->getViewFileUrl('images/grid-cal.gif') . '",
                        buttonText: "' . $this->escapeHtml(__('Date selector')) . '",
                        from: {
                            id: "' . $htmlId . '_from"
                        },
                        to: {
                            id: "' . $htmlId . '_to"
                        }
                    })
            });
        </script>';
        return $html;
    }

    /**
     * Return escaped value for calendar
     *
     * @param string $index
     * @return string
     */
    public function getEscapedValue($index = null)
    {
        if ($this->getColumn()->getFilterTime()) {
            $value = $this->getValue($index);
            if ($value instanceof \Zend_Date) {
                return $value->toString(
                    $this->_localeDate->getDateTimeFormat(
                        \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT
                    )
                );
            }
            return $value;
        }

        return parent::getEscapedValue($index);
    }
}
