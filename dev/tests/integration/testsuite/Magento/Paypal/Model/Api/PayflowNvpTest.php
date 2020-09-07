<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Api;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Paypal\Model\CartFactory;
use Magento\Paypal\Model\Config;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PayflowNvpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PayflowNvp
     */
    private $nvpApi;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Curl|MockObject
     */
    private $httpClient;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var CurlFactory|MockObject $httpFactory */
        $httpFactory = $this->getMockBuilder(CurlFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClient = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpFactory->method('create')
            ->willReturn($this->httpClient);

        $this->nvpApi = $this->objectManager->create(PayflowNvp::class, [
            'curlFactory' => $httpFactory
        ]);

        /** @var ProductMetadataInterface|MockObject $productMetadata */
        $productMetadata = $this->getMockBuilder(ProductMetadataInterface::class)
            ->getMock();
        $productMetadata->method('getEdition')
            ->willReturn('');

        /** @var Config $config */
        $config = $this->objectManager->get(Config::class);
        $config->setMethodCode(Config::METHOD_WPP_PE_EXPRESS);

        $refObject = new \ReflectionObject($config);
        $refProperty = $refObject->getProperty('productMetadata');
        $refProperty->setAccessible(true);
        $refProperty->setValue($config, $productMetadata);

        $this->nvpApi->setConfigObject($config);
    }

    /**
     * Checks a case when items and discount are present in the request.
     *
     * @magentoDataFixture Magento/Paypal/_files/quote_payflowpro.php
     * @magentoDbIsolation disabled
     */
    public function testRequestLineItems()
    {
        $quote = $this->getQuote('100000015');
        /** @var CartFactory $cartFactory */
        $cartFactory = $this->objectManager->get(CartFactory::class);
        $cart = $cartFactory->create(['salesModel' => $quote]);

        $request = 'TENDER=P&AMT=52.14&FREIGHTAMT=0.00&TAXAMT=0.00&'
            . 'L_NAME0=Simple 1&L_QTY0=1&L_COST0=7.69&'
            . 'L_NAME1=Simple 2&L_QTY1=2&L_COST1=9.69&'
            . 'L_NAME2=Simple 3&L_QTY2=3&L_COST2=11.69&'
            . 'L_NAME3=Discount&L_QTY3=1&L_COST3=-10.00&'
            . 'TRXTYPE=A&ACTION=S&BUTTONSOURCE=Magento_2_';

        $this->httpClient->method('write')
            ->with(
                'POST',
                'https://payflowpro.paypal.com/transaction',
                '1.1',
                ['PAYPAL-NVP: Y'],
                self::equalTo($request)
            );

        $this->httpClient->method('read')
            ->willReturn("HTTP/1.1 200 OK\r\nConnection: close\r\n\r\nRESULT=0&RESPMSG=Approved");

        $this->nvpApi->setAmount($quote->getBaseGrandTotal());
        $this->nvpApi->setPaypalCart($cart);
        $this->nvpApi->setQuote($quote);
        $this->nvpApi->setIsLineItemsEnabled(true);
        $this->nvpApi->callSetExpressCheckout();
    }

    /**
     * Gets quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote($reservedOrderId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();
        /** @var QuoteRepository $quoteRepository */
        $quoteRepository = $this->objectManager->get(QuoteRepository::class);
        $items = $quoteRepository->getList($searchCriteria)
            ->getItems();
        return array_pop($items);
    }
}
