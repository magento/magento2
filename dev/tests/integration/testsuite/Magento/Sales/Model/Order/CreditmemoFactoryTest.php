<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 */
class CreditmemoFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Placeholder for order item id field.
     */
    const ORDER_ITEM_ID_PLACEHOLDER = 'id_item_';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CreditmemoFactory
     */
    private $creditmemoFactory;

    /**
<<<<<<< HEAD
     * @inheritdoc
=======
     * {@inheritdoc}
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->creditmemoFactory = $this->objectManager->create(CreditmemoFactory::class);
    }

    /**
     * Checks a case when creditmemo created from the order.
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_bundle_and_invoiced.php
     * @dataProvider createByOrderDataProvider
     * @param array $creditmemoData
     * @param int $expectedQty
     */
    public function testCreateByOrder(array $creditmemoData, $expectedQty)
    {
        $order = $this->getOrder('100000001');
        $creditmemoData = $this->prepareCreditMemoData($order, $creditmemoData);
        $creditmemo = $this->creditmemoFactory->createByOrder($order, $creditmemoData);
        self::assertEquals($expectedQty, $creditmemo->getTotalQty(), 'Creditmemo has wrong total qty.');
    }

    /**
     * @return array
     */
<<<<<<< HEAD
    public function createByOrderDataProvider()
=======
    public function createByOrderDataProvider(): array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        return [
            [
                'creditmemoData' => [
                    'qtys' => [
                        self::ORDER_ITEM_ID_PLACEHOLDER . '2' => 2,
                        self::ORDER_ITEM_ID_PLACEHOLDER . '3' => 10,
<<<<<<< HEAD
                    ]
                ],
                'expectedQty' => 12
=======
                    ],
                ],
                'expectedQty' => 12,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ],
            [
                'creditmemoData' => [
                    'qtys' => [
                        self::ORDER_ITEM_ID_PLACEHOLDER . '1' => 2,
                        self::ORDER_ITEM_ID_PLACEHOLDER . '2' => 2,
                        self::ORDER_ITEM_ID_PLACEHOLDER . '3' => 10,
<<<<<<< HEAD
                    ]
                ],
                'expectedQty' => 14
            ]
=======
                    ],
                ],
                'expectedQty' => 14,
            ],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        ];
    }

    /**
     * Checks a case when creditmemo created from the invoice.
     *
     * @magentoDataFixture Magento/Sales/_files/invoice_with_bundle.php
<<<<<<< HEAD
=======
     *
     * @return void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function testCreateByInvoice()
    {
        $invoice = $this->getInvoice('100000001');
        $creditmemo = $this->creditmemoFactory->createByInvoice($invoice);
        self::assertEquals(14, $creditmemo->getTotalQty(), 'Creditmemo has wrong total qty.');
    }

    /**
     * Prepare Creditmemo data.
     *
     * @param Order $order
     * @param array $creditmemoData
     * @return array
     */
<<<<<<< HEAD
    private function prepareCreditMemoData(Order $order, array $creditmemoData)
=======
    private function prepareCreditMemoData(Order $order, array $creditmemoData): array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $result = [];
        $orderItems = $order->getAllItems();
        foreach ($creditmemoData['qtys'] as $key => $item) {
            $result[$orderItems[$this->prepareOrderItemKey($key)]->getId()] = $item;
        }
        $creditmemoData['qtys'] = $result;

        return $creditmemoData;
    }

    /**
     * Prepare order item key.
     *
     * @param string $key
     * @return int
     */
    private function prepareOrderItemKey($key)
    {
        return str_replace(self::ORDER_ITEM_ID_PLACEHOLDER, '', $key) - 1;
    }

    /**
     * Retrieves order by increment ID.
     *
     * @param string $incrementId
     * @return Order
     */
    private function getOrder(string $incrementId): Order
    {
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId($incrementId);
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $order;
    }

    /**
     * Retrieves invoice by increment ID.
     *
     * @param string $incrementId
     * @return Invoice
     */
    private function getInvoice(string $incrementId): Invoice
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();
        /** @var InvoiceRepositoryInterface $repository */
        $repository = $this->objectManager->get(InvoiceRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)->getItems();
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return array_pop($items);
    }
}
