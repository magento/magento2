<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend_Http_Response;

/**
 * Class contains tests for Direct Post integration
 */
class DirectpostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ZendClientFactory|MockObject
     */
    private $httpClientFactory;

    /**
     * @var Directpost
     */
    private $directPost;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->httpClientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->directPost = $this->objectManager->create(Directpost::class, [
            'httpClientFactory' => $this->httpClientFactory
        ]);
    }

    /**
     * @covers \Magento\Authorizenet\Model\Directpost::capture
     * @magentoDataFixture Magento/Authorizenet/_files/order.php
     */
    public function testCapture()
    {
        $amount = 120.15;
        /** @var Payment $payment */
        $payment = $this->getPayment();
        $transactionId = '106235225';

        /** @var ZendClient|MockObject $httpClient */
        $httpClient = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUri', 'setConfig', 'setParameterPost', 'setMethod', 'request'])
            ->getMock();

        $this->httpClientFactory->expects(static::once())
            ->method('create')
            ->willReturn($httpClient);

        $response = $this->getMockBuilder(Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBody'])
            ->getMock();
        $response->expects(static::once())
            ->method('getBody')
            ->willReturn(
                "1(~)1(~)1(~)This transaction has been approved.(~)AWZFTG(~)P(~){$transactionId}(~)100000002(~)
                (~)120.15(~)CC(~)prior_auth_capture(~)(~)Anthony(~)Nealy(~)(~)Pearl St(~)Los Angeles(~)California
                (~)10020(~)US(~)22-333-44(~)(~)customer@example.com(~)John(~)Doe(~)
                (~)Bourne St(~)London(~)(~)DW23W(~)UK(~)0.00(~)(~){$amount}(~)(~)
                (~)74B5D54ADFE98093A0FF6446(~)(~)(~)(~)(~)(~)(~)(~)(~)(~)(~)(~)(~)XXXX1111(~)Visa(~)(~)(~)(~)(~)
                (~)(~)(~)(~)(~)(~)(~)(~)(~)(~)(~)(~)"
            );

        $httpClient->expects(static::once())
            ->method('request')
            ->willReturn($response);

        $this->directPost->capture($payment, $amount);

        static::assertEquals($transactionId, $payment->getTransactionId());
        static::assertFalse($payment->getIsTransactionClosed());
        static::assertEquals('US', $payment->getOrder()->getBillingAddress()->getCountryId());
        static::assertEquals('UK', $payment->getOrder()->getShippingAddress()->getCountryId());
    }

    /**
     * Get order payment
     * @return Payment
     */
    private function getPayment()
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->objectManager->get(FilterBuilder::class);
        $filters = [
            $filterBuilder->setField(OrderInterface::INCREMENT_ID)
                ->setValue('100000002')
                ->create()
        ];

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilters($filters)
            ->create();

        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $orders = $orderRepository->getList($searchCriteria)
            ->getItems();

        /** @var OrderInterface $order */
        $order = array_pop($orders);
        return $order->getPayment();
    }
}
