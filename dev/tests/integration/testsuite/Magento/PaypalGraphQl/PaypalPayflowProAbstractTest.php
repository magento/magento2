<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl;

use Magento\GraphQl\Controller\GraphQl;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

use Magento\Payment\Model\Method\Online\GatewayInterface;

/**
 * Abstract class with common logic for Paypal GraphQl tests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class PaypalPayflowProAbstractTest extends TestCase
{
    /**
     * @var GatewayInterface|MockObject
     */
    protected $gatewayMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var GraphQl
     */
    protected $graphqlController;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->objectManager->addSharedInstance($this->getGatewayMock(), Gateway::class);

        $this->graphqlController = $this->objectManager->get(GraphQl::class);
    }

    protected function tearDown()
    {
        $this->disablePaypalPaymentMethods();
        $this->objectManager->removeSharedInstance(Gateway::class);
    }

    /**
     * Get quote by reserved order id
     *
     * @param $reservedOrderId
     * @return Quote
     */
    protected function getQuoteByReservedOrderId($reservedOrderId): Quote
    {
        $quoteFactory = $this->objectManager->get(QuoteFactory::class);
        /** @var Quote $quote */
        $quote = $quoteFactory->create();

        $quote->load($reservedOrderId, 'reserved_order_id');
        return $quote;
    }

    /**
     * Enables Paypal payment method by payment code
     *
     * @return void
     */
    protected function enablePaymentMethod($methodCode): void
    {
        $config = $this->objectManager->get(Config::class);
        $config->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

        $paymentMethodActive = 'payment/' . $methodCode . '/active';

        $config->setDataByPath($paymentMethodActive, '1');
        $config->save();
    }

    /**
     * Disables list of Paypal payment methods
     *
     * @return void
     */
    protected function disablePaypalPaymentMethods(): void
    {
        $paypalMethods = [
            'payflowpro',
        ];
        $config = $this->objectManager->get(Config::class);
        $config->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

        foreach ($paypalMethods as $method) {
            $paymentMethodActive = 'payment/' . $method . '/active';
            $config->setDataByPath($paymentMethodActive, '0');
            $config->save();
        }
    }

    /**
     * Get mock of Gateway class
     *
     * @return GatewayInterface|MockObject
     */
    private function getGatewayMock()
    {
        if (empty($this->gatewayMock)) {
            $this->gatewayMock = $this->getMockBuilder(Gateway::class)
                ->disableOriginalConstructor()
                ->setMethods(['postRequest'])
                ->getMock();
        }
        return $this->gatewayMock;
    }

    /**
     * Get GraphQl query for creating Paypal token
     *
     * @param string $cartId
     * @param string $paymentMethod
     * @return string
     */
    protected function getCreatePayflowTokenMutation(string $cartId): string
    {
        $url = $this->objectManager->get(UrlInterface::class);
        $baseUrl = $url->getBaseUrl();

        return <<<QUERY
mutation {
  createPayflowProToken(
    input: {
      cart_id:"{$cartId}",
      urls: {
        cancel_url: "{$baseUrl}paypal/transparent/cancel/"
        error_url: "{$baseUrl}paypal/transparent/error/"
        return_url: "{$baseUrl}paypal/transparent/response/"
      }
    }
  ) {
    response_message
    result
    result_code
    secure_token
    secure_token_id
  }
}
QUERY;
    }
}
