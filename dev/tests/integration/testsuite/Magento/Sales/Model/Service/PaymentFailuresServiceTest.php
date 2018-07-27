<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\PaymentFailuresInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\Quote;

class PaymentFailuresServiceTest extends \PHPUnit_Framework_TestCase
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
     * @var string
     */
    private $quoteId = 'test01';

    protected function setUp()
    {
        $this->quote = Bootstrap::getObjectManager()->create(Quote::class);
        $this->quote->load($this->quoteId, 'reserved_order_id');

        $cartRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();

        $cartRepositoryMock->method('get')
            ->with($this->quoteId)
            ->willReturn($this->quote);

        $this->paymentFailures = Bootstrap::getObjectManager()->create(
            PaymentFailuresInterface::class,
            [
                'cartRepository' => $cartRepositoryMock
            ]
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_two_products_and_customer.php
     * @magentoConfigFixture current_store payment/payflowpro/title Some Title Of The Method
     * @magentoConfigFixture current_store carriers/freeshipping/title Some Shipping Method
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testHandlerWithCustomer()
    {
        $errorMessage = __('Transaction declined.');
        $checkoutType = 'custom_checkout';

        $this->paymentFailures->handle($this->quoteId, $errorMessage);

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
            'items' => 'Simple Product  x 2  USD 10<br />Simple Product  x 1  USD 10',
            'total' => 'USD 30.0000',
            'billingAddress' => $this->quote->getBillingAddress(),
            'shippingAddress' => $this->quote->getShippingAddress()
        ];

        $this->assertEquals($expectedVars, $templateVars);
    }
}
