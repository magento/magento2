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
namespace Magento\RecurringPayment\Model\Plugin;

use Magento\Sales\Model\Quote;
use Magento\Payment\Model\Checks\PaymentMethodChecksInterface;
use Magento\RecurringPayment\Model\Method\RecurringPaymentSpecification;

/**
 * ZeroTotal checker plugin
 * Allow ZeroTotal for recurring payment
 */
class ZeroTotal
{
    /** @var  \Magento\RecurringPayment\Model\Quote\Filter */
    protected $filter;

    /** @var  RecurringPaymentSpecification */
    protected $specification;

    /**
     * @param \Magento\RecurringPayment\Model\Quote\Filter $filter
     * @param RecurringPaymentSpecification $specification
     */
    public function __construct(
        \Magento\RecurringPayment\Model\Quote\Filter $filter,
        RecurringPaymentSpecification $specification
    ) {
        $this->filter = $filter;
        $this->specification = $specification;
    }

    /**
     * @param \Magento\Payment\Model\Checks\ZeroTotal $subject
     * @param callable $proceed
     * @param PaymentMethodChecksInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsApplicable(
        \Magento\Payment\Model\Checks\ZeroTotal $subject,
        \Closure $proceed,
        PaymentMethodChecksInterface $paymentMethod,
        Quote $quote
    ) {
        return $proceed($paymentMethod, $quote)
            || $this->specification->isSatisfiedBy($paymentMethod->getCode())
            && $this->filter->hasRecurringItems($quote);
    }
}
