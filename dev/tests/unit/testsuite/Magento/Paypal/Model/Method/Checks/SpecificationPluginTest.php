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

namespace Magento\Paypal\Model\Method\Checks;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Model\Checks\PaymentMethodChecksInterface;
use Magento\Sales\Model\Quote;
use Magento\Paypal\Model\Billing\AgreementFactory;

class SpecificationPluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var SpecificationPlugin */
    protected $model;

    /** @var AgreementFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $agreementFactory;

    protected function setUp()
    {
        $this->agreementFactory = $this->getMock('Magento\Paypal\Model\Billing\AgreementFactory', ['create']);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Paypal\Model\Method\Checks\SpecificationPlugin',
            [
                'agreementFactory' => $this->agreementFactory
            ]
        );
    }

    public function testAroundIsApplicableNotOriginallyApplicable()
    {
        $paymentMethod = $this->getPaymentMethod('any');
        $quote = $this->getQuote('any');
        $proceed = $this->getProceedClosure(false, $paymentMethod, $quote);
        $this->assertFalse($this->callAroundIsApplicable($proceed, $paymentMethod, $quote));
    }

    public function testAroundIsApplicableNotAgreement()
    {
        $paymentMethod = $this->getPaymentMethod('not_agreement');
        $quote = $this->getQuote('any');
        $proceed = $this->getProceedClosure(true, $paymentMethod, $quote);
        $this->assertTrue($this->callAroundIsApplicable($proceed, $paymentMethod, $quote));
    }

    public function testAroundIsApplicableNoCustomerId()
    {
        $paymentMethod = $this->getPaymentMethod('paypal_billing_agreement');
        $quote = $this->getQuote(null);
        $proceed = $this->getProceedClosure(true, $paymentMethod, $quote);
        $this->assertTrue($this->callAroundIsApplicable($proceed, $paymentMethod, $quote));
    }

    /**
     * @param int $count
     * @dataProvider aroundIsApplicableDataProvider
     */
    public function testAroundIsApplicable($count)
    {
        $paymentMethod = $this->getPaymentMethod('paypal_billing_agreement');
        $quote = $this->getQuote(1);
        $proceed = $this->getProceedClosure(true, $paymentMethod, $quote);
        $agreementCollection = $this->getMock(
            'Magento\Paypal\Model\Resource\Billing\Agreement\Collection',
            [],
            [],
            '',
            false
        );
        $agreementCollection->expects($this->once())
            ->method('count')
            ->will($this->returnValue($count));
        $agreement = $this->getMock('Magento\Paypal\Model\Billing\Agreement', [], [], '', false);
        $agreement->expects($this->once())
            ->method('getAvailableCustomerBillingAgreements')
            ->with(1)
            ->will($this->returnValue($agreementCollection));
        $this->agreementFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($agreement));
        $this->assertEquals($count > 0, $this->callAroundIsApplicable($proceed, $paymentMethod, $quote));
    }

    public function aroundIsApplicableDataProvider()
    {
        return [[0], [1], [2]];
    }

    /**
     * @param bool $result
     * @param PaymentMethodChecksInterface $paymentMethod
     * @param Quote $quote
     * @return \Closure
     */
    private function getProceedClosure($result, PaymentMethodChecksInterface $paymentMethod, Quote $quote)
    {
        $self = $this;
        return function ($parameter1, $parameter2) use ($result, $paymentMethod, $quote, $self) {
            $self->assertSame($paymentMethod, $parameter1);
            $self->assertSame($quote, $parameter2);
            return $result;
        };
    }

    /**
     * Get payment method parameter
     *
     * @param string $code
     * @return PaymentMethodChecksInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentMethod($code)
    {
        $paymentMethod = $this->getMockForAbstractClass('Magento\Payment\Model\Checks\PaymentMethodChecksInterface');
        $paymentMethod->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));
        return $paymentMethod;
    }

    /**
     * Get quote parameter
     *
     * @param mixed $customerId
     * @return Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuote($customerId)
    {
        $quote = $this->getMock('Magento\Sales\Model\Quote', ['__wakeup'], [], '', false);
        $quote->setCustomerId($customerId);
        return $quote;
    }

    /**
     * Call aroundIsApplicable method
     *
     * @param \Closure $proceed
     * @param PaymentMethodChecksInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     */
    private function callAroundIsApplicable(
        \Closure $proceed,
        PaymentMethodChecksInterface $paymentMethod,
        Quote $quote
    ) {
        $specification = $this->getMockForAbstractClass('Magento\Payment\Model\Checks\SpecificationInterface');
        return $this->model->aroundIsApplicable($specification, $proceed, $paymentMethod, $quote);
    }
}
