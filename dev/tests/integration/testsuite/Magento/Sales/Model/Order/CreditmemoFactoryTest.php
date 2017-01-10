<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Test for CreditmemoFactory class.
 * @magentoDbIsolation enabled
 */
class CreditmemoFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Placeholder for order item id field.
     */
    const ORDER_ITEM_ID_PLACEHOLDER = 'id_item_';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_dummy_item_and_invoiced.php
     * @dataProvider createByOrderDataProvider
     * @param array $creditmemoData
     * @param int $expectedQty
     */
    public function testCreateByOrder(array $creditmemoData, $expectedQty)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        /** @var \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory */
        $creditmemoFactory = $this->objectManager->create(\Magento\Sales\Model\Order\CreditmemoFactory::class);
        $creditmemoData = $this->prepareCreditMemoData($order, $creditmemoData);
        $creditmemo = $creditmemoFactory->createByOrder($order, $creditmemoData);
        $this->assertEquals($expectedQty, $creditmemo->getTotalQty(), 'Creditmemo has wrong total qty.');
    }

    /**
     * Prepare Creditmemo data.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $creditmemoData
     * @return array
     */
    private function prepareCreditMemoData(\Magento\Sales\Model\Order $order, array $creditmemoData)
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
     * @return array
     */
    public function createByOrderDataProvider()
    {
        return [
            // Variation #1
            [
                //$creditmemoData
                [
                    'qtys' => [
                        self::ORDER_ITEM_ID_PLACEHOLDER . '1' => 1,
                        self::ORDER_ITEM_ID_PLACEHOLDER . '2' => 1,
                    ]
                ],
                //$expectedQty
                4
            ]
        ];
    }
}
