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
 * @package     Magento_Payment
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Payment\Model\Recurring\Profile;

/**
 * Recurring profile gateway management interface
 */
interface MethodInterface
{
    /**
     * Validate data
     *
     * @param \Magento\RecurringProfile\Model\RecurringProfile $profile
     * @throws \Magento\Core\Exception
     */
    public function validateRecurringProfile(\Magento\RecurringProfile\Model\RecurringProfile $profile);

    /**
     * Submit to the gateway
     *
     * @param \Magento\RecurringProfile\Model\RecurringProfile $profile
     * @param \Magento\Payment\Model\Info $paymentInfo
     */
    public function submitRecurringProfile(\Magento\RecurringProfile\Model\RecurringProfile $profile, \Magento\Payment\Model\Info $paymentInfo);

    /**
     * Fetch details
     *
     * @param string $referenceId
     * @param \Magento\Object $result
     */
    public function getRecurringProfileDetails($referenceId, \Magento\Object $result);

    /**
     * Check whether can get recurring profile details
     *
     * @return bool
     */
    public function canGetRecurringProfileDetails();

    /**
     * Update data
     *
     * @param \Magento\RecurringProfile\Model\RecurringProfile $profile
     */
    public function updateRecurringProfile(\Magento\RecurringProfile\Model\RecurringProfile $profile);

    /**
     * Manage status
     *
     * @param \Magento\RecurringProfile\Model\RecurringProfile $profile
     */
    public function updateRecurringProfileStatus(\Magento\RecurringProfile\Model\RecurringProfile $profile);
}
