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
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Block\Widget;

class Dob extends \Magento\Customer\Block\Widget\AbstractWidget
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

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/dob.phtml');
    }

    public function isEnabled()
    {
        $attributeMetadata = $this->_getAttribute('dob');
        return $attributeMetadata ? (bool)$attributeMetadata->isVisible() : false;
    }

    public function isRequired()
    {
        $attributeMetadata = $this->_getAttribute('dob');
        return $attributeMetadata ? (bool)$attributeMetadata->isRequired() : false;
    }

    public function setDate($date)
    {
        $this->setTime($date ? strtotime($date) : false);
        $this->setData('date', $date);
        return $this;
    }

    public function getDay()
    {
        return $this->getTime() ? date('d', $this->getTime()) : '';
    }

    public function getMonth()
    {
        return $this->getTime() ? date('m', $this->getTime()) : '';
    }

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
        return $this->_locale->getDateFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT);
    }

    /**
     * Add date input html
     *
     * @param string $code
     * @param string $html
     */
    public function setDateInput($code, $html)
    {
        $this->_dateInputs[$code] = $html;
    }

    /**
     * Sort date inputs by dateformat order of current locale
     *
     * @return string
     */
    public function getSortedDateInputs()
    {
        $mapping = array(
            '/[^medy]/i' => '\\1',
            '/m{1,5}/i' => '%1$s',
            '/e{1,5}/i' => '%2$s',
            '/d{1,5}/i' => '%2$s',
            '/y{1,5}/i' => '%3$s',
        );

        $dateFormat = preg_replace(
            array_keys($mapping),
            array_values($mapping),
            $this->getDateFormat()
        );

        return sprintf($dateFormat,
            $this->_dateInputs['m'], $this->_dateInputs['d'], $this->_dateInputs['y']);
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
            if (isset($rules[self::MIN_DATE_RANGE_KEY])) {
                return date("Y/m/d", $rules[self::MIN_DATE_RANGE_KEY]);
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
            if (isset($rules[self::MAX_DATE_RANGE_KEY])) {
                return date("Y/m/d", $rules[self::MAX_DATE_RANGE_KEY]);
            }
        }
        return null;
    }
}
