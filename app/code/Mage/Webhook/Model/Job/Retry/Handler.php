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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Webhook_Model_Job_Retry_Handler
{
    private static $RETRY_TIME_TO_ADD = array(
        1 => 1,
        2 => 2,
        3 => 4,
        4 => 10,
        5 => 30,
        6 => 60,
        7 => 120,
        8 => 240,
    );

    /**
     * Handles job dispatch failures.
     * @param Mage_Webhook_Model_Job_Interface $job
     */
    public function handleFailure(Mage_Webhook_Model_Job_Interface $job)
    {
        $retryCount = $job->getRetryCount() + 1;
        if ($retryCount < count(self::$RETRY_TIME_TO_ADD) + 1) {
            $addedTimeInMinutes = self::$RETRY_TIME_TO_ADD[$retryCount] * 60 + time();
            $job->setRetryCount($retryCount);
            $job->setRetryAt(Varien_Date::formatDate($addedTimeInMinutes, true));
            $job->setUpdatedAt(Varien_Date::formatDate(time(), true));
            $job->setStatus(Mage_Webhook_Model_Dispatch_Job::RETRY);
        } else {
            $job->setStatus(Mage_Webhook_Model_Dispatch_Job::FAILED);
        }
    }
}
