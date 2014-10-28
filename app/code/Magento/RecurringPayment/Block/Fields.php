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
namespace Magento\RecurringPayment\Block;

/**
 * Recurring payment fields block
 *
 * @TODO: this is temporary solution, revise it during recurring payment-related blocks movement
 */
class Fields extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * Getter for field label
     *
     * @param string $field
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getFieldLabel($field)
    {
        switch ($field) {
            case 'order_item_id':
                return __('Purchased Item');
            case 'state':
                return __('Payment State');
            case 'created_at':
                return __('Created');
            case 'updated_at':
                return __('Updated');
            case 'subscriber_name':
                return __('Subscriber Name');
            case 'start_datetime':
                return __('Start Date');
            case 'internal_reference_id':
                return __('Internal Reference ID');
            case 'schedule_description':
                return __('Schedule Description');
            case 'suspension_threshold':
                return __('Maximum Payment Failures');
            case 'bill_failed_later':
                return __('Auto Bill on Next Cycle');
            case 'period_unit':
                return __('Billing Period Unit');
            case 'period_frequency':
                return __('Billing Frequency');
            case 'period_max_cycles':
                return __('Maximum Billing Cycles');
            case 'billing_amount':
                return __('Billing Amount');
            case 'trial_period_unit':
                return __('Trial Billing Period Unit');
            case 'trial_period_frequency':
                return __('Trial Billing Frequency');
            case 'trial_period_max_cycles':
                return __('Maximum Trial Billing Cycles');
            case 'trial_billing_amount':
                return __('Trial Billing Amount');
            case 'currency_code':
                return __('Currency');
            case 'shipping_amount':
                return __('Shipping Amount');
            case 'tax_amount':
                return __('Tax Amount');
            case 'init_amount':
                return __('Initial Fee');
            case 'init_may_fail':
                return __('Allow Initial Fee Failure');
            case 'method_code':
                return __('Payment Method');
            case 'reference_id':
                return __('Payment Reference ID');
        }
    }

    /**
     * Getter for field comments
     *
     * @param string $field
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getFieldComment($field)
    {
        switch ($field) {
            case 'order_item_id':
                return __('Original order item that recurring payment corresponds to');
            case 'subscriber_name':
                return __(
                    'Full name of the person receiving the product or service paid for by the recurring payment.'
                );
            case 'start_datetime':
                return __('This is the date when billing for the payment begins.');
            case 'schedule_description':
                return __(
                    'Enter a short description of the recurring payment. ' .
                    'By default, this description will match the product name.'
                );
            case 'suspension_threshold':
                return __(
                    'This is the number of scheduled payments ' .
                    'that can fail before the payment is automatically suspended.'
                );
            case 'bill_failed_later':
                return __(
                    'Use this to automatically bill the outstanding balance amount in the next billing cycle ' .
                    '(if there were failed payments).'
                );
            case 'period_unit':
                return __('This is the unit for billing during the subscription period.');
            case 'period_frequency':
                return __('This is the number of billing periods that make up one billing cycle.');
            case 'period_max_cycles':
                return __('This is the number of billing cycles for the payment period.');
            case 'init_amount':
                return __('The initial, non-recurring payment amount is due immediately when the payment is created.');
            case 'init_may_fail':
                return __(
                    'This sets whether to suspend the payment if the initial fee fails or, ' .
                    'instead, add it to the outstanding balance.'
                );
        }
    }
}
