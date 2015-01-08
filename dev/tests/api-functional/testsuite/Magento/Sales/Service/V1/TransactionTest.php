<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Service\V1;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config;

/**
 * Class TransactionReadTest
 */
class TransactionTest extends WebapiAbstract
{
    /**
     * Service read name
     */
    const SERVICE_READ_NAME = 'salesTransactionRepositoryV1';

    /**
     * Resource path for REST
     */
    const RESOURCE_PATH = '/V1/transactions';

    /**
     * Service version
     */
    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Tests list of order transactions
     *
     * @magentoApiDataFixture Magento/Sales/_files/transactions_detailed.php
     */
    public function testTransactionGet()
    {
        /** @var Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000006');

        /** @var Payment $payment */
        $payment = $order->getPayment();
        /** @var Transaction $transaction */
        $transaction = $payment->getTransaction('trx_auth');

        $childTransactions = $transaction->getChildTransactions();
        $childTransaction = reset($childTransactions);

        $expectedData = $this->getPreparedTransactionData($transaction);
        $childTransactionData = $this->getPreparedTransactionData($childTransaction);
        $expectedData['child_transactions'][] = $childTransactionData;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $transaction->getId(),
                'httpMethod' => Config::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'get',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['id' => $transaction->getId()]);
        ksort($expectedData);
        ksort($result);
        $this->assertEquals($expectedData, $result);
    }

    /**
     * Tests list of order transactions
     * @dataProvider filtersDataProvider
     */
    public function testTransactionList($filters)
    {
        /** @var Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000006');

        /** @var Payment $payment */
        $payment = $order->getPayment();
        /** @var Transaction $transaction */
        $transaction = $payment->getTransaction('trx_auth');

        $childTransactions = $transaction->getChildTransactions();

        $childTransaction = reset($childTransactions);

        /** @var $searchCriteriaBuilder  \Magento\Framework\Api\SearchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );

        $searchCriteriaBuilder->addFilter($filters);
        $searchData = $searchCriteriaBuilder->create()->__toArray();

        $requestData = ['criteria' => $searchData];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Config::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'getList',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertArrayHasKey('items', $result);

        $transactionData = $this->getPreparedTransactionData($transaction);
        $childTransactionData = $this->getPreparedTransactionData($childTransaction);
        $transactionData['child_transactions'][] = $childTransactionData;
        $expectedData = [$transactionData, $childTransactionData];

        $this->assertEquals($expectedData, $result['items']);
    }

    /**
     * @param Transaction $transaction
     * @return array
     */
    private function getPreparedTransactionData(Transaction $transaction)
    {
        $additionalInfo = [];
        foreach ($transaction->getAdditionalInformation() as $value) {
            $additionalInfo[] = $value;
        }

        $expectedData = ['transaction_id' => (int)$transaction->getId()];

        if (!is_null($transaction->getParentId())) {
            $expectedData['parent_id'] = (int)$transaction->getParentId();
        }

        $expectedData = array_merge(
            $expectedData,
            [
                'order_id' => (int)$transaction->getOrderId(),
                'payment_id' => (int)$transaction->getPaymentId(),
                'txn_id' => $transaction->getTxnId(),
                'parent_txn_id' => ($transaction->getParentTxnId() ? (string)$transaction->getParentTxnId() : ''),
                'txn_type' => $transaction->getTxnType(),
                'is_closed' => (int)$transaction->getIsClosed(),
                'additional_information' => ['data'],
                'created_at' => $transaction->getCreatedAt(),
                'child_transactions' => [],
            ]
        );

        return $expectedData;
    }

    /**
     * @return array
     */
    public function filtersDataProvider()
    {
        /** @var $filterBuilder  \Magento\Framework\Api\FilterBuilder */
        $filterBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Api\FilterBuilder'
        );

        return [
            [
                [
                    $filterBuilder->setField('created_at')->setValue('2020-12-12 00:00:00')
                        ->setConditionType('lteq')->create(),
                ],
            ]
        ];
    }
}
