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
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NvpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Nvp
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
    protected function setUp()
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

        $this->nvpApi = $this->objectManager->create(Nvp::class, [
            'curlFactory' => $httpFactory
        ]);

        /** @var ProductMetadataInterface|MockObject $productMetadata */
        $productMetadata = $this->getMockBuilder(ProductMetadataInterface::class)
            ->getMock();
        $productMetadata->method('getEdition')
            ->willReturn('');

        /** @var Config $config */
        $config = $this->objectManager->get(Config::class);
        $config->setMethodCode(Config::METHOD_EXPRESS);

        $refObject = new \ReflectionObject($config);
        $refProperty = $refObject->getProperty('productMetadata');
        $refProperty->setAccessible(true);
        $refProperty->setValue($config, $productMetadata);

        $this->nvpApi->setConfigObject($config);
    }

    /**
     * Checks a case when items with FPT (Fixed Product Tax) are present in the request.
     *
     * @magentoConfigFixture current_store tax/weee/enable 1
     * @magentoConfigFixture current_store tax/weee/include_in_subtotal 0
     * @magentoDataFixture Magento/Paypal/_files/quote_with_fpt.php
     */
    public function testRequestTotalsAndLineItemsWithFPT()
    {
        $quote = $this->getQuote('100000016');
        /** @var CartFactory $cartFactory */
        $cartFactory = $this->objectManager->get(CartFactory::class);
        $cart = $cartFactory->create(['salesModel' => $quote]);

        $request = 'PAYMENTACTION=Authorization&AMT=112.70'
            . '&SHIPPINGAMT=0.00&ITEMAMT=112.70&TAXAMT=0.00'
            . '&L_NAME0=Simple+Product+FPT&L_QTY0=1&L_AMT0=100.00'
            . '&L_NAME1=FPT&L_QTY1=1&L_AMT1=12.70'
            . '&METHOD=SetExpressCheckout&VERSION=72.0&BUTTONSOURCE=Magento_Cart_';

        $this->httpClient->method('write')
            ->with(
                'POST',
                'https://api-3t.paypal.com/nvp',
                '1.1',
                [],
                $this->equalTo($request)
            );

        $this->httpClient->method('read')
            ->willReturn(
                "HTTP/1.1 200 OK\r\nConnection: close\r\n\r\nRESULT=0&RESPMSG=Approved"
            );

        $this->nvpApi->setAmount($quote->getBaseGrandTotal());
        $this->nvpApi->setPaypalCart($cart);
        $this->nvpApi->setQuote($quote);
        $this->nvpApi->setIsLineItemsEnabled(true);
        $this->nvpApi->callSetExpressCheckout();
    }

    /**
     * Test that the refund request to Paypal sends the correct data
     *
     * @magentoDataFixture Magento/Paypal/_files/order_express_with_tax.php
     */
    public function testCallRefundTransaction()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();

        $this->nvpApi->setPayment(
            $payment
        )->setTransactionId(
            'fooTransactionId'
        )->setAmount(
            $payment->formatAmount($order->getBaseGrandTotal())
        )->setCurrencyCode(
            $order->getBaseCurrencyCode()
        )->setRefundType(
            Config::REFUND_TYPE_PARTIAL
        );

        $httpQuery = 'TRANSACTIONID=fooTransactionId&REFUNDTYPE=Partial'
            .'&CURRENCYCODE=USD&AMT=145.98&METHOD=RefundTransaction'
            .'&VERSION=72.0&BUTTONSOURCE=Magento_Cart_';

        $this->httpClient->expects($this->once())->method('write')
            ->with(
                'POST',
                'https://api-3t.paypal.com/nvp',
                '1.1',
                [],
                $httpQuery
            );

        $this->nvpApi->callRefundTransaction();
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
