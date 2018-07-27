<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Model\Method\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Model\Billing\AgreementFactory;
use Magento\Quote\Model\Quote;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SpecificationPluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var SpecificationPlugin */
    protected $model;

    /** @var AgreementFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $agreementFactory;

    protected function setUp()
    {
        $this->agreementFactory = $this->getMockBuilder('Magento\Paypal\Model\Billing\AgreementFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

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
        $this->assertFalse($this->callAroundIsApplicable($proceed, $paymentMethod, $quote));
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
            'Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection',
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

    /**
     * @return array
     */
    public function aroundIsApplicableDataProvider()
    {
        return [[0], [1], [2]];
    }

    /**
     * @param bool $result
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return \Closure
     */
    private function getProceedClosure($result, MethodInterface $paymentMethod, Quote $quote)
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
     * @return MethodInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentMethod($code)
    {
        $paymentMethod = $this->getMockForAbstractClass('Magento\Payment\Model\MethodInterface');
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
        $quote = $this->getMock('Magento\Quote\Model\Quote', ['__wakeup'], [], '', false);
        $quote->setCustomerId($customerId);
        return $quote;
    }

    /**
     * Call aroundIsApplicable method
     *
     * @param \Closure $proceed
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     */
    private function callAroundIsApplicable(
        \Closure $proceed,
        MethodInterface $paymentMethod,
        Quote $quote
    ) {
        $specification = $this->getMockForAbstractClass('Magento\Payment\Model\Checks\SpecificationInterface');
        return $this->model->aroundIsApplicable($specification, $proceed, $paymentMethod, $quote);
    }
}
