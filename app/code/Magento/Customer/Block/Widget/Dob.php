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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Block\Widget;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Service\ArrayObjectSearch;

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
    protected $_dateInputs = array();

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/dob.phtml');
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $attributeMetadata = $this->_getAttribute('dob');
        return $attributeMetadata ? (bool)$attributeMetadata->isVisible() : false;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        $attributeMetadata = $this->_getAttribute('dob');
        return $attributeMetadata ? (bool)$attributeMetadata->isRequired() : false;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->setTime($date ? strtotime($date) : false);
        $this->setData('date', $date);
        return $this;
    }

    /**
     * @return string|bool
     */
    public function getDay()
    {
        return $this->getTime() ? date('d', $this->getTime()) : '';
    }

    /**
     * @return string|bool
     */
    public function getMonth()
    {
        return $this->getTime() ? date('m', $this->getTime()) : '';
    }

    /**
     * @return string|bool
     */
    public function getYear()
    {
        return $this->getTime() ? date('Y', $this->getTime()) : '';
    }

    /**
     * Returns format which will be applied for DOB in javascript
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->_localeDate->getDateFormat(TimezoneInterface::FORMAT_TYPE_SHORT);
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
        $mapping = array();
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
        if (!is_null($dob)) {
            $rules = $this->_getAttribute('dob')->getValidationRules();
            $minDateValue = ArrayObjectSearch::getArrayElementByName(
                $rules,
                self::MIN_DATE_RANGE_KEY
            );
            if (!is_null($minDateValue)) {
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
        if (!is_null($dob)) {
            $rules = $this->_getAttribute('dob')->getValidationRules();
            $maxDateValue = ArrayObjectSearch::getArrayElementByName(
                $rules,
                self::MAX_DATE_RANGE_KEY
            );
            if (!is_null($maxDateValue)) {
                return date("Y/m/d", $maxDateValue);
            }
        }
        return null;
    }
}
