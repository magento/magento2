<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl;

use Magento\Customer\Helper\Address;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Exception\LocalizedExceptionFactory;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Math\Random;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Api\NvpFactory;
use Magento\Paypal\Model\Api\PayflowNvp;
use Magento\Paypal\Model\Api\AbstractApi;
use Magento\Paypal\Model\Api\ProcessableExceptionFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Paypal\Model\Api\Type\Factory as ApiFactory;
use Psr\Log\LoggerInterface;
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Abstract class with common logic for Paypal GraphQl tests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class PaypalExpressAbstractTest extends TestCase
{
    /**
     * @var AbstractApi|MockObject
     */
    protected $nvpMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var GraphQl
     */
    protected $graphqlController;

    /**
     * @var GraphQlRequest
     */
    protected $graphQlRequest;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $apiFactoryMock = $this->getMockBuilder(ApiFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $apiFactoryMock->method('create')
            ->willReturnMap(
                [
                    [Nvp::class, [], $this->getNvpMock(Nvp::class)],
                    [PayflowNvp::class, [], $this->getNvpMock(PayflowNvp::class)]
                ]
            );

        $this->objectManager->addSharedInstance($apiFactoryMock, ApiFactory::class);

        $this->graphqlController = $this->objectManager->get(GraphQl::class);

        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
    }

    protected function tearDown(): void
    {
        $this->disablePaypalPaymentMethods();
        $this->objectManager->removeSharedInstance(ApiFactory::class);
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
            'paypal_express',
            'payflow_express',
            'payflow_link'
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
     * Get mock of Nvp class
     *
     * @param string $nvpClass
     * @return AbstractApi|MockObject
     */
    private function getNvpMock(string $nvpClass)
    {
        if (empty($this->nvpMock)) {
            $constructorArgs = [
                'customerAddress' => $this->objectManager->get(Address::class),
                'logger' => $this->objectManager->get(LoggerInterface::class),
                'customLogger' => $this->objectManager->get(Logger::class),
                'localeResolver' => $this->objectManager->get(ResolverInterface::class),
                'regionFactory' => $this->objectManager->get(RegionFactory::class),
                'countryFactory' => $this->objectManager->get(CountryFactory::class),
                'processableExceptionFactory' => $this->objectManager->get(ProcessableExceptionFactory::class),
                'frameworkExceptionFactory' => $this->objectManager->get(LocalizedExceptionFactory::class),
                'curlFactory' => $this->objectManager->get(CurlFactory::class),
            ];

            if ($nvpClass === PayflowNvp::class) {
                $constructorArgs += [
                    'mathRandom' => $this->objectManager->get(Random::class),
                    'nvpFactory' => $this->objectManager->get(NvpFactory::class)
                ];
            }

            $constructorArgs += ['data' => []];
            $this->nvpMock = $this->getMockBuilder($nvpClass)
                ->setConstructorArgs($constructorArgs)
                ->onlyMethods(['call'])
                ->getMock();
        }
        return $this->nvpMock;
    }

    /**
     * Get GraphQl query for creating Paypal token
     *
     * @param string $cartId
     * @param string $paymentMethod
     * @return string
     */
    protected function getCreateTokenMutation(string $cartId, string $paymentMethod): string
    {

        return <<<QUERY
mutation {
    createPaypalExpressToken(input: {
        cart_id: "{$cartId}",
        code: "{$paymentMethod}",
        urls: {
            return_url: "paypal/express/return/",
            cancel_url: "paypal/express/cancel/"
            success_url: "checkout/onepage/success/",
            pending_url: "checkout/onepage/pending/"
        }
        express_button: true
    })
    {
        __typename
        token
        paypal_urls{
            start
            edit
        }
    }
}
QUERY;
    }
}
