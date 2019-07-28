<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Simplexml\Element;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class contains tests for Direct Post integration
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DirectpostTest extends \PHPUnit\Framework\TestCase
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
        $payment = $this->getPayment('100000002');
        $transactionId = '106235225';

        /** @var ZendClient|MockObject $httpClient */
        $httpClient = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUri', 'setConfig', 'setParameterPost', 'setMethod', 'request'])
            ->getMock();

        $this->httpClientFactory->expects(static::once())
            ->method('create')
            ->willReturn($httpClient);

        $response = $this->getMockBuilder('Zend_Http_Response')
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
     * Verifies that order is placed in correct state according the action taken for a transaction that
     * triggered one or more of the Advanced Fraud Detection Suite filters.
     *
     * @param string $filterAction
     * @param string $orderId
     * @param string $expectedOrderState
     *
     * @magentoConfigFixture current_store payment/authorizenet_directpost/trans_md5 TestHash
     * @magentoConfigFixture current_store payment/authorizenet_directpost/login TestLogin
     * @magentoDataFixture Magento/Authorizenet/_files/order.php
     * @dataProvider fdsFilterActionDataProvider
     */
    public function testProcessWithFdsFilterActionReportOnly($filterAction, $orderId, $expectedOrderState)
    {
        $responseBody = $this->getSuccessResponse($orderId);
        $transactionService = $this->getTransactionService($filterAction);
        $this->objectManager->addSharedInstance($transactionService, TransactionService::class);

        $this->directPost->process($responseBody);

        /** @var Payment $payment */
        $payment = $this->getPayment($orderId);
        $this->objectManager->removeSharedInstance(TransactionService::class);

        static::assertEquals($expectedOrderState, $payment->getOrder()->getState());
    }

    /**
     * @return array
     */
    public function fdsFilterActionDataProvider()
    {
        return [
            [
                'filter_action' => 'authAndHold',
                'order_id' => '100000003',
                'expected_order_state' => Order::STATE_PAYMENT_REVIEW
            ],
            [
                'filter_action' => 'report',
                'order_id' => '100000004',
                'expected_order_state' => Order::STATE_COMPLETE
            ],
        ];
    }

    /**
     * @param string $orderId
     * @return array
     */
    private function getSuccessResponse($orderId)
    {
        return [
            'x_response_code' => '1',
            'x_response_reason_code' => '1',
            'x_response_reason_text' => 'This transaction has been approved.',
            'x_avs_code' => 'Y',
            'x_auth_code' => 'YWO2E2',
            'x_trans_id' => '40004862720',
            'x_method' => 'CC',
            'x_card_type' => 'Visa',
            'x_account_number' => 'XXXX1111',
            'x_first_name' => 'John',
            'x_last_name' => 'Smith',
            'x_company' => 'CompanyName',
            'x_address' => 'Green str, 67',
            'x_city' => 'CityM',
            'x_state' => 'Alabama',
            'x_zip' => '93930',
            'x_country' => 'US',
            'x_phone' => '3468676',
            'x_fax' => '04040404',
            'x_email' => 'user_1@example.com',
            'x_invoice_num' => $orderId,
            'x_description' => '',
            'x_type' => 'auth_only',
            'x_cust_id' => '',
            'x_ship_to_first_name' => 'John',
            'x_ship_to_last_name' => 'Smith',
            'x_ship_to_company' => 'CompanyName',
            'x_ship_to_address' => 'Green str, 67',
            'x_ship_to_city' => 'CityM',
            'x_ship_to_state' => 'Alabama',
            'x_ship_to_zip' => '93930',
            'x_ship_to_country' => 'US',
            'x_amount' => '120.15',
            'x_tax' => '0.00',
            'x_duty' => '0.00',
            'x_freight' => '5.00',
            'x_tax_exempt' => 'FALSE',
            'x_po_num' => '',
            'x_MD5_Hash' => 'C1CC5AB9D6F0481E240AD74DFF624584',
            'x_SHA2_Hash' => '',
            'x_cvv2_resp_code' => 'P',
            'x_cavv_response' => '2',
            'x_test_request' => 'false',
            'controller_action_name' => 'directpost_payment',
            'is_secure' => '1',
        ];
    }

    /**
     * Get order payment.
     *
     * @param string $orderId
     * @return Payment
     */
    private function getPayment($orderId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter(OrderInterface::INCREMENT_ID, $orderId)
            ->create();

        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $orders = $orderRepository->getList($searchCriteria)
            ->getItems();

        /** @var OrderInterface $order */
        $order = array_pop($orders);
        return $order->getPayment();
    }

    /**
     * Returns TransactionService mocked object with authorize predefined response.
     *
     * @param string $filterAction
     * @return TransactionService|MockObject
     */
    private function getTransactionService($filterAction)
    {
        $response = str_replace(
            '{filterAction}',
            $filterAction,
            file_get_contents(__DIR__ . '/../_files/transaction_details.xml')
        );

        $transactionService = $this->getMockBuilder(TransactionService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transactionService->method('getTransactionDetails')
            ->willReturn(
                new Element($response)
            );

        return $transactionService;
    }
}
