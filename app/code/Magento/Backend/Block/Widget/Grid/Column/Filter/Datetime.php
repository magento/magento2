<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Date grid column filter
 *
 * @todo        date format
 */
class Datetime extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Date
{
    /**
     * full day is 86400, we need 23 hours:59 minutes:59 seconds = 86399
     */
    public const END_OF_DAY_IN_SECONDS = 86399;

    /**
     * @inheritdoc
     */
    public function getValue($index = null)
    {
        if ($index) {
            if ($data = $this->getData('value', 'orig_' . $index)) {
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
            /** @var $datetimeTo \DateTime */
            $datetimeTo->setTimezone(
                new \DateTimeZone(
                    $this->_scopeConfig->getValue(
                        $this->_localeDate->getDefaultTimezonePath(),
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                )
            );
            $datetimeTo->modify('+1 day')->modify('-1 second');
            $datetimeTo->setTimezone(
                new \DateTimeZone('UTC')
            );
        }
        return $value;
    }

    /**
     * Convert given date to default (UTC) timezone
     *
     * @param string $date
     * @return \DateTime|null
     */
    protected function _convertDate($date)
    {
        if ($this->getColumn()->getFilterTime()) {
            try {
                $timezone = $this->getColumn()->getTimezone() !== false
                    ? $this->_localeDate->getConfigTimezone() : 'UTC';
                $adminTimeZone = new \DateTimeZone($timezone);
                $simpleRes = new \DateTime($date, $adminTimeZone);
                $simpleRes->setTimezone(new \DateTimeZone('UTC'));
                return $simpleRes;
            } catch (\Exception $e) {
                return null;
            }
        }
        return parent::_convertDate($date);
    }

    /**
     * Render filter html
     *
     * @return string
     */
    public function getHtml()
    {
        $htmlId = $this->mathRandom->getUniqueHash($this->_getHtmlId());
        $format = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $timeFormat = '';

        if ($this->getColumn()->getFilterTime()) {
            $timeFormat = $this->_localeDate->getTimeFormat(
                \IntlDateFormatter::SHORT
            );
        }

        $html =
            '<div class="range" id="' . $htmlId . '_range"><div class="range-line date">' . '<input type="text" name="'
            . $this->_getHtmlName() . '[from]" id="' . $htmlId . '_from"' . ' value="' . $this->getEscapedValue('from')
            . '" class="input-text admin__control-text no-changes" placeholder="' . __(
                'From'
            ) . '" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'from'
            ) . '/>' . '</div>';
        $html .= '<div class="range-line date">' . '<input type="text" name="' . $this->_getHtmlName() . '[to]" id="'
            . $htmlId . '_to"' . ' value="' . $this->getEscapedValue(
                'to'
            ) . '" class="input-text admin__control-text no-changes" placeholder="' . __(
                'To'
            ) . '" ' . $this->getUiId(
                'filter',
                $this->_getHtmlName(),
                'to'
            ) . '/>' . '</div></div>';
        $html .= '<input type="hidden" name="' . $this->_getHtmlName() . '[locale]"' . ' value="'
            . $this->localeResolver->getLocale() . '"/>';
        $scriptString = 'require(["jquery", "mage/calendar"],function($){
                    $("#' . $htmlId . '_range").dateRange({
                        dateFormat: "' . $format . '",
                        timeFormat: "' . $timeFormat . '",
                        showsTime: ' . ($this->getColumn()->getFilterTime() ? 'true' : 'false') . ',
                        buttonText: "' . $this->escapeHtml(__('Date selector')) . '",
                        buttonImage: "' . $this->getViewFileUrl('Magento_Theme::calendar.png') . '",
                        from: {
                            id: "' . $htmlId . '_from"
                        },
                        to: {
                            id: "' . $htmlId . '_to"
                        }
                    })
            });';
        $html .= $this->secureHtmlRenderer->renderTag('script', [], $scriptString, false);

        return $html;
    }

    /**
     * Return escaped value for calendar
     *
     * @param string|null $index
     * @return array|string|int|float|null
     */
    public function getEscapedValue($index = null)
    {
        if ($this->getColumn()->getFilterTime()) {
            $value = $this->getValue($index);
            if ($value instanceof \DateTimeInterface) {
                return $this->_localeDate->formatDateTime($value);
            }

            if (is_string($value)) {
                return $this->escapeHtml($value);
            }

            return $value;
        }

        return parent::getEscapedValue($index);
    }
}
