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
 * @category    Mage
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Reports data helper
 */
class Mage_Reports_Helper_Data extends Mage_Core_Helper_Abstract
{
    const REPORT_PERIOD_TYPE_DAY    = 'day';
    const REPORT_PERIOD_TYPE_MONTH  = 'month';
    const REPORT_PERIOD_TYPE_YEAR   = 'year';
    /**
     * Retrieve array of intervals
     *
     * @param string $from
     * @param string $to
     * @param string $period
     * @return array
     */
    public function getIntervals($from, $to, $period = self::REPORT_PERIOD_TYPE_DAY)
    {
        $intervals = array();
        if (!$from && !$to){
            return $intervals;
        }

        $start = new Zend_Date($from, Varien_Date::DATE_INTERNAL_FORMAT);

        if ($period == self::REPORT_PERIOD_TYPE_DAY) {
            $dateStart = $start;
        }

        if ($period == self::REPORT_PERIOD_TYPE_MONTH) {
            $dateStart = new Zend_Date(date("Y-m", $start->getTimestamp()), Varien_Date::DATE_INTERNAL_FORMAT);
        }

        if ($period == self::REPORT_PERIOD_TYPE_YEAR) {
            $dateStart = new Zend_Date(date("Y", $start->getTimestamp()), Varien_Date::DATE_INTERNAL_FORMAT);
        }

        $dateEnd = new Zend_Date($to, Varien_Date::DATE_INTERNAL_FORMAT);

        while ($dateStart->compare($dateEnd) <= 0) {
            switch ($period) {
                case self::REPORT_PERIOD_TYPE_DAY :
                    $t = $dateStart->toString('yyyy-MM-dd');
                    $dateStart->addDay(1);
                    break;
                case self::REPORT_PERIOD_TYPE_MONTH:
                    $t = $dateStart->toString('yyyy-MM');
                    $dateStart->addMonth(1);
                    break;
                case self::REPORT_PERIOD_TYPE_YEAR:
                    $t = $dateStart->toString('yyyy');
                    $dateStart->addYear(1);
                    break;
            }
            $intervals[] = $t;
        }
        return  $intervals;
    }

    public function prepareIntervalsCollection($collection, $from, $to, $periodType = self::REPORT_PERIOD_TYPE_DAY)
    {
        $intervals = $this->getIntervals($from, $to, $periodType);

        foreach ($intervals as $interval) {
            $item = Mage::getModel('Mage_Adminhtml_Model_Report_Item');
            $item->setPeriod($interval);
            $item->setIsEmpty();
            $collection->addItem($item);
        }
    }
}

