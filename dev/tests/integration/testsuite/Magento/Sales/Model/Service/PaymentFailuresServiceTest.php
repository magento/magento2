<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Service;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests \Magento\Sales\Api\PaymentFailuresInterface.
 */
class PaymentFailuresServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentFailuresInterface
     */
    private $paymentFailures;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartRepositoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->quote = Bootstrap::getObjectManager()->create(Quote::class);
        $this->cartRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();

        $this->paymentFailures = Bootstrap::getObjectManager()->create(
            PaymentFailuresInterface::class,
            [
                'cartRepository' => $this->cartRepositoryMock,
            ]
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_two_products_and_customer.php
     * @magentoConfigFixture current_store payment/payflowpro/title Some Title Of The Method
     * @magentoConfigFixture current_store carriers/freeshipping/title Some Shipping Method
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testHandlerWithCustomer(): void
    {
        $errorMessage = __('Transaction declined.');
        $checkoutType = 'custom_checkout';

        $this->quote->load('test01', 'reserved_order_id');
        $this->cartRepositoryMock->method('get')
            ->with($this->quote->getId())
            ->willReturn($this->quote);

        $this->paymentFailures->handle((int)$this->quote->getId(), $errorMessage->render());

        $paymentReflection = new \ReflectionClass($this->paymentFailures);
        $templateTimeMethod = $paymentReflection->getMethod('getLocaleDate');
        $templateTimeMethod->setAccessible(true);

        $templateVarsMethod = $paymentReflection->getMethod('getTemplateVars');
        $templateVarsMethod->setAccessible(true);

        $templateVars = $templateVarsMethod->invoke($this->paymentFailures, $this->quote, $errorMessage, $checkoutType);
        $expectedVars = [
            'reason' => $errorMessage,
            'checkoutType' => $checkoutType,
            'dateAndTime' => $templateTimeMethod->invoke($this->paymentFailures),
            'customer' => 'John Smith',
            'customerEmail' => 'aaa@aaa.com',
            'paymentMethod' => 'Some Title Of The Method',
            'shippingMethod' => 'Some Shipping Method',
            'items' => 'Simple Product  x 2  USD 10<br />Custom Design Simple Product  x 1  USD 10',
            'total' => 'USD 30.0000',
            'billingAddress' => $this->quote->getBillingAddress(),
            'shippingAddress' => $this->quote->getShippingAddress(),
            'billingAddressHtml' => $this->quote->getBillingAddress()->format('html'),
            'shippingAddressHtml' => $this->quote->getShippingAddress()->format('html'),
        ];

        $this->assertEquals($expectedVars, $templateVars);
    }
}
