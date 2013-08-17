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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Log Cron Backend Model
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Model_Config_Backend_Log_Cron extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH  = 'crontab/jobs/log_clean/schedule/cron_expr';
    const CRON_MODEL_PATH   = 'crontab/jobs/log_clean/run/model';

    /**
     * Cron settings after save
     *
     * @return Mage_Backend_Model_Config_Backend_Log_Cron
     */
    protected function _afterSave()
    {
        $enabled    = $this->getData('groups/log/fields/enabled/value');
        $time       = $this->getData('groups/log/fields/time/value');
        $frequncy   = $this->getData('groups/log/fields/frequency/value');

        $frequencyWeekly    = Mage_Cron_Model_Config_Source_Frequency::CRON_WEEKLY;
        $frequencyMonthly   = Mage_Cron_Model_Config_Source_Frequency::CRON_MONTHLY;

        if ($enabled) {
            $cronExprArray = array(
                intval($time[1]),                                   # Minute
                intval($time[0]),                                   # Hour
                ($frequncy == $frequencyMonthly) ? '1' : '*',       # Day of the Month
                '*',                                                # Month of the Year
                ($frequncy == $frequencyWeekly) ? '1' : '*',        # Day of the Week
            );
            $cronExprString = join(' ', $cronExprArray);
        } else {
            $cronExprString = '';
        }

        try {
            Mage::getModel('Mage_Core_Model_Config_Data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();

            Mage::getModel('Mage_Core_Model_Config_Data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        }
        catch (Exception $e) {
            Mage::throwException(Mage::helper('Mage_Backend_Helper_Data')->__('We can\'t save the Cron expression.'));
        }
    }
}
