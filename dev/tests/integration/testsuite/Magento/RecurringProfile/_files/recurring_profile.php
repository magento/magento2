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

// Requires Magento/Sales/_files/quote.php
// Requires Magento/Customer/_files/customer.php
use Magento\TestFramework\Helper\Bootstrap;

define('FIXTURE_RECURRING_PROFILE_SCHEDULE_DESCRIPTION', 'fixture-recurring-profile-schedule');

$objectManager = Bootstrap::getObjectManager();
// Mock Profile class, because no default implementation of \Magento\Payment\Model\Recurring\Profile\MethodInterface
$profile = \PHPUnit_Framework_MockObject_Generator::getMock(
    'Magento\RecurringProfile\Model\Profile',
    ['isValid'],
    [
        $objectManager->get('Magento\Model\Context'),
        $objectManager->get('Magento\Registry'),
        $objectManager->get('Magento\Payment\Helper\Data'),
        $objectManager->get('Magento\RecurringProfile\Model\PeriodUnits'),
        $objectManager->get('Magento\RecurringProfile\Block\Fields'),
        $objectManager->get('Magento\Stdlib\DateTime\TimezoneInterface'),
        $objectManager->get('Magento\Locale\ResolverInterface'),
        $objectManager->get('Magento\Sales\Model\OrderFactory'),
        $objectManager->get('Magento\Sales\Model\Order\AddressFactory'),
        $objectManager->get('Magento\Sales\Model\Order\PaymentFactory'),
        $objectManager->get('Magento\Sales\Model\Order\ItemFactory'),
        $objectManager->get('Magento\Math\Random'),
        $objectManager->get('Magento\RecurringProfile\Model\States')
    ]
);
$profile->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
    ->method('isValid')
    ->will(new \PHPUnit_Framework_MockObject_Stub_Return(true));
/** @var Magento\RecurringProfile\Model\Profile $profile */
$profile
    ->setQuote(Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote')->load(1))
    ->setPeriodUnit('year')
    ->setPeriodFrequency(1)
    ->setScheduleDescription(FIXTURE_RECURRING_PROFILE_SCHEDULE_DESCRIPTION)
    ->setBillingAmount(1)
    ->setCurrencyCode('USD')
    ->setInternalReferenceId('rp-1')
    ->setCustomerId(1)
    ->save();
