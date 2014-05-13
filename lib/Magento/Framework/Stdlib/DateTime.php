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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Stdlib;

/**
 * Converter of date formats
 * Internal dates
 */
class DateTime
{
    /**#@+
     * Date format, used as default. Compatible with \Zend_Date
     */
    const DATETIME_INTERNAL_FORMAT = 'yyyy-MM-dd HH:mm:ss';

    const DATE_INTERNAL_FORMAT = 'yyyy-MM-dd';

    const DATETIME_PHP_FORMAT = 'Y-m-d H:i:s';

    const DATE_PHP_FORMAT = 'Y-m-d';

    /**#@-*/

    /**
     * Minimum allowed year value
     */
    const YEAR_MIN_VALUE = -10000;

    /**
     * Maximum allowed year value
     */
    const YEAR_MAX_VALUE = 10000;

    /**
     * Convert date to UNIX timestamp
     * Returns current UNIX timestamp if date is true
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateInterface|bool $date
     * @return int
     */
    public function toTimestamp($date)
    {
        if ($date instanceof \Magento\Framework\Stdlib\DateTime\DateInterface) {
            return $date->getTimestamp();
        }

        if ($date === true) {
            return time();
        }

        return strtotime($date);
    }

    /**
     * Retrieve current date in internal format
     *
     * @param boolean $withoutTime day only flag
     * @return string
     */
    public function now($withoutTime = false)
    {
        $format = $withoutTime ? self::DATE_PHP_FORMAT : self::DATETIME_PHP_FORMAT;
        return date($format);
    }

    /**
     * Format date to internal format
     *
     * @param string|\Zend_Date|bool|null $date
     * @param boolean $includeTime
     * @return string|null
     */
    public function formatDate($date, $includeTime = true)
    {
        if ($date === true) {
            return $this->now(!$includeTime);
        }

        if ($date instanceof \Magento\Framework\Stdlib\DateTime\DateInterface) {
            if ($includeTime) {
                return $date->toString(self::DATETIME_INTERNAL_FORMAT);
            } else {
                return $date->toString(self::DATE_INTERNAL_FORMAT);
            }
        }

        if (empty($date)) {
            return null;
        }

        if (!is_numeric($date)) {
            $date = $this->toTimestamp($date);
        }

        $format = $includeTime ? self::DATETIME_PHP_FORMAT : self::DATE_PHP_FORMAT;
        return date($format, $date);
    }

    /**
     * Check whether sql date is empty
     *
     * @param string $date
     * @return boolean
     */
    public function isEmptyDate($date)
    {
        return preg_replace('#[ 0:-]#', '', $date) === '';
    }
}
