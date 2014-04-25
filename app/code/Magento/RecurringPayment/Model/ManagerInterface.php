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
namespace Magento\RecurringPayment\Model;

use Magento\Payment\Model\Info as PaymentInfo;
use Magento\Framework\Object;

/**
 * Recurring payment gateway management interface
 */
interface ManagerInterface
{
    /**
     * Validate data
     *
     * @param RecurringPayment $payment
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function validate(RecurringPayment $payment);

    /**
     * Submit to the gateway
     *
     * @param RecurringPayment $payment
     * @param PaymentInfo $paymentInfo
     * @return void
     */
    public function submit(RecurringPayment $payment, PaymentInfo $paymentInfo);

    /**
     * Fetch details
     *
     * @param string $referenceId
     * @param \Magento\Framework\Object $result
     * @return void
     */
    public function getDetails($referenceId, Object $result);

    /**
     * Check whether can get recurring payment details
     *
     * @return bool
     */
    public function canGetDetails();

    /**
     * Update data
     *
     * @param RecurringPayment $payment
     * @return void
     */
    public function update(RecurringPayment $payment);

    /**
     * Manage status
     *
     * @param RecurringPayment $payment
     * @return void
     */
    public function updateStatus(RecurringPayment $payment);

    /**
     * Get  Payment Method code
     *
     * @return string
     */
    public function getPaymentMethodCode();
}
