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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring profile gateway management interface
 */
namespace Magento\Payment\Model\Recurring\Profile;

interface MethodInterface
{
    /**
     * Validate data
     *
     * @param \Magento\Payment\Model\Recurring\Profile $profile
     * @throws \Magento\Core\Exception
     */
    public function validateRecurringProfile(\Magento\Payment\Model\Recurring\Profile $profile);

    /**
     * Submit to the gateway
     *
     * @param \Magento\Payment\Model\Recurring\Profile $profile
     * @param \Magento\Payment\Model\Info $paymentInfo
     */
    public function submitRecurringProfile(\Magento\Payment\Model\Recurring\Profile $profile, \Magento\Payment\Model\Info $paymentInfo);

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
     * @param \Magento\Payment\Model\Recurring\Profile $profile
     */
    public function updateRecurringProfile(\Magento\Payment\Model\Recurring\Profile $profile);

    /**
     * Manage status
     *
     * @param \Magento\Payment\Model\Recurring\Profile $profile
     */
    public function updateRecurringProfileStatus(\Magento\Payment\Model\Recurring\Profile $profile);
}
