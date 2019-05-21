<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaypalGraphQl\Controller;

use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Api\Type\Factory as ApiFactory;
use Magento\Quote\Model\Quote;
use Magento\Framework\App\Request\Http;
use Magento\GraphQl\Controller\GraphQl;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Framework\Webapi\Request;

/**
 * Tests of Paypal Express actions
 *
 * @magentoAppArea graphql
 */
class ExpressTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var GraphQl
     */
    private $graphqlController;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->request = $this->objectManager->create(Http::class);
    }

    /**
     * Test setPaymentMethodOnCart & PlaceOrder with simple product and customer for paypal_express.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_express_with_customer.php
     * @magentoConfigFixture current_store payment/paypal_express/active 1
     */
    public function testReturnAction()
    {
        $payerId = 123;
        $token = 'EC-8F665944MJ782471F';

        $paymentMethodCode = \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS;

        $orderId = 'test02';
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load($orderId, 'reserved_order_id');

        $quoteIdMask = $this->objectManager->create(QuoteIdMask::class);
        $quoteIdMask->setQuoteId($quote->getId());
        $quoteIdMask->save();

        /** @var \Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface $maskedQuote */
        $maskedQuote = $this->objectManager->create(\Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface::class);

        $cartId = $maskedQuote->execute($quote->getId());

        $nvpMethods = [
            'setToken',
            'setPayerId',
            'setAmount',
            'setPaymentAction',
            'setNotifyUrl',
            'setInvNum',
            'setCurrencyCode',
            'setPaypalCart',
            'setIsLineItemsEnabled',
            'setAddress',
            'setBillingAddress',
            'callDoExpressCheckoutPayment',
            'callGetExpressCheckoutDetails',
            'getExportedBillingAddress',
            'GetExpressCheckoutDetails',
        ];

        $nvpMock = $this->getMockBuilder(Nvp::class)
            ->setMethods($nvpMethods)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($nvpMethods as $method) {
            $nvpMock->method($method)
            ->willReturnSelf();
        }

        $apiFactoryMock = $this->getMockBuilder(ApiFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $apiFactoryMock->method('create')
            ->with(Nvp::class)
            ->willReturn($nvpMock);

        $this->objectManager->addSharedInstance($apiFactoryMock, ApiFactory::class);

        $payment = $quote->getPayment();
        $payment->setMethod($paymentMethodCode)
            ->setAdditionalInformation(
                \Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_SHIPPING_OVERRIDDEN,
                1
            );

        $quote->save();

        /** @var \Magento\Integration\Model\Oauth\Token $tokenModel */
        $tokenModel = $this->objectManager->create(\Magento\Integration\Model\Oauth\Token::class);
        $customerToken = $tokenModel->createCustomerToken(1)->getToken();

        $query
            = <<<QUERY
mutation {
          setPaymentMethodOnCart(input: {
            payment_method: {
              code: "$paymentMethodCode",
              additional_data: {
                $paymentMethodCode: {
                  payer_id: "$payerId",
                  token: "$token"
                }
              }
            },
            cart_id: "$cartId"})
          {
            cart {
              selected_payment_method {
                code
              }
            }
          }
          placeOrder(input: {cart_id: "$cartId"}) {
            order {
              order_id
            }
          }
}
QUERY;


        $webApiRequest = $this->objectManager->get(Request::class);
        $webApiRequest->getHeaders()->addHeaderLine('Content-Type', 'application/json')
            ->addHeaderLine('Accept', 'application/json')
            ->addHeaderLine('Authorization', 'Bearer ' . $customerToken);
        $this->request->setHeaders($webApiRequest->getHeaders());
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $this->request->setContent(json_encode(['query' => $query]));
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $this->request->setHeaders($headers);
        $response = $this->graphqlController->dispatch($this->request);
        $this->assertEquals(
            '{"data":{"setPaymentMethodOnCart":{"cart":{"selected_payment_method":{"code":"'
            . $paymentMethodCode . '"}}},"placeOrder":{"order":{"order_id":"' . $orderId . '"}}}}',
            $response->getContent()
        );

        $this->objectManager->removeSharedInstance(ApiFactory::class);
    }
}
