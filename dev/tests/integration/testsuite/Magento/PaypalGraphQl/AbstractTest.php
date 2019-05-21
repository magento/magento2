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
use Magento\GraphQl\Controller\GraphQl;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Api\ProcessableExceptionFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Paypal\Model\Api\Type\Factory as ApiFactory;
use Psr\Log\LoggerInterface;

/**
 * Abstract class with common logic for Paypal GraphQl tests
 */
abstract class AbstractTest extends TestCase
{
    /**
     * @var Nvp|MockObject
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

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $apiFactoryMock = $this->getMockBuilder(ApiFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $apiFactoryMock->method('create')
            ->with(Nvp::class)
            ->willReturn($this->getNvpMock());
        $this->objectManager->addSharedInstance($apiFactoryMock, ApiFactory::class);

        $this->graphqlController = $this->objectManager->get(GraphQl::class);
    }

    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(ApiFactory::class);
    }

    protected function getQuoteByReservedOrderId($reservedOrderId)
    {
        $quoteFactory = $this->objectManager->get(QuoteFactory::class);
        $quote = $quoteFactory->create();

        $quote->load($reservedOrderId, 'reserved_order_id');

        return $quote;
    }

    private function getNvpMock()
    {
        if (empty($this->nvpMock)) {
            $this->nvpMock = $this->getMockBuilder(Nvp::class)
                ->setConstructorArgs(
                    [
                    'customerAddress' => $this->objectManager->get(Address::class),
                    'logger' => $this->objectManager->get(LoggerInterface::class),
                    'customerLogger' => $this->objectManager->get(Logger::class),
                    'resolverInterface' => $this->objectManager->get(ResolverInterface::class),
                    'regionFactory' => $this->objectManager->get(RegionFactory::class),
                    'countryFactory' => $this->objectManager->get(CountryFactory::class),
                    'processableExceptionFactory' => $this->objectManager->get(ProcessableExceptionFactory::class),
                    'frameworkExceptionFactory' => $this->objectManager->get(LocalizedExceptionFactory::class),
                    'curlFactory' => $this->objectManager->get(CurlFactory::class),
                    'data' => []
                    ]
                )
                ->setMethods(['call'])
                ->getMock();
        }
        return $this->nvpMock;
    }

    protected function getCreateTokenMutation($cartId, $paymentMethod)
    {
        return <<<QUERY
mutation {
    createPaypalExpressToken(input: {
        cart_id: "{$cartId}",
        code: "{$paymentMethod}",
        express_button: true
    })
    {
        __typename
        token
        paypal_urls{
            start
            edit
        }
        method
    }
}
QUERY;
    }
}
